<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\Command;

use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Helper command to generate a role-hierarchy yaml file that includes all sonata admin roles.
 *
 * Useful when using the role based security in Sonata
 */
class DumpRoleHierarchyCommand extends Command
{
    /** @var string */
    protected static $defaultName = 'zicht:admin:dump-role-hierarchy';

    /** @var Pool */
    private $pool;

    /** @var SecurityHandlerInterface */
    private $securityHandler;

    public function __construct(Pool $pool, SecurityHandlerInterface $securityHandler, string $name = null)
    {
        parent::__construct($name);
        $this->pool = $pool;
        $this->securityHandler = $securityHandler;
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Dumps a security.role_hierarchy configuration for the available admins')
            ->addArgument('attributes', InputArgument::IS_ARRAY, 'Additional attributes to append to each role', [])
            ->addOption(
                'root',
                '',
                InputOption::VALUE_REQUIRED,
                'Additionally generate a root admin role which implies all other admin roles'
            )
            ->setHelp(
                "This command generates a list usable by the sonata.admin.security.handler.role security strategy.\n\n"
                . 'By default, for all of the admins, a role with suffix _ADMIN is generated which implies the '
                . "sonata security attribute roles, so a sonata admin called 'foo.admin' will yield the following "
                . "structure:\n\n"
                . "    ROLE_FOO_ADMIN_ADMIN: [ROLE_FOO_ADMIN_EDIT, ROLE_FOO_ADMIN_CREATE, ...]\n\n"
                . 'When inheritance is available, the intuitive inheritance structure is also generated. Say above admin '
                . "manages a class which extends a class Qux with id 'qux.admin', the admin role for qux will imply "
                . "admin rights for foo:\n\n"
                . "    ROLE_QUX_ADMIN_ADMIN: [ROLE_FOO_ADMIN_ADMIN, ROLE_QUX_ADMIN_EDIT, ...]\n\n"
                . "In addition, for each of the attributes, the attributes on the child admins is implied as well:\n\n"
                . "    ROLE_QUX_ADMIN_DELETE: [ROLE_FOO_ADMIN_DELETE]\n\n"
                . 'Optionally provide attributes that will be suffixed to each role'
            );
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $attributes = array_merge(
            ['LIST', 'VIEW', 'CREATE', 'EDIT', 'DELETE', 'EXPORT'],
            $input->getArgument('attributes')
        );

        $pool = $this->pool;
        $handler = $this->securityHandler;

        $roleHierarchy = [];
        $adminClasses = $pool->getAdminClasses();

        foreach ($adminClasses as $class => $ids) {
            list($id) = $ids;

            $admin = $pool->getAdminByAdminCode($id);
            $pattern = $handler->getBaseRole($admin);
            $adminAttr = sprintf($pattern, 'ADMIN');
            $roleHierarchy[$adminAttr] = [];
            foreach ($attributes as $attr) {
                $roleHierarchy[$adminAttr][] = sprintf($pattern, $attr);
            }
        }

        $inheritedAttributes = $attributes;
        $inheritedAttributes[] = 'ADMIN';
        foreach ($adminClasses as $class => $ids) {
            foreach ($ids as $id) {
                $childAdmin = $pool->getAdminByAdminCode($id);
                while ($pool->hasAdminByClass(get_parent_class($class))) {
                    $parentClass = get_parent_class($class);
                    foreach ($adminClasses[$parentClass] as $adminClass) {
                        $parentAdmin = $pool->getInstance($adminClass);
                        $pattern = $handler->getBaseRole($parentAdmin);

                        // rights on the base class imply rights on the child classes:
                        foreach ($inheritedAttributes as $attr) {
                            $roleHierarchy[sprintf($pattern, $attr)][] = sprintf($handler->getBaseRole($childAdmin), $attr);
                        }
                    }

                    $class = $parentClass;
                }
            }
        }

        if ($baseRole = $input->getOption('root')) {
            $roleHierarchy[$baseRole] = array_filter(
                array_keys($roleHierarchy),
                function ($n) {
                    return strpos(strrev($n), strrev('_ADMIN')) === 0;
                }
            );
        }

        // optimize: all roles that are implicit, don't need to be explicit
        $keysToRemove = [];
        foreach ($roleHierarchy as $parentName => $childNames) {
            foreach ($childNames as $child1) {
                if (isset($roleHierarchy[$child1])) {
                    foreach ($childNames as $p2 => $child2) {
                        if (in_array($child2, $roleHierarchy[$child1])) {
                            $keysToRemove[$parentName][] = $p2;
                        }
                    }
                }
            }
        }
        foreach ($keysToRemove as $parent => $keys) {
            foreach ($keys as $k) {
                unset($roleHierarchy[$parent][$k]);
            }
            $roleHierarchy[$parent] = array_values($roleHierarchy[$parent]);
        }

        $dumpableConfig = [
            'security' => [
                'role_hierarchy' => $roleHierarchy,
            ],
        ];

        $output->writeln('# Generated by command ' . $this->getName() . ' (' . __CLASS__ . ')');
        $output->writeln('# ' . join(' ', $_SERVER['argv']));
        $output->writeln('');
        $output->writeln(Yaml::dump($dumpableConfig, 4));
    }
}
