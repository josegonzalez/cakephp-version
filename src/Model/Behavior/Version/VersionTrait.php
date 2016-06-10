<?php
namespace Josegonzalez\Version\Model\Behavior\Version;

use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use LogicException;

trait VersionTrait
{
    /**
     * Retrieves a specified version for the current entity
     *
     * @param int $versionId The version number to retrieve
     * @param bool $reset If true, will re-retrieve the related version collection
     * @return \Cake\ORM\Entity
     */
    public function version($versionId, $reset = false)
    {
        $versions = $this->versions($reset);
        if (empty($versions[$versionId])) {
            return null;
        }

        return $versions[$versionId];
    }

    /**
     * Retrieves the related versions for the current entity
     *
     * @param bool $reset If true, will re-retrieve the related version collection
     * @return \Cake\Collection\Collection
     */
    public function versions($reset = false)
    {
        if ($reset === false && $this->has('_versions')) {
            return $this->get('_versions');
        }

        $table = TableRegistry::get($this->source());
        $primaryKey = (array)$table->primaryKey();

        $query = $table->find('versions');
        $pkValue = $this->extract($primaryKey);
        $conditions = [];
        foreach ($pkValue as $key => $value) {
            $field = current($query->aliasField($key));
            $conditions[$field] = $value;
        }
        $entities = $query->where($conditions)->all();

        if (empty($entities)) {
            return [];
        }

        $entity = $entities->first();
        $this->set('_versions', $entity->get('_versions'));
        return $this->get('_versions');
    }
}
