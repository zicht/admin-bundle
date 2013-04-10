<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\AdminBundle\Event;

class Subscriber implements \Symfony\Component\EventDispatcher\EventSubscriberInterface
{
    /**
     * @{inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            AdminEvents::MENU_EVENT => 'addMenuItem'
        );
    }


    public function __construct(\Knp\Menu\MenuItem $root, \Knp\Menu\FactoryInterface $factory)
    {
        $this->menu = $root;
        $this->factory = $factory;
    }


    public function addMenuItem(\Zicht\Bundle\AdminBundle\Event\MenuEvent $e)
    {
        $this->menu->addChild(
            $this->factory->createFromArray($e->getMenuConfig())
        );
    }
}