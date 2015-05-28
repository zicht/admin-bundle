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
     * @return array
     */
    public function getResults($repository, $pattern, $max = 15)
    {
        $repoConfig = $this->repos[$repository];
        /** @var $q \Doctrine\ORM\QueryBuilder */
        $q = $this->doctrine
            ->getRepository($repoConfig['repository'])
            ->createQueryBuilder('i')

            // this is high because of sorting not done in SQL.
            ->setMaxResults(1500)
        ;
        $eb = $q->expr();
        $expr = $eb->orX();
        foreach ($repoConfig['fields'] as $fieldName) {
            $expr->add($eb->like($eb->lower('i.' . $fieldName), $eb->lower(':pattern')));
        }
        $q->where($expr);

        $results = array();
        foreach ($q->getQuery()->execute(array('pattern' => '%' . $pattern . '%')) as $record) {
            $admin = $this->adminPool->getAdminByClass(get_class($record));
            if (!$admin) {
                $admin = $this->adminPool->getAdminByClass(get_parent_class($record));
            }
            $results[]= array(
                'label' => (string)$record,
                'value' => (string)$record,
                'url' => ($admin ? $admin->generateObjectUrl('edit', $record) : null),
                'id' => ($admin ? $admin->id($record) : null)
            );
        }

        // TODO do this sort in DQL. Unfortunately, doctrine is not too handy with this, so
        // I'll keep it like this for a second. Note the the "setMaxResults()" should be reverted to $max
        // and the slice can be removed
        usort($results, function($a, $b) use ($pattern) {
            $percentA = 0;
            $percentB = 0;
            similar_text($a['label'], $pattern, $percentA);
            similar_text($b['label'], $pattern, $percentB);

            return $percentB - $percentA;
        });

        return array_slice($results, 0, $max);
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
}