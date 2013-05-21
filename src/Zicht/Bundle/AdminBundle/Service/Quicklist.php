<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\AdminBundle\Service;
 
use Sonata\AdminBundle\Admin\Pool;
use Doctrine\Bundle\DoctrineBundle\Registry;

class Quicklist
{
    public function __construct(Registry $doctrine, Pool $pool)
    {
        $this->doctrine = $doctrine;
        $this->adminPool = $pool;
        $this->repos = array();
    }


    public function addRepositoryConfig($name, $config)
    {
        $this->repos[$name] = $config;
    }


    public function getRepositoryConfigs()
    {
        return $this->repos;
    }


    public function getResults($repository, $pattern)
    {
        $repoConfig = $this->repos[$repository];
        /** @var $q \Doctrine\ORM\QueryBuilder */
        $q = $this->doctrine
            ->getRepository($repoConfig['repository'])
            ->createQueryBuilder('i')
            ->setMaxResults(10)
        ;
        $eb = $q->expr();
        $expr = $eb->orX();
        foreach ($repoConfig['fields'] as $fieldName) {
            $expr->add($eb->like('i.' . $fieldName, ':pattern'));
        }
        $q->where($expr);

        $results = array();
        foreach ($q->getQuery()->execute(array('pattern' => '%' . $pattern . '%')) as $record) {
            $admin = $this->adminPool->getAdminByClass(get_class($record));
            if (!$admin) {
                $admin = $this->adminPool->getAdminByClass(get_parent_class($record));
            }
            $results[]= array(
                'label' => (string) $record,
                'value' => (string) $record,
                'url' => ($admin ? $admin->generateObjectUrl('edit', $record) : null)
            );
        }
        return $results;
    }
}