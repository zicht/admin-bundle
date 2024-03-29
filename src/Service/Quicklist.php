<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\Service;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Exception\AdminClassNotFoundException;
use Sonata\AdminBundle\Exception\AdminCodeNotFoundException;

/**
 * Quick list service
 */
class Quicklist
{
    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * @var Pool
     */
    private $adminPool;

    /**
     * @var array
     */
    private $repos;

    public function __construct(Registry $doctrine, Pool $pool)
    {
        $this->doctrine = $doctrine;
        $this->adminPool = $pool;
        $this->repos = [];
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
            return array_filter(
                $this->repos,
                function ($item) {
                    return isset($item['exposed']) && $item['exposed'] === true;
                }
            );
        }
        return $this->repos;
    }

    /**
     * Gets the first admin when there are multiple definitions.
     *
     * @param string $class
     * @return \Sonata\AdminBundle\Admin\AdminInterface|null
     */
    private function getFirstAdminPerClass($class)
    {
        $code = null;
        $admins = $this->adminPool->getAdminClasses();

        foreach ($admins as $key => $value) {
            if ($key === $class) {
                $code = current($value);
                break;
            }
        }

        try {
            return $code === null ? $this->adminPool->getAdminByClass($class) : $this->adminPool->getAdminByAdminCode($code);
        } catch (AdminClassNotFoundException|AdminCodeNotFoundException) {
            return null;
        }
    }

    /**
     * Get the result suggestions for the passed pattern
     *
     * @param string $repository
     * @param string $pattern
     * @param string|null $language
     * @param int|null $max
     * @return array
     */
    public function getResults($repository, $pattern, $language = null, $max = null)
    {
        $queryResults = $this->findRecords($repository, $pattern, $language);

        $results = [];
        foreach ($queryResults as $record) {
            $admin = $this->getFirstAdminPerClass(get_class($record));
            if (!$admin) {
                $admin = $this->getFirstAdminPerClass(get_parent_class($record));
            }
            $resultRecord = $this->createResultRecord($record, $admin);
            $results[] = $resultRecord;
        }

        // TODO do this sort in DQL. Unfortunately, doctrine is not too handy with this, so
        // I'll keep it like this for a second. Note the the "setMaxResults()" should be reverted to $max
        // and the slice can be removed
        usort(
            $results,
            function ($a, $b) use ($pattern) {
                $percentA = 0;
                $percentB = 0;
                similar_text($a['label'], $pattern, $percentA);
                similar_text($b['label'], $pattern, $percentB);

                return $percentB - $percentA;
            }
        );

        $maxResults = $max !== null ? $max : $this->repos[$repository]['max_results'];

        return array_slice($results, 0, $maxResults);
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
            ->find($id);
    }

    /**
     * @param string $repository
     * @param string $pattern
     * @param string|null $language
     * @return mixed
     */
    private function findRecords($repository, $pattern, $language = null)
    {
        $repoConfig = $this->repos[$repository];
        /** @var $q \Doctrine\ORM\QueryBuilder */
        $q = $this->doctrine
            ->getRepository($repoConfig['repository'])
            ->createQueryBuilder('i')
            ->setMaxResults(1500);
        $eb = $q->expr();
        $expr = $eb->orX();
        foreach ($repoConfig['fields'] as $fieldName) {
            $expr->add($eb->like($eb->lower('i.' . $fieldName), $eb->lower(':pattern')));
        }
        $q->where($expr);

        $params = ['pattern' => '%' . $pattern . '%'];

        if (null !== $language) {
            $q->andWhere('i.language = :lang');
            $params[':lang'] = $language;
        }

        return $q->getQuery()->execute($params);
    }

    /**
     * @param object $record
     * @param object $admin
     * @return array
     */
    public function createResultRecord($record, $admin)
    {
        $resultRecord = [
            'label' => (string)$record,
            'value' => (string)$record,
            'url' => ($admin ? $admin->generateObjectUrl('edit', $record) : null),
            'id' => ($admin ? $admin->id($record) : null),
        ];
        return $resultRecord;
    }
}
