<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\AdminBundle\Event;

use \Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Interface for services that listen to one event and send out another based on that event.
 */
interface PropagationInterface
{
    /**
     * Build the event and forward it, using the event's own dispatcher.
     *
     * @param \Symfony\Component\EventDispatcher\Event $e
     * @param string $eventName
     * @param EventDispatcherInterface $dispatcher
     * @return mixed
     */
    public function buildAndForwardEvent(Event $e, $eventName, EventDispatcherInterface $dispatcher);
}