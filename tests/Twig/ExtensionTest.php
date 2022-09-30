<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace ZichtTest\Bundle\AdminBundle\Twig;

use PHPUnit\Framework\TestCase;
use Twig\TwigFunction;
use Zicht\Bundle\AdminBundle\Twig\Extension;

class MyModel
{
}

class ExtensionTest extends TestCase
{
    public function setUp(): void
    {
        $this->markTestSkipped('Disable until resolving mocking of final class Pool');
    }

    public function testExtensionConfiguration()
    {
        $e = new Extension(
            $this->getMockBuilder('Sonata\AdminBundle\Admin\Pool')->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')->disableOriginalConstructor()->getMock()
        );

        $fn = $e->getFunctions();
        $this->assertInstanceOf(TwigFunction::class, $fn['admin_url']);
        $this->assertEquals('zicht_admin', $e->getName());
        return $e;
    }

    public function testAdminUrlForModelObject()
    {
        $e = new Extension(
            $sonata = $this->getMockBuilder('Sonata\AdminBundle\Admin\Pool')
                ->disableOriginalConstructor()
                ->setMethods(['getAdminByClass', 'getAdminByAdminCode'])
                ->getMock(),
            $doctrine = $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')
                ->disableOriginalConstructor()
                ->getMock()
        );
        $admin = $this->getMockBuilder('Sonata\AdminBundle\Admin\Admin')->disableOriginalConstructor()->setMethods(['generateObjectUrl'])->getMock();
        $sonata->expects($this->once())
            ->method('getAdminByClass')
            ->with('ZichtTest\Bundle\AdminBundle\Twig\MyModel')
            ->will($this->returnValue($admin));
        $model = new MyModel();
        $admin->expects($this->once())->method('generateObjectUrl')->with('edit', $model);
        $e->adminUrl($model, 'edit');
    }

    public function testAdminUrlForString()
    {
        $e = new Extension(
            $sonata = $this->getMockBuilder('Sonata\AdminBundle\Admin\Pool')
                ->disableOriginalConstructor()
                ->setMethods(['getAdminByClass', 'getAdminByAdminCode'])
                ->getMock(),
            $doctrine = $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')
                ->disableOriginalConstructor()
                ->getMock()
        );
        $admin = $this->getMockBuilder('Sonata\AdminBundle\Admin\Admin')->disableOriginalConstructor()->setMethods(['generateUrl'])->getMock();
        $sonata->expects($this->once())
            ->method('getAdminByAdminCode')
            ->with('my_model')
            ->will($this->returnValue($admin));
        $admin->expects($this->once())->method('generateUrl')->with('list');
        $e->adminUrl('my_model', 'list');
    }

    public function testAdminUrlForUnsupportedObjectWillThrowInvalidArgumentException()
    {
        $this->expectException('\InvalidArgumentException');
        $e = new Extension(
            $sonata = $this->getMockBuilder('Sonata\AdminBundle\Admin\Pool')
                ->disableOriginalConstructor()
                ->setMethods(['getAdminByClass', 'getAdminByAdminCode'])
                ->getMock(),
            $doctrine = $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')
                ->disableOriginalConstructor()
                ->getMock()
        );
        $sonata->expects($this->once())
            ->method('getAdminByAdminCode')
            ->with('unknown_model')
            ->will($this->returnValue(null));
        $e->adminUrl('unknown_model', 'list');
    }

    public function testAdminUrlSupportsDoctrineNamespaces()
    {
        $e = new Extension(
            $sonata = $this->getMockBuilder('Sonata\AdminBundle\Admin\Pool')
                ->disableOriginalConstructor()
                ->setMethods(['getAdminByClass', 'getAdminByAdminCode'])
                ->getMock(),
            $doctrine = $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')
                ->disableOriginalConstructor()
                ->setMethods(['getAliasNamespace'])
                ->getMock()
        );
        $doctrine->expects($this->once())->method('getAliasNamespace')->with('My')->will($this->returnValue('ZichtTest\Bundle\AdminBundle\Twig'));
        $admin = $this->getMockBuilder('Sonata\AdminBundle\Admin\Admin')->disableOriginalConstructor()->setMethods(['generateUrl'])->getMock();
        $sonata->expects($this->once())
            ->method('getAdminByClass')
            ->with('ZichtTest\Bundle\AdminBundle\Twig\MyModel')
            ->will($this->returnValue($admin));
        $admin->expects($this->once())->method('generateUrl');
        $e->adminUrl('My:MyModel', 'list');
    }

    public function testAdminUrlWithAnythingElseThrowsInvalidArgumentException()
    {
        $this->expectException('\InvalidArgumentException');
        $e = new Extension(
            $this->getMockBuilder('Sonata\AdminBundle\Admin\Pool')
                ->disableOriginalConstructor()
                ->getMock(),
            $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')
                ->disableOriginalConstructor()
                ->getMock()
        );
        $e->adminUrl(1, 2);
    }
}
