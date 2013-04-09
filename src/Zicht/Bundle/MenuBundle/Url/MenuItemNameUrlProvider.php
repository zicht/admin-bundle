<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\MenuBundle\Url;

use Zicht\Bundle\UrlBundle\Url\StaticProvider;
use Zicht\Bundle\UrlBundle\Url\SuggestableProvider;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Routing\RouterInterface;

class MenuItemNameUrlProvider extends StaticProvider implements SuggestableProvider
{
    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $repository;


    function __construct(Registry $doctrine, RouterInterface $router)
    {
        parent::__construct($router);
        $this->repository = $doctrine->getManager()->getRepository('Zicht\Bundle\MenuBundle\Entity\MenuItem');
        $this->loaded = false;
        $this->mappings = array();
    }


    function supports($name)
    {
        if (!$this->loaded) {
            $this->loadMappings();
        }
        return parent::supports($name);
    }


    protected function loadMappings()
    {
        $queryBuilder = $this->repository
            ->createQueryBuilder('m')
            ->select('m.name, m.path')
            ->andWhere('m.path IS NOT NULL')
            ->andWhere('m.name IS NOT NULL')
        ;
        $mappings = array();
        foreach ($queryBuilder->getQuery()->execute() as $item) {
            $mappings[$item['name']] = $item['path'];
        }
        $this->addAll($mappings);
        $this->loaded = true;
    }

    /**
     * Suggest url's based on the passed pattern. The return value must be an array containing "label" and "value" keys.
     *
     * @param $pattern
     * @return mixed
     */
    public function suggest($pattern)
    {
        $menuItems = $this->repository->createQueryBuilder('m')
            ->andWhere('m.name LIKE :pattern')
            ->getQuery()
            ->execute(array('pattern' => '%' . $pattern . '%'))
        ;

        $suggestions = array();
        foreach ($menuItems as $item) {
            $suggestions[]= array(
                'value' => $item->getName(),
                'label' => sprintf('%s (menu item)', $item)
            );
        }

        return $suggestions;
    }
}