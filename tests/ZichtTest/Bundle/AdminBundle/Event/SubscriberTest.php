<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */
namespace ZichtTest\Bundle\AdminBundle\Event;

use Zicht\Bundle\AdminBundle\Event\Propagator;
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
        $root = $this->getMockBuilder('Knp\Menu\MenuItem')->disableOriginalConstructor()->setMethods(array('addChild'))->getMock();
        $factory = $this->getMockBuilder('Knp\Menu\MenuFactory')->disableOriginalConstructor()->setMethods(array('createFromArray'))->getMock();
        $item = $this->getMockBuilder('Zicht\Bundle\AdminBundle\Event\MenuEvent')->setMethods(array('getMenuConfig'))->disableOriginalConstructor()->getMock();
        $itemConfig = array(
            'foo' => 'bar',
            'baz' => 'bat'
        );
        $bogusItem = rand(1, 100);
        $item->expects($this->once())->method('getMenuConfig')->will($this->returnValue($itemConfig));
        $factory->expects($this->once())->method('createFromArray')->with($itemConfig)->will($this->returnValue(
            $bogusItem
        ));
        $root->expects($this->once())->method('addChild')->with($bogusItem);

        $subs = new Subscriber($root, $factory);
        $subs->addMenuItem($item);
    }
}