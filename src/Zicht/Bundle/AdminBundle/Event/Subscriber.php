<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\AdminBundle\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Knp\Menu\MenuItem;
use Knp\Menu\FactoryInterface;

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

    /**
     * @{inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            AdminEvents::MENU_EVENT => 'addMenuItem'
        );
    }


    /**
     * Constructor
     *
     * @param \Knp\Menu\MenuItem $root
     * @param \Knp\Menu\FactoryInterface $factory
     */
    public function __construct(MenuItem $root, FactoryInterface $factory)
    {
        $this->menu = $root;
        $this->factory = $factory;
    }


    /**
     * Add a child to the menu
     *
     * @param MenuEvent $e
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