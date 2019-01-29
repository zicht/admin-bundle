<?php
/**
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */
namespace ZichtTest\Bundle\AdminBundle\Event;

use Zicht\Bundle\AdminBundle\Event\Subscriber;

class SubscriberTest extends \PHPUnit_Framework_TestCase
{
    function testSubscription()
    {
        $this->assertEquals(
            array(
                \Zicht\Bundle\AdminBundle\Event\AdminEvents::MENU_EVENT => 'addMenuItem'
            ),
            Subscriber::getSubscribedEvents()
        );
    }
    
    function testAddMenuItemWillAddItemToRootOfMenu()
    {
        $root = $this->getMockBuilder('Knp\Menu\MenuItem')
            ->disableOriginalConstructor()
            ->setMethods(array('addChild'))
            ->getMock();

        $child = $this->getMockBuilder('Knp\Menu\MenuItem')
            ->disableOriginalConstructor()
            ->setMethods(array('addChild'))
            ->getMock();

        $factory = $this->getMockBuilder('Knp\Menu\MenuFactory')
            ->disableOriginalConstructor()
            ->setMethods(array('createItem'))
            ->getMock();

        $item = $this->getMockBuilder('Zicht\Bundle\AdminBundle\Event\MenuEvent')
            ->setMethods(array('getMenuConfig'))
            ->disableOriginalConstructor()
            ->getMock();

        $itemConfig = array(
            'name' => 'name',
            'foo' => 'bar',
            'baz' => 'bat'
        );

        $item->expects($this->once())->method('getMenuConfig')->will($this->returnValue($itemConfig));
        $factory->expects($this->once())->method('createItem')->with('name', $itemConfig)->will($this->returnValue(
            $child
        ));
        $root->expects($this->once())->method('addChild')->with($child);

        $subs = new Subscriber($root, $factory);
        $subs->addMenuItem($item);
    }
}