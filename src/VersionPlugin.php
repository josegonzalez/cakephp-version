<?php
declare(strict_types=1);

namespace Josegonzalez\Version;

use Cake\Core\BasePlugin;
use Cake\Core\PluginApplicationInterface;
use Cake\Event\Event;
use Josegonzalez\Version\Event\VersionListener;

class VersionPlugin extends BasePlugin
{
    /**
     * @inheritDoc
     */
    public function bootstrap(PluginApplicationInterface $app): void
    {
        if (PHP_SAPI === 'cli') {
            $this->bootstrapCli($app);
        }
    }

    /**
     * CLI bootstrap
     *
     * @param \Cake\Core\PluginApplicationInterface $app Applicaction instance
     * @return void
     */
    public function bootstrapCli(PluginApplicationInterface $app): void
    {
        $app->getEventManager()->on('Bake.beforeRender', function (Event $event): void {
            (new VersionListener($event))->execute();
        });
    }
}
