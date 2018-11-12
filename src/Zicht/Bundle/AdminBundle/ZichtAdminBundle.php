<?php
/**
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Bundle\AdminBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;

/**
 * Bundle for the admin menu
 */
class ZichtAdminBundle extends Bundle
{
    /**
     * Build
     *
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(
            new DependencyInjection\Compiler\EventPropagationPass(),
            PassConfig::TYPE_BEFORE_OPTIMIZATION
        );
    }
}
