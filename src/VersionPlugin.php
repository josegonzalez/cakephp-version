<?php
declare(strict_types=1);

namespace Josegonzalez\Version;

use Cake\Core\BasePlugin;
use Cake\Core\PluginApplicationInterface;
use Cake\Event\Event;
use Josegonzalez\Version\Event\VersionListener;

class VersionPlugin extends BasePlugin
{
    public function bootstrap(PluginApplicationInterface $app): void
    {
        if (PHP_SAPI === 'cli') {
            $this->bootstrapCli($app);
        }
    }

    public function bootstrapCli(PluginApplicationInterface $app): void
    {
        $app->getEventManager()->on('Bake.beforeRender', function (Event $event) {
            (new VersionListener($event))->execute();
        });

    }
}