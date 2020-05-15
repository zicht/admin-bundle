<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace ZichtTest\Bundle\AdminBundle\Event;

use Zicht\Bundle\AdminBundle\Event\MenuEvent;

class MenuEventTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorWillSetUrlAndTitle()
    {
        $e = new MenuEvent('url', 'title');
        $this->assertEquals('url', $e->getUrl());
        $this->assertEquals('title', $e->getTitle());

        $this->assertEquals(['name' => $e->getTitle(), 'uri' => $e->getUrl()], $e->getMenuConfig());
    }


    public function testOptions()
    {
        $e = new MenuEvent('url', 'title', ['foo' => 'bar']);
        $this->assertEquals(
            [
                'name' => $e->getTitle(),
                'uri' => $e->getUrl(),
                'foo' => 'bar'
            ],
            $e->getMenuConfig()
        );
    }
}
