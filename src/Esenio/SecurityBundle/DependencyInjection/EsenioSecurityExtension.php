<?php

namespace Esenio\SecurityBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class EsenioSecurityExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('esenio_security.realm', $config['realm']);
        $container->setParameter('esenio_security.secret', $config['secret']);

        if (isset($config['jwt']) && is_array($config['jwt'])) {
            $container->setParameter('esenio_security.jwt', $config['jwt']);
            foreach ($config['jwt'] as $k => $v) {
                $container->setParameter('esenio_security.jwt.' . $k, $v);
            }

        }
    }
}
