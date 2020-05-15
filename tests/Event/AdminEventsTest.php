<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace ZichtTest\Bundle\AdminBundle\Event;

class AdminEventsTest extends \PHPUnit_Framework_TestCase
{
    public function testClassConstants()
    {
        $this->assertTrue(defined('Zicht\Bundle\AdminBundle\Event\AdminEvents::MENU_EVENT'));
    }
}
