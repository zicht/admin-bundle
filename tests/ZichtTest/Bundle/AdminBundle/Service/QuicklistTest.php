<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\AdminBundle {

    class QuicklistTest extends \PHPUnit_Framework_TestCase
    {
        public function setUp()
        {
            $this->doctrine = $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')
                ->setMethods(array('getRepository'))
                ->disableOriginalConstructor()->getMock();
            $this->pool     = $this->getMockBuilder('Sonata\AdminBundle\Admin\Pool')->disableOriginalConstructor()->getMock();
        }

        public function testQuicklist()
        {
            $q = new \Zicht\Bundle\AdminBundle\Service\Quicklist($this->doctrine, $this->pool);

            $q->addRepositoryConfig('a', array('repository' => array('b' => 'c')));
            $this->assertEquals(array('a' => array('repository' => array('b' => 'c'))), $q->getRepositoryConfigs());
        }


        public function testGetResults()
        {
            $q = new \Zicht\Bundle\AdminBundle\Service\Quicklist($this->doctrine, $this->pool);

            $q->addRepositoryConfig(
                'foo',
                array(
                    'repository' => 'Foo',
                    'fields'     => array('title')
                )
            );
            $repo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
                ->disableOriginalConstructor()
                ->setMethods(array('createQueryBuilder'))
                ->getMock();

            $repo->expects($this->once())->method('createQueryBuilder')->will($this->returnValue(new M\Qb(array(
                $r1 = new M\A(),
                $r2 = new M\B()
            ))));

            $this->doctrine->expects($this->once())->method('getRepository')->with('Foo')->will(
                $this->returnValue(
                    $repo
                )
            );
            $q->getResults('foo', 'bar');
        }
    }
}


namespace ZichtTest\Bundle\AdminBundle\M {
    class Eb
    {
        function __call($method, $args)
        {
            $this->calls[] = array($method, $args);

            return $this;
        }
    }

    class Qb
    {
        public $calls;

        public function __construct($results)
        {
            $this->results = $results;
        }

        function expr()
        {
            return new Eb();
        }


        function __call($method, $args)
        {
            $this->calls[] = array($method, $args);

            return $this;
        }


        function getQuery()
        {
            return new Q($this->results);
        }
    }


    class Q
    {
        public function __construct($results)
        {
            $this->results = $results;
        }

        function execute()
        {
            return $this->results;
        }
    }

    class A
    {
        function __toString() {
            return __CLASS__;
        }
    }

    class B
    {
        function __toString() {
            return __CLASS__;
        }
    }

}
