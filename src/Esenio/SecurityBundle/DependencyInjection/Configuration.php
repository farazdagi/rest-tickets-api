<?php

namespace Esenio\SecurityBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('esenio_security');

        $rootNode
            ->children()
                ->scalarNode('secret')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('realm')->defaultValue('Secured Area')->end()
            ->arrayNode('jwt')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('return_url')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('iss')->end()
                    ->scalarNode('sub')->end()
                    ->scalarNode('aud')->end()
                    ->scalarNode('token_lifetime')->defaultValue(3600)->end()
                    ->scalarNode('token_type')->defaultValue('Bearer')->end()
                    ->scalarNode('algo')->defaultValue('HS256')->end()
                ->end()
            ->end()
            ->end();

        return $treeBuilder;
    }
}
