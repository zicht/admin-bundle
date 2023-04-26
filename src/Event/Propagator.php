<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\Event;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Service that propagates events as other types, using PropagtionInterface instances
 */
class Propagator
{
    /**
     * @var PropagationInterface[][]
     */
    protected $propagations;

    public function __construct()
    {
        $this->propagations = [];
    }

    /**
     * Add a propagation for the specified event type.
     *
     * @param string $eventType
     * @param PropagationInterface $builder
     * @return void
     */
    public function registerPropagation($eventType, $builder)
    {
        $this->propagations[$eventType][] = $builder;
    }

    /**
     * Builds and forwards the event for all progragations registered for the specified event type.
     *
     * @param string $eventName
     * @return void
     */
    public function onEvent(Event $anyEvent, $eventName, EventDispatcherInterface $dispatcher)
    {
        if (isset($this->propagations[$eventName])) {
            foreach ($this->propagations[$eventName] as $builder) {
                $builder->buildAndForwardEvent($anyEvent);
            }
        }
    }
}
