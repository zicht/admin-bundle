<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Bundle\AdminBundle\Twig;

use Sonata\AdminBundle\Admin\Pool;
use Doctrine\Bundle\DoctrineBundle\Registry;

class Extension extends \Twig_Extension
{
    function __construct(Pool $sonata, Registry $doctrine)
    {
        $this->sonata = $sonata;
        $this->doctrine = $doctrine;
    }


    public function getFunctions()
    {
        return array(
            'admin_url' => new \Twig_Function_Method($this, 'admin_url')
        );
    }


    public function admin_url($subject, $action, $parameters = array())
    {
        if (is_object($subject)) {
            $className = get_class($subject);
        } elseif (is_string($subject)) {
            $className = $subject;
            if (strpos($className, ':') !== false) {
                list($namespace, $entity) = explode(':', $className);
                $className = $this->doctrine->getAliasNamespace($namespace) . '\\' . $entity;
            }
        } else {
            throw new \InvalidArgumentException("Unsupported subject, need either an object or a string");
        }

        /** @var $admin \Sonata\AdminBundle\Admin\Admin */
        $admin = $this->sonata->getAdminByClass($className);
        if (!$admin){
            // assume the string is an admincode.
            $admin = $this->sonata->getAdminByAdminCode($className);
        }

        if (!$admin) {
            throw new \InvalidArgumentException("No admin found for {$className}");
        }
        if (is_object($subject)) {
            return $admin->generateObjectUrl($action, $subject, $parameters);
        } else {
            return $admin->generateUrl($action, $parameters);
        }
    }


    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'zicht_admin';
    }
}