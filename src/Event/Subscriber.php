<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\Event;

use Knp\Menu\FactoryInterface;
use Knp\Menu\MenuItem;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Kernel event subscriber for the Admin menu
 */
class Subscriber implements EventSubscriberInterface
{
    /**
     * @var MenuItem
     */
    private $menu;

    /**
     * @var FactoryInterface
     */
    private $factory;

    public function __construct(MenuItem $root, FactoryInterface $factory)
    {
        $this->menu = $root;
        $this->factory = $factory;
    }

    public static function getSubscribedEvents()
    {
        return [
            AdminEvents::MENU_EVENT => 'addMenuItem',
        ];
    }

    /**
     * Add a child to the menu
     *
     * @return void
     */
    public function addMenuItem(MenuEvent $e)
    {
        $array = $e->getMenuConfig();

        $this->menu->addChild(
            $this->factory->createItem($array['name'], $array)
        );
    }
}
