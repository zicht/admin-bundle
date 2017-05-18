<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace ZichtTest\Bundle\AdminBundle\Twig;

use Zicht\Bundle\AdminBundle\Twig\Extension;

class MyModel
{
}

class ExtensionTest extends \PHPUnit_Framework_TestCase
{
    function testExtensionConfiguration()
    {
        $e = new Extension(
            $this->getMockBuilder('Sonata\AdminBundle\Admin\Pool')->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')->disableOriginalConstructor()->getMock()
        );

        $fn = $e->getFunctions();
        $this->assertInstanceOf('Twig_SimpleFunction', $fn['admin_url']);
        $this->assertEquals('zicht_admin', $e->getName());
        return $e;
    }


    function testAdminUrlForModelObject()
    {
        $e = new Extension(
            $sonata = $this->getMockBuilder('Sonata\AdminBundle\Admin\Pool')
                ->disableOriginalConstructor()
                ->setMethods(array('getAdminByClass', 'getAdminByAdminCode'))
                ->getMock()
            ,
            $doctrine = $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')
                ->disableOriginalConstructor()
                ->getMock()
        );
        $admin = $this->getMockBuilder('Sonata\AdminBundle\Admin\Admin')->disableOriginalConstructor()->setMethods(array('generateObjectUrl'))->getMock();
        $sonata->expects($this->once())
            ->method('getAdminByClass')
            ->with('ZichtTest\Bundle\AdminBundle\Twig\MyModel')
            ->will($this->returnValue($admin));
        $model = new MyModel();
        $admin->expects($this->once())->method('generateObjectUrl')->with('edit', $model);
        $e->adminUrl($model, 'edit');
    }


    function testAdminUrlForString()
    {
        $e = new Extension(
            $sonata = $this->getMockBuilder('Sonata\AdminBundle\Admin\Pool')
                ->disableOriginalConstructor()
                ->setMethods(array('getAdminByClass', 'getAdminByAdminCode'))
                ->getMock()
            ,
            $doctrine = $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')
                ->disableOriginalConstructor()
                ->getMock()
        );
        $admin = $this->getMockBuilder('Sonata\AdminBundle\Admin\Admin')->disableOriginalConstructor()->setMethods(array('generateUrl'))->getMock();
        $sonata->expects($this->once())
            ->method('getAdminByAdminCode')
            ->with('my_model')
            ->will($this->returnValue($admin));
        $admin->expects($this->once())->method('generateUrl')->with('list');
        $e->adminUrl('my_model', 'list');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testAdminUrlForUnsupportedObjectWillThrowInvalidArgumentException()
    {
        $e = new Extension(
            $sonata = $this->getMockBuilder('Sonata\AdminBundle\Admin\Pool')
                ->disableOriginalConstructor()
                ->setMethods(array('getAdminByClass', 'getAdminByAdminCode'))
                ->getMock()
            ,
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


    /**
     *
     */
    function testAdminUrlSupportsDoctrineNamespaces()
    {
        $e = new Extension(
            $sonata = $this->getMockBuilder('Sonata\AdminBundle\Admin\Pool')
                ->disableOriginalConstructor()
                ->setMethods(array('getAdminByClass', 'getAdminByAdminCode'))
                ->getMock()
            ,
            $doctrine = $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')
                ->disableOriginalConstructor()
                ->setMethods(array('getAliasNamespace'))
                ->getMock()
        );
        $doctrine->expects($this->once())->method('getAliasNamespace')->with('My')->will($this->returnValue('ZichtTest\Bundle\AdminBundle\Twig'));
        $admin = $this->getMockBuilder('Sonata\AdminBundle\Admin\Admin')->disableOriginalConstructor()->setMethods(array('generateUrl'))->getMock();
        $sonata->expects($this->once())
            ->method('getAdminByClass')
            ->with('ZichtTest\Bundle\AdminBundle\Twig\MyModel')
            ->will($this->returnValue($admin));
        $admin->expects($this->once())->method('generateUrl');
        $e->adminUrl('My:MyModel', 'list');
    }


    /**
     * @expectedException \InvalidArgumentException
     */
    function testAdminUrlWithAnythingElseThrowsInvalidArgumentException()
    {
        $e = new Extension(
            $this->getMockBuilder('Sonata\AdminBundle\Admin\Pool')
                ->disableOriginalConstructor()
                ->getMock()
            ,
            $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')
                ->disableOriginalConstructor()
                ->getMock()
        );
        $e->adminUrl(1, 2);
    }
}