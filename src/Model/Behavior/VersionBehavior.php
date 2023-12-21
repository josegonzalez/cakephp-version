<?php
declare(strict_types=1);

/**
 * Class VersionBehavior
 *
 * @category CakePHP-Plugin
 * @package  Josegonzalez\Version\Model\Behavior
 * @author   Jose Diaz-Gonzalez <email-for-consulting@josediazgonzalez.com>
 * @license  MIT License (https://github.com/josegonzalez/cakephp-version/blob/master/LICENSE.txt)
 * @link     https://github.com/josegonzalez/cakephp-version
 */

namespace Josegonzalez\Version\Model\Behavior;

use ArrayObject;
use Cake\Collection\Collection;
use Cake\Collection\CollectionInterface;
use Cake\Database\TypeFactory;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\ResultSetInterface;
use Cake\Event\Event;
use Cake\ORM\Association;
use Cake\ORM\Behavior;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use DateTime;
use InvalidArgumentException;
use function Cake\Core\namespaceSplit;

/**
 * This behavior provides a way to version dynamic data by keeping versions
 * in a separate table linked to the original record from another one. Versioned
 * fields can be configured to override those in the main table when fetched or
 * put aside into another property for the same entity.
 *
 * If you want to retrieve all versions for each of the fetched records,
 * you can use the custom `versions` finders that is exposed to the table.
 *
 * @category CakePHP-Plugin
 * @package  Josegonzalez\Version\Model\Behavior
 * @author   Jose Diaz-Gonzalez <email-for-consulting@josediazgonzalez.com>
 * @license  MIT License (https://github.com/josegonzalez/cakephp-version/blob/master/LICENSE.txt)
 * @link     https://github.com/josegonzalez/cakephp-version
 */
class VersionBehavior extends Behavior
{
    use LocatorAwareTrait;

    /**
     * Default config
     *
     * These are merged with user-provided configuration when the behavior is used.
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'implementedFinders' => ['versions' => 'findVersions'],
        'versionTable' => 'version',
        'versionField' => 'version_id',
        'additionalVersionFields' => ['created'],
        'fields' => null,
        'foreignKey' => 'foreign_key',
        'referenceName' => null,
        'onlyDirty' => false,
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
    public function initialize(array $config): void
    {
        if ($this->_config['referenceName'] == null) {
            $this->_config['referenceName'] = $this->referenceName();
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
    public function setupFieldAssociations(string $table): void
    {
        $options = [
            'table' => $table,
        ];

        foreach ($this->fields() as $field) {
            $this->versionAssociation($field, $options);
        }

        $this->versionAssociation(null, $options);
    }

    /**
     * Returns association object for all versions or single field version.
     *
     * @param string|null $field   Field name for per-field association.
     * @param array       $options Association options.
     * @return \Cake\ORM\Association
     */
    public function versionAssociation(?string $field = null, array $options = []): Association
    {
        $name = $this->associationName($field);

        if (!$this->_table->associations()->has($name)) {
            $model = $this->_config['referenceName'];

            $options += [
                'className' => $this->_config['versionTable'],
                'foreignKey' => $this->_config['foreignKey'],
                'strategy' => 'subquery',
                'dependent' => true,
            ];

            if ($field) {
                $options += [
                    'conditions' => [
                        $name . '.model' => $model,
                        $name . '.field' => $field,
                    ],
                    'propertyName' => $field . '_version',
                ];
            } else {
                $options += [
                    'conditions' => [
                        $name . '.model' => $model,
                    ],
                    'propertyName' => '__version',
                ];
            }

            $this->_table->hasMany($name, $options);
        }

        return $this->_table->getAssociation($name);
    }

