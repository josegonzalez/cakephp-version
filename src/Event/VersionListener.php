<?php
namespace Josegonzalez\Version\Event;

use Cake\Datasource\ConnectionManager;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

class VersionListener extends EventListener
{
    /**
     * Called before the entity template is rendered
     *
     * @param Event $event An Event instance
     * @return void
     */
    public function beforeRenderEntity(Event $event)
    {
        $this->_checkAssociation($event, 'versions');
    }

    /**
     * Called before the test case template is rendered
     *
     * @param Event $event An Event instance
     * @return void
     */
    public function beforeRenderTestCase(Event $event)
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
     * @param Event $event An Event instance
     * @return void
     */
    public function beforeRenderTable(Event $event)
    {
        $this->_checkAssociation($event, 'versions');
        $this->_fixVersionTables($event);
    }

    /**
     * Removes unnecessary associations
     *
     * @param Event $event An Event instance
     * @return void
     */
    protected function _fixVersionTables(Event $event)
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
     * @param Event $event An Event instance
     * @param string $tableSuffix a suffix for the primary table
     * @return bool true if modified, false otherwise
     */
    protected function _checkAssociation(Event $event, $tableSuffix)
    {
        $subject = $event->subject;
        $connection = ConnectionManager::get($subject->viewVars['connection']);
        $schema = $connection->schemaCollection();

        $versionTable = sprintf('%s_%s', Hash::get($event->subject->viewVars, 'table'), $tableSuffix);
        if (!in_array($versionTable, $schema->listTables())) {
            return false;
        }

        $event->subject->viewVars['behaviors']['Josegonzalez/Version.Version'] = [
            sprintf("'versionTable' => '%s'", $versionTable),
        ];

        $event->subject->viewVars['associations']['belongsTo'] = $this->_modifyBelongsTo($event);
        $event->subject->viewVars['rulesChecker'] = $this->_modifyRulesChecker($event);

        return true;
    }

    /**
     * Removes unnecessary belongsTo associations
     *
     * @param Event $event An Event instance
     * @return array
     */
    protected function _modifyBelongsTo(Event $event)
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
     * @param Event $event An Event instance
     * @return array
     */
    protected function _modifyRulesChecker(Event $event)
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
