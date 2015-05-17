<?php
namespace Josegonzalez\Version\Event;

use Cake\Event\Event as CakeEvent;
use Cake\Event\EventListenerInterface;
use Cake\Event\EventManagerTrait;

abstract class EventListener implements EventListenerInterface
{
    use EventManagerTrait;

    /**
     * The CakeEvent attached to this class
     *
     * @var \Cake\Event\Event $event Event instance.
     */
    protected $event;

    /**
     * Constructor.
     *
     * @param \Cake\Event\Event $event Event instance.
     */
    public function __construct(CakeEvent $event)
    {
        $this->event = $event;
        $this->eventManager()->attach($this);
    }

    /**
     * Dispatches all the attached events
     *
     * @return void
     */
    public function execute()
    {
        $methods = array_values($this->implementedEvents());
        foreach ($methods as $method) {
            $this->dispatchEvent(sprintf('Bake.%s', $method), null, $this->event->subject);
        }
    }

    /**
     * Check whether or not a bake call is a certain type.
     *
     * @param string|array $type The type of file you want to check.
     * @return bool Whether or not the bake template is the type you are checking.
     */
    public function isType($type)
    {
        $template = sprintf('Bake/%s.ctp', $type);
        return strpos($this->event->data[0], $template) !== false;
    }

    /**
     * Get the callbacks this class is interested in.
     *
     * @return array
     */
    public function implementedEvents()
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
            'tests/test_case' => 'beforeRenderTestCase',
        ];

        $events = [];
        foreach ($methodMap as $template => $method) {
            if ($this->isType($template) && method_exists($this, $method)) {
                $events[sprintf('Bake.%s', $method)] = $method;
            }
        }

        return $events;
    }
}
