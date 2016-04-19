<?php
use Cake\Event\Event;
use Cake\Event\EventManager;
use Josegonzalez\Version\Event\VersionListener;

EventManager::instance()->on('Bake.beforeRender', function (Event $event) {
    (new VersionListener($event))->execute();
});
