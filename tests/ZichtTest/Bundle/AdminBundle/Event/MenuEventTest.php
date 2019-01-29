<?php
/**
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */
namespace ZichtTest\Bundle\AdminBundle\Event;

use Zicht\Bundle\AdminBundle\Event\MenuEvent;

class MenuEventTest extends \PHPUnit_Framework_TestCase
{
    function testConstructorWillSetUrlAndTitle()
    {
        $e = new MenuEvent('url', 'title');
        $this->assertEquals('url', $e->getUrl());
        $this->assertEquals('title', $e->getTitle());

        $this->assertEquals(array('name' => $e->getTitle(), 'uri' => $e->getUrl()), $e->getMenuConfig());
    }


    function testOptions()
    {
        $e = new MenuEvent('url', 'title', array('foo' => 'bar'));
        $this->assertEquals(
            array(
                'name' => $e->getTitle(),
                'uri' => $e->getUrl(),
                'foo' => 'bar'
            ),
            $e->getMenuConfig()
        );
    }
}