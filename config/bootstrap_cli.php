<?php
use Cake\Event\Event;
use Cake\Event\EventManager;
use Josegonzalez\Version\Event\Bake\TableEvent;

EventManager::instance()->attach(function (Event $event) {
    $tableEvent = new TableEvent($event);
    $tableEvent();
}, 'Bake.beforeRender');
