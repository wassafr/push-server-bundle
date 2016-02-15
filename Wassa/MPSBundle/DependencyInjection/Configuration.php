<?php

namespace Wassa\MPSBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('wassa_mps');

        $rootNode
            ->children()
                ->booleanNode('delete_failed')
                    ->info('Delete tokens with errors')
                    ->defaultFalse()
                ->end()
                ->arrayNode('gcm')
                    ->children()
                        ->scalarNode('api_key')
                            ->info('GCM API Key')
                        ->end()
                        ->scalarNode('dry_run')
                            ->info('Enable dry run mode')
                            ->isRequired()
                            ->defaultValue('true')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('apns')
                    ->children()
                        ->scalarNode('environment')
                            ->info('APNS Environment (production or sandbox)')
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->defaultValue('sandbox')
                            ->validate()
                            ->ifNotInArray(array('sandbox', 'production'))
                                ->thenInvalid('Invalid APNS environment "%s"')
                            ->end()
                        ->end()
                        ->scalarNode('prod_cert')
                            ->info('APNS Production certificate file')
                        ->end()
                        ->scalarNode('sand_cert')
                            ->info('APNS Sandbox certificate file')
                        ->end()
                        ->scalarNode('ca_cert')
                            ->info('APNS Root CA certificate file')
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('entity_manager')->defaultNull()->end()
            ->end();

        return $treeBuilder;
    }
}
