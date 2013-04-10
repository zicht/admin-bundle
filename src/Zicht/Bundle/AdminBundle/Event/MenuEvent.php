<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\AdminBundle\Event;

class MenuEvent extends \Symfony\Component\EventDispatcher\Event
{
    public function __construct($url, $title)
    {
        $this->url = $url;
        $this->title = $title;
    }


    public function getTitle()
    {
        return $this->title;
    }

    public function getUrl()
    {
        return $this->url;
    }


    public function getMenuConfig()
    {
        return array(
            'name' => $this->getTitle(),
            'uri' => $this->getUrl()
        );
    }
}