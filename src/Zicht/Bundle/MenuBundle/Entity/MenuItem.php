<?php
/**
 * @author Jeroen Fiege <jeroenf@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\MenuBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Zicht\Bundle\MenuBundle\Entity\MenuItem
 *
 * @ORM\Entity
 * @ORM\Table(name="menu_item", indexes={@ORM\Index(columns={"name", "path"})})
 * @Gedmo\Tree(type="nested")
 * @ORM\Entity(repositoryClass="Gedmo\Tree\Entity\Repository\NestedTreeRepository")
 */
class MenuItem
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     */
    private $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    private $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     */
    private $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(name="root", type="integer", nullable=true)
     */
    private $root;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="MenuItem", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="MenuItem", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    private $children;

    /**
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string $path
     *
     * @ORM\Column(name="path", type="string", length=255, nullable=true)
     */
    private $path = null;


    /**
     * Optional menu item name, used to hook dynamic items into the menu.
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name = null;


    /**
     * Constructor
     */
    public function __construct($title = null, $path = null, $name = '')
    {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();

        $this->setTitle($title);
        $this->setPath($path);
        $this->setName($name);
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Page
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    public function getLeveledTitle()
    {
        return str_repeat('-', $this->lvl) . $this->title;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return MenuItem
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }


    /**
     * Set parent
     *
     * @param Zicht\Bundle\MenuBundle\Entity\MenuItem $parent
     * @return MenuItem
     */
    public function setParent(\Zicht\Bundle\MenuBundle\Entity\MenuItem $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return Zicht\Bundle\MenuBundle\Entity\MenuItem
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add children
     *
     * @param Zicht\Bundle\MenuBundle\Entity\MenuItem $children
     * @return MenuItem
     */
    public function addChildren(\Zicht\Bundle\MenuBundle\Entity\MenuItem $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param Zicht\Bundle\MenuBundle\Entity\MenuItem $children
     */
    public function removeChildren(\Zicht\Bundle\MenuBundle\Entity\MenuItem $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Get children
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    public function __toString()
    {
        return (string)$this->title;
    }

    /**
     * Set lft
     *
     * @param integer $lft
     * @return MenuItem
     */
    public function setLft($lft)
    {
        $this->lft = $lft;

        return $this;
    }

    /**
     * Get lft
     *
     * @return integer
     */
    public function getLft()
    {
        return $this->lft;
    }

    /**
     * Set lvl
     *
     * @param integer $lvl
     * @return MenuItem
     */
    public function setLvl($lvl)
    {
        $this->lvl = $lvl;

        return $this;
    }

    /**
     * Get lvl
     *
     * @return integer
     */
    public function getLvl()
    {
        return $this->lvl;
    }
    public function getLevel()
    {
        // TODO renamed "lvl" to level in the db, so tree_title.html.twig in ZichtFrameworkExtraBundle won't die on us.
        return $this->lvl;
    }

    /**
     * Set rgt
     *
     * @param integer $rgt
     * @return MenuItem
     */
    public function setRgt($rgt)
    {
        $this->rgt = $rgt;

        return $this;
    }

    /**
     * Get rgt
     *
     * @return integer
     */
    public function getRgt()
    {
        return $this->rgt;
    }

    /**
     * Set root
     *
     * @param integer $root
     * @return MenuItem
     */
    public function setRoot($root)
    {
        $this->root = $root;

        return $this;
    }

    /**
     * Get root
     *
     * @return integer
     */
    public function getRoot()
    {
        return $this->root;
    }


    public function isRoot()
    {
        return $this->root == $this->id;
    }


    public function setName($name)
    {
        $this->name = $name;
    }


    public function getName()
    {
        return $this->name;
    }


    protected $addToMenu = false;

    public function setAddToMenu($addToMenu)
    {
        $this->addToMenu = $addToMenu;
    }

    public function isAddToMenu()
    {
        return $this->addToMenu;
    }
}