    /**
     * Modifies the entity before it is saved so that versioned fields are persisted
     * in the database too.
     *
     * @param \Cake\Event\Event                $event   The beforeSave event that was fired
     * @param \Cake\Datasource\EntityInterface $entity  The entity that is going to be saved
     * @param \ArrayObject                     $options the options passed to the save method
     * @return void
     */
    public function beforeSave(Event $event, EntityInterface $entity, ArrayObject $options): void
    {
        $association = $this->versionAssociation();
        $name = $association->getName();
        $newOptions = [$name => ['validate' => false]];
        $options['associated'] = $newOptions + $options['associated'];

        $fields = $this->fields();
        $values = $entity->extract($fields, $this->_config['onlyDirty']);

        $primaryKey = (array)$this->_table->getPrimaryKey();
        $versionField = $this->_config['versionField'];

        if (isset($options['versionId'])) {
            $versionId = $options['versionId'];
        } else {
            $versionId = $this->getVersionId($entity) + 1;
        }
        $created = new DateTime();
        $new = [];
        $entityClass = $this->getTableLocator()->get($this->_config['versionTable'])->getEntityClass();
        foreach ($values as $field => $content) {
            if (in_array($field, $primaryKey) || $field == $versionField) {
                continue;
            }

            $converted = $this->convertFieldsToType([$field => $content], 'toDatabase');

            $data = [
                'version_id' => $versionId,
                'model' => $this->_config['referenceName'],
                'field' => $field,
                'content' => $converted[$field],
                'created' => $created,
            ] + $this->extractForeignKey($entity);

            $userData = $this->_table->dispatchEvent('Model.Version.beforeSave', (array)$options);
            if ($userData !== null && $userData->getResult() !== null && is_array($userData->getResult())) {
                $data = array_merge($data, $userData->getResult());
            }

            $new[$field] = new $entityClass(
                $data,
                [
                'useSetters' => false,
                'markNew' => true,
                ]
            );
        }

        $entity->set($association->getProperty(), $new);
        if (!empty($versionField) && in_array($versionField, $this->_table->getSchema()->columns())) {
            $entity->set($this->_config['versionField'], $versionId);
        }
    }

    /**
     * Unsets the temporary `__version` property after the entity has been saved
     *
     * @param \Cake\Event\Event                $event  The beforeSave event that was fired
     * @param \Cake\Datasource\EntityInterface $entity The entity that is going to be saved
     * @return void
     */
    public function afterSave(Event $event, EntityInterface $entity): void
    {
        $property = $this->versionAssociation()->getProperty();
        $entity->unset($property);
    }

