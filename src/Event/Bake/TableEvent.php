<?php
namespace Josegonzalez\Version\Event\Bake;

use Cake\Datasource\ConnectionManager;
use Cake\Event\Event as CakeEvent;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

class TableEvent extends Event
{
    public function beforeRenderTable(CakeEvent $event)
    {
        $subject = $event->subject;
        $connection = ConnectionManager::get($subject->viewVars['connection']);
        $schema = $connection->schemaCollection();

        $tableSuffixes = ['revision', 'revisions', 'version', 'versions'];
        foreach ($tableSuffixes as $tableSuffix) {
            $versionTable = sprintf('%s_%s', Hash::get($subject->viewVars, 'table'), $tableSuffix);

            if (in_array($versionTable, $schema->listTables())) {
                $event->subject->viewVars['behaviors']['Josegonzalez/Version.Version'] = [
                    sprintf("'versionTable' => '%s'", $versionTable),
                ];
                break;
            }
        }

        return $event;
    }
}
