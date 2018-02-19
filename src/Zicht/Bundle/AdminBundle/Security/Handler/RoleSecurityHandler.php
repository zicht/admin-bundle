<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\Security\Handler;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Security\Handler\RoleSecurityHandler as BaseRoleSecurityHandler;

/**
 * This class wraps the RoleSecurityHandler to allow the adminclass to handle specific attributes for the admin
 * so that it is compatible with voting for entities.
 */
class RoleSecurityHandler extends BaseRoleSecurityHandler
{
    /**
     * @{inheritDoc}
     */
    public function isGranted(AdminInterface $admin, $attributes, $object = null)
    {
        // sonata-admin 2
        if (isset($this->securityContext)) {
            if ($this->securityContext->isGranted($attributes, $object)) {
                return true;
            }
        }
        // sonata admin 3
        if (isset($this->authorizationChecker)) {
            if ($this->authorizationChecker->isGranted($attributes, $object)) {
                return true;
            }
        }
        return parent::isGranted($admin, $attributes, $object);
    }
}
