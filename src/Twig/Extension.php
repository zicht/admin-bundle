<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\Twig;

use InvalidArgumentException;
use Sonata\AdminBundle\Admin\Pool;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Extensions for twig
 */
class Extension extends AbstractExtension
{
    /**
     * @var Pool
     */
    private $sonata;

    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * @param Pool $sonata
     * @param Registry $doctrine
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
        return [
            'admin_url' => new TwigFunction('admin_url', [$this, 'adminUrl']),
        ];
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
    public function adminUrl($subject, $action, $parameters = [])
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
            throw new InvalidArgumentException('Unsupported subject, need either an object or a string');
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
