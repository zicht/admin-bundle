<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\Doctrine;

use \Doctrine\Bundle\DoctrineBundle\Registry;
use \Symfony\Component\EventDispatcher\EventSubscriberInterface;
use \Symfony\Component\HttpKernel\Event\KernelEvent;
use \Symfony\Component\HttpKernel\HttpKernelInterface;
use \Symfony\Component\HttpKernel\KernelEvents;

/**
 * Makes sure the transaction is started on every admin url known to do writes.
 */
class TransactionalRequestListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => 'onKernelRequest'
        );
    }


    public function __construct(Registry $doctrine, $pattern)
    {
        $this->doctrine = $doctrine;
        $this->pattern = $pattern;
    }


    public function onKernelRequest(KernelEvent $event)
    {
        // TODO explicit transaction management in stead of this.
        // See ZICHTDEV-119
        if (
            $event->getRequestType() === HttpKernelInterface::MASTER_REQUEST
            && preg_match($this->pattern, $event->getRequest()->getRequestUri())
        ) {
            $this->doctrine->getConnection()->beginTransaction();
        }
    }
}