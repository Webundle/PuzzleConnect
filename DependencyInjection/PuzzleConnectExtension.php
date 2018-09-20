<?php

namespace Puzzle\ConnectBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class PuzzleConnectExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        
        $container->setParameter('puzzle_connect', $config);
        $container->setParameter('puzzle_connect.client_id', $config['client_id']);
        $container->setParameter('puzzle_connect.client_secret', $config['client_secret']);
        $container->setParameter('puzzle_connect.base_authorize_uri', $config['base_authorize_uri']);
        $container->setParameter('puzzle_connect.base_token_uri', $config['base_token_uri']);
        $container->setParameter('puzzle_connect.base_apis_uri', $config['base_apis_uri']);
        $container->setParameter('puzzle_connect.apis_version', $config['apis_version']);
        $container->setParameter('puzzle_connect.default_redirect_uri', $config['default_redirect_uri']);
        $container->setParameter('puzzle_connect.default_scope', $config['default_scope']);
    }
}
