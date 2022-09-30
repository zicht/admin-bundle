<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class ObjectDuplicateEvent extends Event
{
    /** @var object */
    private $oldObject;

    /** @var object */
    private $newObject;

    /**
     * @param object $oldObject
     * @param object $newObject
     */
    public function __construct($oldObject, $newObject)
    {
        $this->oldObject = $oldObject;
        $this->newObject = $newObject;
    }

    /**
     * @return object
     */
    public function getOldObject()
    {
        return $this->oldObject;
    }

    /**
     * @return object
     */
    public function getNewObject()
    {
        return $this->newObject;
    }
}
