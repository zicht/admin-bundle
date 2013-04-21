<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace ZichtTest\Bundle\AdminBundle\Event;

class AdminEventsTest extends \PHPUnit_Framework_TestCase
{
    function testClassConstants()
    {
        $this->assertTrue(defined('Zicht\Bundle\AdminBundle\Event\AdminEvents::MENU_EVENT'));
    }
}