<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\AdminBundle\Event;

use \Symfony\Component\EventDispatcher\Event;


/**
 * Service that propagates events as other types, using PropagtionInterface instances
 */
class Propagator
{
    /**
     * @var PropagationInterface[][]
     */
    protected $propagations;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->propagations = array();
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
        $this->propagations[$eventType][]= $builder;
    }

    /**
     * Builds and forwards the event for all progragations registered for the specified event type.
     *
     * @param \Symfony\Component\EventDispatcher\Event $anyEvent
     * @return void
     */
    public function onEvent(Event $anyEvent)
    {
        if (isset($this->propagations[$anyEvent->getName()])) {
            foreach ($this->propagations[$anyEvent->getName()] as $builder) {
                $builder->buildAndForwardEvent($anyEvent);
            }
        }
    }
}