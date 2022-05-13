<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\Admin;

use Doctrine\Common\Collections\Criteria;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Zicht\Bundle\AdminBundle\Sonata\Datagrid\CustomSortProxyQuery;
use Zicht\Bundle\FrameworkExtraBundle\Form\ParentChoiceType;

/**
 * Provides a base class for easily providing admins for tree structures.
 *
 * @template T of object
 * @extends AbstractAdmin<T>
 */
class TreeAdmin extends AbstractAdmin
{
    /**
     * {@inheritDoc}
     */
    public function createQuery($context = 'list')
    {
        if ($context === 'list') {
            /** @var $em \Doctrine\ORM\EntityManager */
            $em = $this->getModelManager()->getEntityManager($this->getClass());

            /** @var $cmd \Doctrine\Common\Persistence\Mapping\ClassMetadata */
            $cmd = $em->getMetadataFactory()->getMetadataFor($this->getClass());

            $queryBuilder = $em->createQueryBuilder();
            $queryBuilder
                ->select('n')
                ->from($this->getClass(), 'n');

            if ($cmd->hasField('root')) {
                $queryBuilder->orderBy('n.root', Criteria::ASC);
                $queryBuilder->addOrderBy('n.lft', Criteria::ASC);
            } else {
                $queryBuilder->orderBy('n.lft', Criteria::ASC);
            }

            return new CustomSortProxyQuery($queryBuilder);
        }
        return parent::createQuery($context);
    }


    /**
     * {@inheritDoc}
     */
    public function configureFormFields(FormMapper $form)
    {
        $form
            ->tab('General')
            ->with('General')
            ->add('parent', ParentChoiceType::class, ['required' => false, 'class' => $this->getClass()])
            ->add('title', null, ['required' => true])
            ->end()
            ->end();
    }


    /**
     * {@inheritDoc}
     */
    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add(
                'root',
                'doctrine_orm_callback',
                [
                    'label' => 'Sectie',
                    'callback' => function ($qb, $alias, $f, $v) {
                        if ($v['value']) {
                            $qb->andWhere($alias . '.root=:root')
                                ->setParameter(':root', $v['value']);
                        }
                    },
                    'field_type' => EntityType::class,
                    'field_options' => [
                        'query_builder' => function ($repo) {
                            return $repo->createQueryBuilder('t')->andWhere('t.parent IS NULL');
                        },
                        'class' => $this->getClass()
                    ],
                ]
            )
            ->add(
                'id',
                'doctrine_orm_callback',
                [
                    'callback' => [$this, 'filterWithChildren'],
                ]
            );
    }


    /**
     * {@inheritDoc}
     */
    public function configureListFields(ListMapper $list)
    {
        $list
            ->addIdentifier('title', null, ['template' => '@ZichtAdmin/CRUD/tree_title.html.twig'])
            ->add(
                '_action',
                'actions',
                [
                    'actions' => [
                        'filter' => [
                            'template' => '@ZichtAdmin/CRUD/actions/filter.html.twig',
                        ],
                        'move' => [
                            'template' => '@ZichtAdmin/CRUD/actions/move.html.twig',
                        ],
                        'edit' => [],
                        'delete' => [],
                    ],
                ]
            );
    }

    /**
     * {@inheritDoc}
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        parent::configureRoutes($collection);

        $collection->add('moveUp', $this->getRouterIdParameter() . '/move-up');
        $collection->add('moveDown', $this->getRouterIdParameter() . '/move-down');
    }


    /**
     * Get item plus children
     *
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param string $alias
     * @param string $field
     * @param array $value
     *
     * @return bool|null
     */
    public function filterWithChildren($qb, $alias, $field, $value)
    {
        // Check whether it is a numeric value because we could get a string number.
        if (!($value['value'] && is_numeric($value['value']))) {
            return null;
        }

        // Get the parent item
        $parentQb = clone $qb;
        $parentQb->where($parentQb->expr()->eq(sprintf('%s.id', $alias), ':id'));
        $parentQb->setParameter('id', (int)$value['value']);
        $currentItem = $parentQb->getQuery()->getOneOrNullResult();

        if ($currentItem === null) {
            return null;
        }

        $qb->where(
            $qb->expr()->andX(
                $qb->expr()->eq(sprintf('%s.root', $alias), ':root'),
                $qb->expr()->orX(
                    $qb->expr()->andX(
                        $qb->expr()->lt(sprintf('%s.lft', $alias), ':left'),
                        $qb->expr()->gt(sprintf('%s.rgt', $alias), ':right')
                    ),
                    $qb->expr()->between(sprintf('%s.lft', $alias), ':left', ':right')
                )
            )
        );

        $qb->setParameter('root', $currentItem->getRoot());
        $qb->setParameter('left', $currentItem->getLft());
        $qb->setParameter('right', $currentItem->getRgt());

        return true;
    }
}