    /**
     * Return the last version id
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity.
     * @return int
     */
    public function getVersionId(EntityInterface $entity): int
    {
        $table = $this->getTableLocator()->get($this->_config['versionTable']);
        $extractedKey = $this->extractForeignKey($entity);

        //If any extracted key is null (in case of new entity), don't trigger db-query.
        foreach ($extractedKey as $key) {
            if ($key === null) {
                $preexistent = [];
                continue;
            }
        }

        if (!isset($preexistent)) {
            $preexistent = $table->find()
                ->select(['version_id'])
                ->where(
                    [
                        'model' => $this->_config['referenceName'],
                    ] + $extractedKey
                )
                ->orderBy(['id desc'])
                ->limit(1)
                ->enableHydration(false)
                ->toArray();
        }

        return Hash::get($preexistent, '0.version_id', 0);
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
     * @param \Cake\ORM\Query $query   The original query to modify
     * @param array           $options Options
     * @return \Cake\ORM\Query
     */
    public function findVersions(Query $query, array $options): Query
    {
        $association = $this->versionAssociation();
        $name = $association->getName();

        return $query
            ->contain(
                [$name => function (Query $q) use ($name, $options) {
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
                    $q->where(["$name.field IN" => $this->fields()]);

                    return $q;
                }]
            )
            ->formatResults($this->groupVersions(...), $query::PREPEND);
    }

    /**
     * Modifies the results from a table find in order to merge full version records
     * into each entity under the `_versions` key
     *
     * @param \Cake\Datasource\ResultSetInterface $results Results to modify.
     * @return \Cake\Collection\CollectionInterface
     */
    public function groupVersions(ResultSetInterface $results): CollectionInterface
    {
        $property = $this->versionAssociation()->getProperty();

        return $results->map(
            function (EntityInterface $row) use ($property) {
                $versionField = $this->_config['versionField'];
                $versions = (array)$row->get($property);
                $grouped = new Collection($versions);

                $result = [];
                foreach ($grouped->combine('field', 'content', 'version_id') as $versionId => $keys) {
                    $entityClass = $this->_table->getEntityClass();
                    $versionData = [
                    $versionField => $versionId,
                    ];

                    $keys = $this->convertFieldsToType($keys, 'toPHP');

                    /** @var \Cake\Datasource\EntityInterface $versionRow */
                    $versionRow = $grouped->match(['version_id' => $versionId])->first();

                    foreach ($this->_config['additionalVersionFields'] as $mappedField => $field) {
                        if (!is_string($mappedField)) {
                            $mappedField = 'version_' . $field;
                        }
                        $versionData[$mappedField] = $versionRow->get($field);
                    }

                    $version = new $entityClass(
                        $keys + $versionData,
                        [
                        'markNew' => false,
                        'useSetters' => false,
                        'markClean' => true,
                        ]
                    );
                    $result[$versionId] = $version;
                }

                $options = ['setter' => false, 'guard' => false];
                $row->set('_versions', $result, $options);
                unset($row[$property]);
                $row->clean();

                return $row;
            }
        );
    }

    /**
     * Returns the versions of a specific entity.
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity.
     * @return array<\Cake\Datasource\EntityInterface>
     */
    public function getVersions(EntityInterface $entity): array
    {
        $primaryKey = (array)$this->_table->getPrimaryKey();

        $query = $this->_table->find('versions');
        $pkValue = $entity->extract($primaryKey);
        $conditions = [];
        foreach ($pkValue as $key => $value) {
            $field = current($query->aliasField($key));
            $conditions[$field] = $value;
        }
        $entities = $query->where($conditions)->all();

        if ($entities->isEmpty()) {
            return [];
        }

        $entity = $entities->first();

        return $entity->get('_versions');
    }

    /**
     * Returns an array of fields to be versioned.
     *
     * @return array
     */
    protected function fields(): array
    {
        $schema = $this->_table->getSchema();
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
    protected function extractForeignKey(EntityInterface $entity): array
    {
        $foreignKey = (array)$this->_config['foreignKey'];
        $primaryKey = (array)$this->_table->getPrimaryKey();
        $pkValue = $entity->extract($primaryKey);

        return array_combine($foreignKey, $pkValue);
    }

    /**
     * Returns default version association name.
     *
     * @param string $field Field name.
     * @return string
     */
    protected function associationName(?string $field = null): string
    {
        $alias = Inflector::singularize($this->_table->getAlias());
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
    protected function referenceName(): string
    {
        $table = $this->_table;
        $name = namespaceSplit(get_class($table));
        $name = substr(end($name), 0, -5);
        if (empty($name)) {
            $name = $table->getTable() ?: $table->getAlias();
            $name = Inflector::camelize($name);
        }

        return $name;
    }

    /**
     * Converts fields to the appropriate type to be stored in the version, and
     * to be converted from the version record to the entity
     *
     * @param array  $fields    Fields to convert
     * @param string $direction Direction (toPHP or toDatabase)
     * @return array
     */
    protected function convertFieldsToType(array $fields, string $direction): array
    {
        if (!in_array($direction, ['toPHP', 'toDatabase'])) {
            $message = sprintf('Cannot convert type, Cake\Database\Type::%s does not exist', $direction);
            throw new InvalidArgumentException($message);
        }

        $driver = $this->_table->getConnection()->getDriver();
        foreach ($fields as $field => $content) {
            $column = $this->_table->getSchema()->getColumn($field);
            $type = TypeFactory::build($column['type']);

            $fields[$field] = $type->{$direction}($content, $driver);
        }

        return $fields;
    }
}
