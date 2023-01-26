<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace ZichtTest\Bundle\AdminBundle\Controller;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Zicht\Bundle\AdminBundle\Controller\QuicklistController;

class QuicklistControllerTest extends TestCase
{
    public function setUp(): void
    {
        $ql = $this->getMockBuilder('Zicht\Bundle\AdminBundle\Service\Quicklist')
            ->disableOriginalConstructor()
            ->setMethods(['getRepositoryConfigs', 'getResults'])
            ->getMock();

        $ql->expects($this->any())->method('getRepositoryConfigs')->will(
            $this->returnValue([
                'foo' => 'bar',
            ])
        );

        $container = new Container();
        $container->set('zicht_admin.quicklist', $ql);
        $controller = new QuicklistController();
        $controller->setContainer($container);

        $this->controller = $controller;
        $this->ql = $ql;
    }

    public function testQuickListActionJson()
    {
        $req2 = new Request([
            'repo' => 'bat',
            'pattern' => 'qux',
        ]);

        $this->ql->expects($this->once())->method('getResults')->with('bat', 'qux');
        $res2 = $this->controller->quicklistAction($req2);

        $this->assertInstanceOf(
            'Symfony\Component\HttpFoundation\JsonResponse',
            $res2
        );
    }
}
