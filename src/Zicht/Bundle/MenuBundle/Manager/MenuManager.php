<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\MenuBundle\Manager;

class MenuManager
{
    function __construct(\Doctrine\Bundle\DoctrineBundle\Registry $doctrine)
    {
        $this->doctrine = $doctrine;
        $this->items = array();
        $this->remove = array();
    }


    function addItem(\Zicht\Bundle\MenuBundle\Entity\MenuItem $item)
    {
        $this->items[]= $item;
    }


    function removeItem(\Zicht\Bundle\MenuBundle\Entity\MenuItem $item)
    {
        $this->remove[]= $item;
    }


    function flush()
    {
        foreach ($this->items as $item) {
            $this->doctrine->getManager()->persist($item);
        }
        foreach ($this->remove as $item) {
            $this->doctrine->getManager()->remove($item);
        }
    }


    /**
     * Find an item by a path
     *
     * @param string $path
     * @return \Zicht\Bundle\MenuBundle\Entity\MenuItem
     */
    public function getItem($path)
    {
        return $this->doctrine->getManager()->getRepository('ZichtMenuBundle:MenuItem')->findOneByPath($path);
    }
}