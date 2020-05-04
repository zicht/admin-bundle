<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\Sonata\Datagrid;

use Doctrine\Common\Collections\Criteria;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;

/**
 * Allow custom sorting to go before the sorting chosen by the user in the admin datagrid (list view)
 * @see \Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery
 */
class CustomSortProxyQuery extends ProxyQuery
{
    /** {@inheritDoc} */
    public function execute(array $params = [], $hydrationMode = null)
    {
        if (!$this->getSortBy() || 0 === count($this->queryBuilder->getDQLPart('orderBy'))) {
            return parent::execute($params, $hydrationMode);
        }

        // Save originals
        $queryBuilderOriginal = clone $this->queryBuilder;
        $sortByOriginal = $this->sortBy;
        $sortOrderOriginal = $this->sortOrder;

        // Reset
        $orderByDQLPart = $this->queryBuilder->getDQLPart('orderBy');
        $this->queryBuilder->resetDQLPart('orderBy');

        // Trick the Sonata Datagrid ProxyQuery into respecting our sorting
        /** @var \Doctrine\ORM\Query\Expr\OrderBy $primarySort */
        $primarySort = array_shift($orderByDQLPart);
        if ($primarySort->count() > 0) {
            $sortSplit = explode(' ', $primarySort->getParts()[0], 2);
            $this->sortBy = $sortSplit[0];
            $this->sortOrder = $sortSplit[1] ?? Criteria::ASC;
        }

        foreach ($orderByDQLPart as $orderBy) {
            $this->queryBuilder->addOrderBy($orderBy);
        }
        $this->queryBuilder->addOrderBy($sortByOriginal, $sortOrderOriginal);

        // Execute
        $results = parent::execute($params, $hydrationMode);

        // Restore originals
        $this->queryBuilder = $queryBuilderOriginal;
        $this->sortBy = $sortByOriginal;
        $this->sortOrder = $sortOrderOriginal;

        return $results;
    }

}
