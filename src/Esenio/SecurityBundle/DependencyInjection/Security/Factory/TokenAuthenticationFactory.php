<?php

namespace Esenio\SecurityBundle\DependencyInjection\Security\Factory;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;


class TokenAuthenticationFactory implements SecurityFactoryInterface
{
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = 'security.authentication.provider.token_auth.'.$id;
        $container
            ->setDefinition($providerId, new DefinitionDecorator('esenio_security.token_authentication.authentication_provider'))
        ;

        $listenerId = 'security.authentication.listener.token_auth.'.$id;
        $container
            ->setDefinition($listenerId, new DefinitionDecorator('esenio_security.http.firewall.token_authentication_listener'))
        ;

        return array($providerId, $listenerId, $defaultEntryPoint);
    }

    public function getPosition()
    {
        return 'pre_auth';
    }

    public function getKey()
    {
        return 'token_auth';
    }

    public function addConfiguration(NodeDefinition $builder)
    {
        $builder
            ->children()
                ->scalarNode('realm')->defaultValue('Secured Area')->end()
                ->arrayNode('providers')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('authenticator')->defaultValue('webmaster@example.com')->cannotBeEmpty()->end()
                        ->scalarNode('user')->defaultValue('webmaster')->cannotBeEmpty()->end()
                    ->end()
                ->end()
            ->end()
        ;

    }
}