<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Interface for services that listen to one event and send out another based on that event.
 */
interface PropagationInterface
{
    /**
     * Build the event and forward it, using the event's own dispatcher.
     */
    public function buildAndForwardEvent(Event $e);
}
