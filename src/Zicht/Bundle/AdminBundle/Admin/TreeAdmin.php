<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Zicht\Bundle\FrameworkExtraBundle\Form\ParentChoiceType;

/**
 * Provides a base class for easily providing admins for tree structures.
 */
class TreeAdmin extends AbstractAdmin
{
    /**
     * Override the default query builder to utilize correct sorting
     *
     * @param string $context
     * @return ProxyQueryInterface
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
                $queryBuilder->orderBy('n.root, n.lft');
            } else {
                $queryBuilder->orderBy('n.lft');
            }

            return new ProxyQuery($queryBuilder);
        }
        return parent::createQuery($context);
    }


    /**
     * {@inheritDoc}
     */
    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
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
     * @param ListMapper $listMapper
     * @return ListMapper
     */
    public function configureListFields(ListMapper $listMapper)
    {
        return $listMapper
            ->addIdentifier('title', null, ['template' => 'ZichtAdminBundle:CRUD:tree_title.html.twig'])
            ->add(
                '_action',
                'actions',
                [
                    'actions' => [
                        'filter' => [
                            'template' => 'ZichtAdminBundle:CRUD:actions/filter.html.twig',
                        ],
                        'move' => [
                            'template' => 'ZichtAdminBundle:CRUD:actions/move.html.twig',
                        ],
                        'edit' => [],
                        'delete' => [],
                    ],
                ]
            );
    }

    /**
     * Configure route
     *
     * @param RouteCollection $collection
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
