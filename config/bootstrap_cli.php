<?php
use Cake\Event\Event;
use Cake\Event\EventManager;
use Josegonzalez\Version\Event\VersionListener;

EventManager::instance()->attach(function (Event $event) {
    (new VersionListener($event))->execute();
}, 'Bake.beforeRender');
