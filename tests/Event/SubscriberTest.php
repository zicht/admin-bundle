<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace ZichtTest\Bundle\AdminBundle\Event;

use PHPUnit\Framework\TestCase;
use Zicht\Bundle\AdminBundle\Event\AdminEvents;
use Zicht\Bundle\AdminBundle\Event\Subscriber;

class SubscriberTest extends TestCase
{
    public function testSubscription()
    {
        $this->assertEquals(
            [
                AdminEvents::MENU_EVENT => 'addMenuItem',
            ],
            Subscriber::getSubscribedEvents()
        );
    }

    public function testAddMenuItemWillAddItemToRootOfMenu()
    {
        $root = $this->getMockBuilder('Knp\Menu\MenuItem')
            ->disableOriginalConstructor()
            ->setMethods(['addChild'])
            ->getMock();

        $child = $this->getMockBuilder('Knp\Menu\MenuItem')
            ->disableOriginalConstructor()
            ->setMethods(['addChild'])
            ->getMock();

        $factory = $this->getMockBuilder('Knp\Menu\MenuFactory')
            ->disableOriginalConstructor()
            ->setMethods(['createItem'])
            ->getMock();

        $item = $this->getMockBuilder('Zicht\Bundle\AdminBundle\Event\MenuEvent')
            ->setMethods(['getMenuConfig'])
            ->disableOriginalConstructor()
            ->getMock();

        $itemConfig = [
            'name' => 'name',
            'foo' => 'bar',
            'baz' => 'bat'
        ];

        $item->expects($this->once())->method('getMenuConfig')->will($this->returnValue($itemConfig));
        $factory->expects($this->once())->method('createItem')->with('name', $itemConfig)->will(
            $this->returnValue(
                $child
            )
        );
        $root->expects($this->once())->method('addChild')->with($child);

        $subs = new Subscriber($root, $factory);
        $subs->addMenuItem($item);
    }
}
