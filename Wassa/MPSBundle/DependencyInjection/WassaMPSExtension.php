<?php

namespace Wassa\MPSBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Yaml\Yaml;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class WassaMPSExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $container->setParameter("wassa_mps.gcm.api_key", $config['gcm']["api_key"]);
        $container->setParameter("wassa_mps.gcm.dry_run", $config['gcm']["dry_run"]);
        $container->setParameter("wassa_mps.apns.environment", $config['apns']["environment"]);
        $container->setParameter("wassa_mps.apns.prod_cert", $config['apns']["prod_cert"]);
        $container->setParameter("wassa_mps.apns.sand_cert", $config['apns']["sand_cert"]);
        $container->setParameter("wassa_mps.apns.ca_cert", $config['apns']["ca_cert"]);
        $container->setParameter("wassa_mps.entity_manager", $config['entity_manager']);
        $container->setParameter("wassa_mps.delete_failed", $config['delete_failed']);
    }

    public function prepend(ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');
        $configValues = Yaml::parse(file_get_contents(dirname(__FILE__).'/../Resources/config/config.yml'));

        if (isset($bundles['NelmioApiDocBundle'])) {
            $container->prependExtensionConfig(
                'nelmio_api_doc',
                $configValues['nelmio_api_doc']
            );
        }
    }
}
