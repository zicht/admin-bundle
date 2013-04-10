<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\AdminBundle\Event;
 
class Propagator
{
    /**
     * @var PropagationInterface[][]
     */
    protected $propagations;

    function __construct()
    {
    }


    function registerPropagation($eventType, $builder)
    {
        $this->propagations[$eventType][]= $builder;
    }


    function onEvent(\Symfony\Component\EventDispatcher\Event $anyEvent)
    {
        if (isset($this->propagations[$anyEvent->getName()])) {
            foreach ($this->propagations[$anyEvent->getName()] as $builder) {
                $builder->buildAndForwardEvent($anyEvent);
            }
        }
    }
}