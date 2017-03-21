<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         3.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Josegonzalez\Version\Model\Behavior;

use ArrayObject;
use Cake\Collection\Collection;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\ORM\Behavior;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use DateTime;

/**
 * This behavior provides a way to version dynamic data by keeping versions
 * in a separate table linked to the original record from another one. Versioned
 * fields can be configured to override those in the main table when fetched or
 * put aside into another property for the same entity.
 *
 * If you want to retrieve all versions for each of the fetched records,
 * you can use the custom `versions` finders that is exposed to the table.
 */
class VersionBehavior extends Behavior
{

    /**
     * Table instance
     *
     * @var \Cake\ORM\Table
     */
    protected $_table;

    /**
     * Default config
     *
     * These are merged with user-provided configuration when the behavior is used.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'implementedFinders' => ['versions' => 'findVersions'],
        'versionTable' => 'version',
        'versionField' => 'version_id',
        'additionalVersionFields' => ['created'],
        'fields' => null,
        'foreignKey' => 'foreign_key',
        'referenceName' => null,
        'onlyDirty' => false
    ];

    /**
     * Constructor hook method.
     *
     * Implement this method to avoid having to overwrite
     * the constructor and call parent.
     *
     * @param array $config The configuration settings provided to this behavior.
     * @return void
     */
    public function initialize(array $config)
    {
        if ($this->_config['referenceName'] == null) {
            $this->_config['referenceName'] = $this->_referenceName();
        }
        $this->setupFieldAssociations($this->_config['versionTable']);
    }

    /**
     * Creates the associations between the bound table and every field passed to
     * this method.
     *
     * Additionally it creates a `version` HasMany association that will be
     * used for fetching all versions for each record in the bound table
     *
     * @param string $table the table name to use for storing each field version
     * @return void
     */
    public function setupFieldAssociations($table)
    {
        $options = [
            'table' => $table
        ];

        foreach ($this->_fields() as $field) {
            $this->versionAssociation($field, $options);
        }

        $this->versionAssociation(null, $options);
    }

    /**
     * Returns association object for all versions or single field version.
     *
     * @param string|null $field Field name for per-field association.
     * @param array $options Association options.
     * @return \Cake\ORM\Association
     */
    public function versionAssociation($field = null, $options = [])
    {
        $name = $this->_associationName($field);

        if (!$this->_table->associations()->has($name)) {
            $model = $this->_config['referenceName'];

            if ($field) {
                $this->_table->hasOne($name, $options + [
                    'className' => $this->_config['versionTable'],
                    'foreignKey' => $this->_config['foreignKey'],
                    'joinType' => 'LEFT',
                    'conditions' => [
                        $name . '.model' => $model,
                        $name . '.field' => $field,
                    ],
                    'propertyName' => $field . '_version'
                ]);
            } else {
                $this->_table->hasMany($name, $options + [
                    'className' => $this->_config['versionTable'],
                    'foreignKey' => $this->_config['foreignKey'],
                    'strategy' => 'subquery',
                    'conditions' => ["$name.model" => $model],
                    'propertyName' => '__version',
                    'dependent' => true
                ]);
            }
        }

        return $this->_table->association($name);
    }

    /**
     * Modifies the entity before it is saved so that versioned fields are persisted
     * in the database too.
     *
     * @param \Cake\Event\Event $event The beforeSave event that was fired
     * @param \Cake\Datasource\EntityInterface $entity The entity that is going to be saved
     * @param \ArrayObject $options the options passed to the save method
     * @return void
     */
    public function beforeSave(Event $event, EntityInterface $entity, ArrayObject $options)
    {
        $association = $this->versionAssociation();
        $name = $association->name();
        $newOptions = [$name => ['validate' => false]];
        $options['associated'] = $newOptions + $options['associated'];

        $fields = $this->_fields();
        $values = $entity->extract($fields, $this->_config['onlyDirty']);

        $model = $this->_config['referenceName'];
        $primaryKey = (array)$this->_table->primaryKey();
        $foreignKey = $this->_extractForeignKey($entity);
        $versionField = $this->_config['versionField'];

        if (isset($options['versionId'])) {
            $versionId = $options['versionId'];
        } else {
            $table = TableRegistry::get($this->_config['versionTable']);
            $preexistent = $table->find()
                ->select(['version_id'])
                ->where([
                    'model' => $model
                ] + $foreignKey)
                ->order(['id desc'])
                ->limit(1)
                ->hydrate(false)
                ->toArray();

            $versionId = Hash::get($preexistent, '0.version_id', 0) + 1;
        }
        $created = new DateTime();
        $new = [];
        foreach ($values as $field => $content) {
            if (in_array($field, $primaryKey) || $field == $versionField) {
                continue;
            }

            $data = [
                'version_id' => $versionId,
                'model' => $model,
                'field' => $field,
                'content' => $content,
                'created' => $created,
            ] + $foreignKey;

            $event = new Event('Model.Version.beforeSave', $this, $options);
            $userData = EventManager::instance()->dispatch($event);
            if (isset($userData->result) && is_array($userData->result)) {
                $data = array_merge($data, $userData->result);
            }

            $entityClass = $table->entityClass();
            $new[$field] = new $entityClass($data, [
                'useSetters' => false,
                'markNew' => true
            ]);
        }

        $entity->set($association->property(), $new);
        if (!empty($versionField) && in_array($versionField, $this->_table->schema()->columns())) {
            $entity->set($this->_config['versionField'], $versionId);
        }
    }

