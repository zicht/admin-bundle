<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\AdminMenu;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\Event;
use Zicht\Bundle\AdminBundle\Event\AdminEvents;
use Zicht\Bundle\AdminBundle\Event\MenuEvent;
use Zicht\Bundle\AdminBundle\Event\PropagationInterface;
use Zicht\Bundle\PageBundle\Event\PageViewEvent;

/**
 * Propagates a PageView event as an AdminMenu event.
 */
class EventPropagationBuilder implements PropagationInterface
{
    /** @var array */
    private $hosts;

    /** @var RequestStack */
    private $requestStack;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(RequestStack $requestStack, array $hosts, EventDispatcherInterface $eventDispatcher)
    {
        $this->hosts = $hosts;
        $this->requestStack = $requestStack;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Build the relevant event and forward it.
     *
     * @return void
     */
    public function buildAndForwardEvent(Event $e)
    {
        if (!$e instanceof PageViewEvent) {
            return;
        }
        if ($request = $this->requestStack->getMainRequest()) {
            $host = $request->getHost();
            foreach ($this->hosts as $otherHost) {
                if ($otherHost != $host) {
                    $this->eventDispatcher->dispatch(
                        new MenuEvent(sprintf('%s:\\\\%s%s', $request->getScheme(), $otherHost, $request->getRequestUri()), $otherHost),
                        AdminEvents::MENU_EVENT
                    );
                }
            }
        }
    }
}
