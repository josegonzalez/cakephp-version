<?php
declare(strict_types=1);

/**
 * Trait VersionTrait
 *
 * @category CakePHP-Plugin
 * @package  Josegonzalez\Version\Model\Behavior\Version
 * @author   Jose Diaz-Gonzalez <email-for-consulting@josediazgonzalez.com>
 * @license  MIT License (https://github.com/josegonzalez/cakephp-version/blob/master/LICENSE.txt)
 * @link     https://github.com/josegonzalez/cakephp-version
 */

namespace Josegonzalez\Version\Model\Behavior\Version;

use Cake\Collection\CollectionInterface;
use Cake\Datasource\FactoryLocator;
use Cake\ORM\Entity;

/**
 * Trait VersionTrait
 *
 * @category CakePHP-Plugin
 * @package  Josegonzalez\Version\Model\Behavior\Version
 * @author   Jose Diaz-Gonzalez <email-for-consulting@josediazgonzalez.com>
 * @license  MIT License (https://github.com/josegonzalez/cakephp-version/blob/master/LICENSE.txt)
 * @link     https://github.com/josegonzalez/cakephp-version
 */
trait VersionTrait
{
    /**
     * Retrieves a specified version for the current entity
     *
     * @param int  $versionId The version number to retrieve
     * @param bool $reset     If true, will re-retrieve the related version collection
     * @return \Cake\ORM\Entity|null
     */
    public function version(int $versionId, bool $reset = false): ?Entity
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
    public function versions(bool $reset = false): CollectionInterface
    {
        if ($reset === false && $this->has('_versions')) {
            return $this->get('_versions');
        }

        /*
         * @var \Josegonzalez\Version\Model\Behavior\VersionBehavior $table
         * @var \Cake\Datasource\EntityInterface $this
         */
        $table = FactoryLocator::get('Table')->get($this->getSource());
        $versions = $table->getVersions($this);
        $this->set('_versions', $versions);

        return $this->get('_versions');
    }
}
