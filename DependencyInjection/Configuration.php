<?php

namespace Puzzle\ConnectBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('puzzle_connect');
        
        $rootNode
            ->children()
                ->scalarNode('client_id')->end()
                ->scalarNode('client_secret')->end()
                ->scalarNode('base_authorize_uri')->end()
                ->scalarNode('base_token_uri')->end()
                ->scalarNode('base_apis_uri')->end()
                ->scalarNode('apis_version')->defaultValue('v1')->end()
                ->scalarNode('default_redirect_uri')->end()
                ->scalarNode('default_scope')->end()
            ->end()
        ;
        return $treeBuilder;
    }
}
