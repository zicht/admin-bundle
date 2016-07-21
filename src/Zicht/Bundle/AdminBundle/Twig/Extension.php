<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Bundle\AdminBundle\Twig;

use InvalidArgumentException;
use Sonata\AdminBundle\Admin\Pool;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Twig_SimpleFunction;


/**
 * Extensions for twig
 */
class Extension extends \Twig_Extension
{
    /**
     * Constructor.
     *
     * @param \Sonata\AdminBundle\Admin\Pool $sonata
     * @param \Doctrine\Bundle\DoctrineBundle\Registry $doctrine
     */
    public function __construct(Pool $sonata, Registry $doctrine)
    {
        $this->sonata = $sonata;
        $this->doctrine = $doctrine;
    }


    /**
     * Registers the 'admin_url' function
     *
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'admin_url' => new Twig_SimpleFunction('admin_url', [$this, 'adminUrl'])
        );
    }


    /**
     * Render an url to a sonata admin
     *
     * @param mixed $subject
     * @param string $action
     * @param array $parameters
     * @return string
     * @throws InvalidArgumentException
     */
    public function adminUrl($subject, $action, $parameters = array())
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
            throw new InvalidArgumentException("Unsupported subject, need either an object or a string");
        }

        /** @var $admin \Sonata\AdminBundle\Admin\Admin */
        $admin = $this->sonata->getAdminByClass($className);
        if (!$admin) {
            // assume the string is an admincode.
            $admin = $this->sonata->getAdminByAdminCode($className);
        }

        if (!$admin) {
            throw new InvalidArgumentException("No admin found for {$className}");
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