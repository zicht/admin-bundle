<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\AdminBundle\Controller;
 
use Zicht\Bundle\AdminBundle\Controller\QuicklistController;
use Symfony\Component\DependencyInjection\Container;

class QuicklistControllerTest extends \PHPUnit_Framework_TestCase
{
    function setUp()
    {
        $ql = $this->getMockBuilder('Zicht\Bundle\AdminBundle\Service\Quicklist')
            ->disableOriginalConstructor()
            ->setMethods(array('getRepositoryConfigs', 'getResults'))
            ->getMock()
        ;

        $ql->expects($this->any())->method('getRepositoryConfigs')->will($this->returnValue(array(
            'foo' => 'bar'
        )));

        $container = new Container();
        $container->set('zicht_admin.quicklist', $ql);
        $controller = new QuicklistController();
        $controller->setContainer($container);

        $this->controller = $controller;
        $this->ql = $ql;
    }

    function testQuickListAction()
    {

        $req = new \Symfony\Component\HttpFoundation\Request();
        $res = $this->controller->quicklistAction($req);

        $this->assertEquals(array('repos' => array('foo' => 'bar')), $res);
    }


    function testQuickListActionJson()
    {
        $req2 = new \Symfony\Component\HttpFoundation\Request(array(
            'repo' => 'bat',
            'pattern' => 'qux'
        ));

        $this->ql->expects($this->once())->method('getResults')->with('bat', 'qux');
        $res2 = $this->controller->quicklistAction($req2);

        $this->assertInstanceOf(
            'Symfony\Component\HttpFoundation\JsonResponse',
            $res2
        );
    }
}