<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\DependencyInjection;

use \Symfony\Component\HttpKernel\DependencyInjection\Extension as DIExtension;
use \Symfony\Component\DependencyInjection\ContainerBuilder;
use \Symfony\Component\Config\FileLocator;
use \Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * Provides the admin services
 */
class ZichtAdminExtension extends DIExtension
{
    /**
     * @{inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        if (isset($config['quicklist'])) {
            $loader->load('quicklist.xml');
            foreach ($config['quicklist'] as $name => $quicklistConfig) {
                $container->getDefinition('zicht_admin.quicklist')
                    ->addMethodCall('addRepositoryConfig', array($name, $quicklistConfig));

                $formResources = $container->getParameter('twig.form.resources');
                $formResources[]= 'ZichtAdminBundle::form_theme.html.twig';
                $container->setParameter('twig.form.resources', $formResources);
            }
        }

        if (isset($config['transactional_listener']) && $config['transactional_listener']['auto']) {
            $loader->load('transactional_listener.xml');

            $container->getDefinition('zicht_admin.transactional_listener')
                ->addArgument($config['transactional_listener']['pattern'])
            ;
        }
    }
}