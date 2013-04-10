<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\AdminBundle\Event;

use Symfony\Component\EventDispatcher\Event;
 
interface PropagationInterface
{
    function buildAndForwardEvent(Event $e);
}