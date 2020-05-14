<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Event that adds an item to the admin menu
 */
class MenuEvent extends Event
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $title;

    /**
     * @var array
     */
    private $options;

    /**
     * @param string $url
     * @param string $title
     * @param array $options
     * s
     */
    public function __construct($url, $title, $options = [])
    {
        $this->url = $url;
        $this->title = $title;
        $this->options = $options;
    }


    /**
     * Returns the title of the menu item
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Returns the url of the menu item
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }


    /**
     * Returns the config to use when building the MenuItem
     *
     * @return array
     */
    public function getMenuConfig()
    {
        return
            ['name' => $this->getTitle(), 'uri' => $this->getUrl()]
            + $this->options;
    }
}
