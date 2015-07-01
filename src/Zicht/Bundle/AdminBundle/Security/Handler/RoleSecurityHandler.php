<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\Security\Handler;

use \Sonata\AdminBundle\Admin\AdminInterface;
use \Sonata\AdminBundle\Security\Handler\RoleSecurityHandler as BaseRoleSecurityHandler;

/**
 * Class RoleSecurityHandler
 * @package Zicht\Bundle\UserBundle\Security\Admin
 */
class RoleSecurityHandler extends BaseRoleSecurityHandler
{
    public function isGranted(AdminInterface $admin, $attributes, $object = null)
    {
        if ($this->securityContext->isGranted($attributes, $object)) {
            return true;
        }
        return parent::isGranted($admin, $attributes, $object); 
    }
}
