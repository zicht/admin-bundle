<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace ZichtTest\Bundle\AdminBundle\Event;

use PHPUnit\Framework\TestCase;

class AdminEventsTest extends TestCase
{
    public function testClassConstants()
    {
        $this->assertTrue(defined('Zicht\Bundle\AdminBundle\Event\AdminEvents::MENU_EVENT'));
    }
}
