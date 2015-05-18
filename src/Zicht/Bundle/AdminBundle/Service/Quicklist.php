<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\AdminBundle\Service;
 
use \Sonata\AdminBundle\Admin\Pool;
use \Doctrine\Bundle\DoctrineBundle\Registry;

/**
 * Quick list service
 */
class Quicklist
{
    /**
     * Constructor.
     *
     * @param \Doctrine\Bundle\DoctrineBundle\Registry $doctrine
     * @param \Sonata\AdminBundle\Admin\Pool $pool
     */
    public function __construct(Registry $doctrine, Pool $pool)
    {
        $this->doctrine = $doctrine;
        $this->adminPool = $pool;
        $this->repos = array();
    }


    /**
     * Add a repository configuration
     *
     * @param string $name
     * @param array $config
     * @return void
     */
    public function addRepositoryConfig($name, $config)
    {
        $this->repos[$name] = $config;
    }


    /**
     * Returns all configurations
     *
     * @param bool $exposedOnly
     * @return array
     */
    public function getRepositoryConfigs($exposedOnly = true)
    {
        if ($exposedOnly) {
            return array_filter($this->repos, function($item) {
                return isset($item['exposed']) && $item['exposed'] === true;
            });
        }
        return $this->repos;
    }


    /**
     * Get the result suggestions for the passed pattern
     *
     * @param string $repository
     * @param string $pattern
     * @param null|sytring $language
     * @return array
     */
    public function getResults($repository, $pattern, $language = null)
    {
        $queryResults = $this->findRecords($repository, $pattern, $language);

        $results = array();
        foreach ($queryResults as $record) {
            $admin = $this->adminPool->getAdminByClass(get_class($record));
            if (!$admin) {
                $admin = $this->adminPool->getAdminByClass(get_parent_class($record));
            }
            $resultRecord = $this->createResultRecord($record, $admin);
            $results[] = $resultRecord;
        }

        return $results;
    }


    /**
     * Return a single record by it's id. Used to map the front-end variable back to an object from the repository.
     *
     * @param string $repository
     * @param mixed $id
     * @return object
     */
    public function getOne($repository, $id)
    {
        $repoConfig = $this->repos[$repository];
        /** @var $q \Doctrine\ORM\QueryBuilder */
        return $this->doctrine
            ->getRepository($repoConfig['repository'])
            ->find($id)
        ;
    }

    /**
     * @param $repository
     * @param $pattern
     * @param null|string $language
     * @return mixed
     */
    private function findRecords($repository, $pattern, $language = null)
    {
        $repoConfig = $this->repos[$repository];
        /** @var $q \Doctrine\ORM\QueryBuilder */
        $q = $this->doctrine
            ->getRepository($repoConfig['repository'])
            ->createQueryBuilder('i')
            ->setMaxResults(10);
        $eb = $q->expr();
        $expr = $eb->orX();
        foreach ($repoConfig['fields'] as $fieldName) {
            $expr->add($eb->like($eb->lower('i.' . $fieldName), $eb->lower(':pattern')));
        }
        $q->where($expr);

        $params = array('pattern' => '%' . $pattern . '%');

        if (null !== $language) {
            $q->andWhere('i.language = :lang');
            $params[':lang'] = $language;
        }


        $queryResults = $q->getQuery()->execute($params);
        return $queryResults;
    }

    /**
     * @param $record
     * @param $admin
     * @return array
     */
    public function createResultRecord($record, $admin)
    {
        $resultRecord = array(
            'label' => (string)$record,
            'value' => (string)$record,
            'url' => ($admin ? $admin->generateObjectUrl('edit', $record) : null),
            'id' => ($admin ? $admin->id($record) : null)
        );
        return $resultRecord;
    }
}