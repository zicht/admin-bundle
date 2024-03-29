<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\Doctrine;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Makes sure the transaction is started on every admin url known to do writes.
 */
class TransactionalRequestListener implements EventSubscriberInterface
{
    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * @var string
     */
    private $pattern;

    /**
     * @var bool
     */
    private $wasTxStarted;

    /**
     * @param string $pattern A Regular expression matching a url
     */
    public function __construct(Registry $doctrine, $pattern)
    {
        $this->doctrine = $doctrine;
        $this->pattern = $pattern;

        $this->wasTxStarted = false;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    /**
     * Starts a transaction within any url matching the constructor's $pattern parameter
     *
     * @return void
     */
    public function onKernelRequest(KernelEvent $event)
    {
        // TODO explicit transaction management in stead of this. See ZICHTDEV-119 for ideas on this
        if ($event->getRequestType() === HttpKernelInterface::MASTER_REQUEST
            && preg_match($this->pattern, $event->getRequest()->getRequestUri())) {
            $this->wasTxStarted = true;
            $this->doctrine->getConnection()->beginTransaction();
        }
    }

    /**
     * Commits the transaction, if started
     *
     * @return void
     */
    public function onKernelResponse(KernelEvent $event)
    {
        if ($event->getRequestType() === HttpKernelInterface::MASTER_REQUEST
            && $this->wasTxStarted
            && $this->doctrine->getConnection()->getTransactionIsolation() > 0) {
            if ($this->doctrine->getConnection()->isRollbackOnly()) {
                $this->doctrine->getConnection()->rollback();
            } else {
                $this->doctrine->getConnection()->commit();
            }
            $this->wasTxStarted = false;
        }
    }
}
