<?php
namespace Josegonzalez\Version\Model\Behavior\Version;

use Cake\Collection\Collection;
use Cake\ORM\TableRegistry;

trait VersionTrait
{
    /**
     * Retrieves a specified version for the current entity
     *
     * @param int $versionId The version number to retrieve
     * @param bool $reset If true, will re-retrieve the related version collection
     * @return \Cake\ORM\Entity|null
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
     * @return \Cake\Collection\CollectionInterface
     */
    public function versions($reset = false)
    {
        if ($reset === false && $this->has('_versions')) {
            return $this->get('_versions');
        }

        /*
         * @var \Josegonzalez\Version\Model\Behavior\VersionBehavior $table
         * @var \Cake\Datasource\EntityInterface $this
         */
        $table = TableRegistry::get($this->source());
        $versions = $table->getVersions($this);
        $this->set('_versions', $versions);

        return $this->get('_versions');
    }
}
