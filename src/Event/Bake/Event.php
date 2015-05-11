<?php
namespace Josegonzalez\Version\Event\Bake;

use Cake\Event\Event as CakeEvent;
use Cake\Event\EventListenerInterface;
use Cake\Event\EventManagerTrait;

abstract class Event implements EventListenerInterface
{
    use EventManagerTrait;

    protected $event;

    public function __construct(CakeEvent $event)
    {
        $this->event = $event;
        $this->implementedEvents = $this->constructEvents();
        $this->eventManager()->attach($this);
    }

    public function __invoke()
    {
        $methods = array_values($this->implementedEvents());
        foreach ($methods as $method) {
            $this->dispatchEvent(sprintf('Bake.%s', $method), null, $this->event->subject);
        }
    }

    public function constructEvents()
    {
        $methodMap = [
            'config/routes' => 'beforeRenderRoutes',
            'Controller/component' => 'beforeRenderComponent',
            'Controller/controller' => 'beforeRenderController',
            'Model/behavior' => 'beforeRenderBehavior',
            'Model/entity' => 'beforeRenderEntity',
            'Model/table' => 'beforeRenderTable',
            'Shell/shell' => 'beforeRenderShell',
            'View/cell' => 'beforeRenderCell',
            'View/helper' => 'beforeRenderHelper',
        ];

        $events = [];
        foreach ($methodMap as $template => $method) {
            if ($this->isType($template) && method_exists($this, $method)) {
                $events[sprintf('Bake.%s', $method)] = $method;
            }
        }

        return $events;
    }

    public function isType($type)
    {
        $template = sprintf('Bake/%s.ctp', $type);
        return strpos($this->event->data[0], $template) !== false;
    }

    public function implementedEvents()
    {
        return $this->implementedEvents;
    }
}
