<?php
declare(strict_types=1);

/**
 * Class VersionListener
 *
 * @category CakePHP-Plugin
 * @package  Josegonzalez\Version\Event
 * @author   Jose Diaz-Gonzalez <email-for-consulting@josediazgonzalez.com>
 * @license  MIT License (https://github.com/josegonzalez/cakephp-version/blob/master/LICENSE.txt)
 * @link     https://github.com/josegonzalez/cakephp-version
 */

namespace Josegonzalez\Version\Event;

use Cake\Datasource\ConnectionManager;
use Cake\Event\EventInterface;
use Cake\Utility\Hash;

/**
 * Class VersionListener
 *
 * @category CakePHP-Plugin
 * @package  Josegonzalez\Version\Event
 * @author   Jose Diaz-Gonzalez <email-for-consulting@josediazgonzalez.com>
 * @license  MIT License (https://github.com/josegonzalez/cakephp-version/blob/master/LICENSE.txt)
 * @link     https://github.com/josegonzalez/cakephp-version
 */
class VersionListener extends EventListener
{
    /**
     * Called before the entity template is rendered
     *
     * @param \Cake\Event\EventInterface $event An Event instance
     * @return void
     */
    public function beforeRenderEntity(EventInterface $event): void
    {
        $this->checkAssociation($event, 'versions');
    }

    /**
     * Called before the test case template is rendered
     *
     * @param \Cake\Event\EventInterface $event An Event instance
     * @return void
     */
    public function beforeRenderTestCase(EventInterface $event): void
    {
        $name = $event->subject->viewVars['subject'];
        $pattern = '/^' . preg_quote($name) . '_(\w+)_version$/';
        foreach (array_keys($event->subject->viewVars['fixtures']) as $key) {
            if (preg_match($pattern, $key)) {
                unset($event->subject->viewVars['fixtures'][$key]);
            }
        }
    }

    /**
     * Called before the table template is rendered
     *
     * @param \Cake\Event\EventInterface $event An Event instance
     * @return void
     */
    public function beforeRenderTable(EventInterface $event): void
    {
        $this->checkAssociation($event, 'versions');
        $this->fixVersionTables($event);
    }

    /**
     * Removes unnecessary associations
     *
     * @param \Cake\Event\EventInterface $event An Event instance
     * @return void
     */
    protected function fixVersionTables(EventInterface $event): void
    {
        if (!preg_match('/Versions$/', $event->subject->viewVars['name'])) {
            return;
        }

        unset($event->subject->viewVars['rulesChecker']['version_id']);
        foreach ($event->subject->viewVars['associations']['belongsTo'] as $i => $association) {
            if ($association['alias'] === 'Versions' && $association['foreignKey'] === 'version_id') {
                unset($event->subject->viewVars['associations']['belongsTo'][$i]);
            }
        }
    }

    /**
     * Attaches the behavior and modifies associations as necessary
     *
     * @param \Cake\Event\EventInterface $event An Event instance
     * @param string $tableSuffix a suffix for the primary table
     * @return bool true if modified, false otherwise
     */
    protected function checkAssociation(EventInterface $event, string $tableSuffix): bool
    {
        $subject = $event->subject;
        $connection = ConnectionManager::get($subject->viewVars['connection']);
        $schema = $connection->getSchemaCollection();

        $versionTable = sprintf('%s_%s', Hash::get($event->subject->viewVars, 'table'), $tableSuffix);
        if (!in_array($versionTable, $schema->listTables())) {
            return false;
        }

        $event->subject->viewVars['behaviors']['Josegonzalez/Version.Version'] = [
            sprintf("'versionTable' => '%s'", $versionTable),
        ];

        $event->subject->viewVars['associations']['belongsTo'] = $this->modifyBelongsTo($event);
        $event->subject->viewVars['rulesChecker'] = $this->modifyRulesChecker($event);

        return true;
    }

    /**
     * Removes unnecessary belongsTo associations
     *
     * @param \Cake\Event\EventInterface $event An Event instance
     * @return array
     */
    protected function modifyBelongsTo(EventInterface $event): array
    {
        $belongsTo = $event->subject->viewVars['associations']['belongsTo'];

        foreach ($belongsTo as $i => $association) {
            if ($association['alias'] !== 'Versions' || $association['foreignKey'] !== 'version_id') {
                continue;
            }

            unset($belongsTo[$i]);
        }

        return $belongsTo;
    }

    /**
     * Removes unnecessary rulesChecker entries
     *
     * @param \Cake\Event\EventInterface $event An Event instance
     * @return array
     */
    protected function modifyRulesChecker(EventInterface $event): array
    {
        $rulesChecker = $event->subject->viewVars['rulesChecker'];

        foreach ($rulesChecker as $key => $config) {
            if (Hash::get($config, 'extra') !== 'Versions' || $key !== 'version_id') {
                continue;
            }

            unset($rulesChecker[$key]);
        }

        return $rulesChecker;
    }
}
