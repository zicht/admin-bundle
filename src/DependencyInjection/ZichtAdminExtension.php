<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension as DIExtension;
use Zicht\Bundle\AdminBundle\AdminMenu\EventPropagationBuilder;

/**
 * Provides the admin services
 */
class ZichtAdminExtension extends DIExtension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        if (isset($config['quicklist'])) {
            $loader->load('quicklist.xml');
            foreach ($config['quicklist'] as $name => $quicklistConfig) {
                $container->getDefinition('zicht_admin.quicklist')
                    ->addMethodCall('addRepositoryConfig', [$name, $quicklistConfig]);

                $formResources = $container->getParameter('twig.form.resources');
                $formResources[] = '@ZichtAdmin/form_theme.html.twig';
                $container->setParameter('twig.form.resources', $formResources);
            }
        }

        if (isset($config['transactional_listener']) && $config['transactional_listener']['auto']) {
            $loader->load('transactional_listener.xml');

            $container->getDefinition('zicht_admin.transactional_listener')
                ->addArgument($config['transactional_listener']['pattern']);
        }

        if (isset($config['rc'])) {
            $loader->load('rc.xml');
            $container->getDefinition('zicht_admin.controller.rc')->replaceArgument(1, $config['rc']);
        }

        $container->getDefinition('zicht_admin.security.authorization.voter.admin_voter')
            ->replaceArgument(0, $config['security']['mapped_attributes']);

        $definition = $container->getDefinition(EventPropagationBuilder::class);
        $definition->replaceArgument(1, $config['menu']['hosts']);
    }
}