    /**
     * Unsets the temporary `__version` property after the entity has been saved
     *
     * @param \Cake\Event\Event $event The beforeSave event that was fired
     * @param \Cake\Datasource\EntityInterface $entity The entity that is going to be saved
     * @return void
     */
    public function afterSave(Event $event, EntityInterface $entity)
    {
        $property = $this->versionAssociation()->property();
        $entity->unsetProperty($property);
    }

    /**
     * Custom finder method used to retrieve all versions for the found records.
     *
     * Versioned values will be found for each entity under the property `_versions`.
     *
     * ### Example:
     *
     * {{{
     * $article = $articles->find('versions')->first();
     * $firstVersion = $article->get('_versions')[1];
     * }}}
     *
     * @param \Cake\ORM\Query $query The original query to modify
     * @param array $options Options
     * @return \Cake\ORM\Query
     */
    public function findVersions(Query $query, array $options)
    {
        $association = $this->versionAssociation();
        $name = $association->name();

        return $query
            ->contain([$name => function (Query $q) use ($name, $options, $query) {
                if (!empty($options['primaryKey'])) {
                    $foreignKey = (array)$this->_config['foreignKey'];
                    $aliasedFK = [];
                    foreach ($foreignKey as $field) {
                        $aliasedFK[] = "$name.$field";
                    }
                    $conditions = array_combine($aliasedFK, (array)$options['primaryKey']);
                    $q->where($conditions);
                }
                if (!empty($options['versionId'])) {
                    $q->where(["$name.version_id IN" => $options['versionId']]);
                }
                $q->where(["$name.field IN" => $this->_fields()]);

                return $q;
            }])
            ->formatResults([$this, 'groupVersions'], $query::PREPEND);
    }

    /**
     * Modifies the results from a table find in order to merge full version records
     * into each entity under the `_versions` key
     *
     * @param \Cake\Datasource\ResultSetInterface $results Results to modify.
     * @return \Cake\Collection\CollectionInterface
     */
    public function groupVersions($results)
    {
        $property = $this->versionAssociation()->property();

        return $results->map(function (EntityInterface $row) use ($property) {
            $versionField = $this->_config['versionField'];
            $versions = (array)$row->get($property);
            $grouped = new Collection($versions);

            $result = [];
            foreach ($grouped->combine('field', 'content', 'version_id') as $versionId => $keys) {
                $entityClass = $this->_table->entityClass();
                $versionData = [
                    $versionField => $versionId
                ];
                foreach ($this->_config['additionalVersionFields'] as $mappedField => $field) {
                    if (!is_string($mappedField)) {
                        $mappedField = 'version_' . $field;
                    }
                    $versionData[$mappedField] = $row->get($field);
                }

                $version = new $entityClass($keys + $versionData, [
                    'markNew' => false,
                    'useSetters' => false,
                    'markClean' => true
                ]);
                $result[$versionId] = $version;
            }

            $options = ['setter' => false, 'guard' => false];
            $row->set('_versions', $result, $options);
            unset($row[$property]);
            $row->clean();

            return $row;
        });
    }

    /**
     * Returns an array of fields to be versioned.
     *
     * @return array
     */
    protected function _fields()
    {
        $schema = $this->_table->schema();
        $fields = $schema->columns();
        if ($this->_config['fields'] !== null) {
            $fields = array_intersect($fields, (array)$this->_config['fields']);
        }

        return $fields;
    }

    /**
     * Returns an array with foreignKey value.
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity.
     * @return array
     */
    protected function _extractForeignKey($entity)
    {
        $foreignKey = (array)$this->_config['foreignKey'];
        $primaryKey = (array)$this->_table->primaryKey();
        $pkValue = $entity->extract($primaryKey);

        return array_combine($foreignKey, $pkValue);
    }

    /**
     * Returns default version association name.
     *
     * @param string $field Field name.
     * @return string
     */
    protected function _associationName($field = null)
    {
        $alias = Inflector::singularize($this->_table->alias());
        if ($field) {
            $field = Inflector::camelize($field);
        }

        return $alias . $field . 'Version';
    }

    /**
     * Returns reference name for identifying this model's records in version table.
     *
     * @return string
     */
    protected function _referenceName()
    {
        $table = $this->_table;
        $name = namespaceSplit(get_class($table));
        $name = substr(end($name), 0, -5);
        if (empty($name)) {
            $name = $table->table() ?: $table->alias();
            $name = Inflector::camelize($name);
        }

        return $name;
    }
}
