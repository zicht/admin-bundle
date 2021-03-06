<?php
// phpcs:disable Zicht.Commenting.FunctionComment.Missing,Zicht.NamingConventions.Functions.NestedDefinition,PSR1.Classes.ClassDeclaration.MultipleClasses
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace ZichtTest\Bundle\AdminBundle {

use Zicht\Bundle\AdminBundle\Service\Quicklist;

    class QuicklistTest extends \PHPUnit_Framework_TestCase
    {
        public function setUp()
        {
            $this->doctrine = $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')
                ->setMethods(['getRepository'])
                ->disableOriginalConstructor()->getMock();
            $this->pool = $this->getMockBuilder('Sonata\AdminBundle\Admin\Pool')->disableOriginalConstructor()->getMock();
            $this->pool->expects($this->any())->method('getAdminClasses')->will(
                $this->returnValue(
                    [
                        'Foo' => [
                            'bar',
                            'bar2',
                        ],
                    ]
                )
            );
        }

        public function testQuicklist()
        {
            $q = new Quicklist($this->doctrine, $this->pool);

            $q->addRepositoryConfig('a', ['repository' => ['b' => 'c']]);
            $this->assertEquals(['a' => ['repository' => ['b' => 'c']]], $q->getRepositoryConfigs(false));
            $this->assertEquals([], $q->getRepositoryConfigs(true));
        }


        public function testGetResults()
        {
            $q = new Quicklist($this->doctrine, $this->pool);

            $q->addRepositoryConfig(
                'foo',
                [
                    'repository' => 'Foo',
                    'fields' => ['title'],
                ]
            );
            $repo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
                ->disableOriginalConstructor()
                ->setMethods(['createQueryBuilder'])
                ->getMock();

            $repo->expects($this->once())->method('createQueryBuilder')->will(
                $this->returnValue(
                    new M\Qb([
                        $r1 = new M\A(),
                        $r2 = new M\B(),
                    ])
                )
            );

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
        public function __call($method, $args)
        {
            $this->calls[] = [$method, $args];

            return $this;
        }
    }

    class Qb
    {
        /** @var array */
        public $calls;

        public function __construct($results)
        {
            $this->results = $results;
        }

        public function expr()
        {
            return new Eb();
        }


        public function __call($method, $args)
        {
            $this->calls[] = [$method, $args];

            return $this;
        }


        public function getQuery()
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

        public function execute()
        {
            return $this->results;
        }
    }

    class A
    {
        public function __toString()
        {
            return __CLASS__;
        }
    }

    class B
    {
        public function __toString()
        {
            return __CLASS__;
        }
    }
}
