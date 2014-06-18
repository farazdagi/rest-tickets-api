<?php

namespace Esenio\SecurityBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;

class SetupTokenEncoderPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @throws ParameterNotFoundException
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        $requiredParams = array(
            'esenio_security.secret',
            'esenio_security.jwt.algo'
        );

        foreach ($requiredParams as $requiredParam) {
            if (!$container->hasParameter($requiredParam)) {
                throw new ParameterNotFoundException(sprintf('Required parameter "%s" not found..', $requiredParam));
            }
        }

        // update encoder definition
        $definition = $container->findDefinition('esenio_security.token_authentication.token_encoder');
        $definition->replaceArgument(0, $container->getParameter('esenio_security.secret'));
        $definition->replaceArgument(1, $container->getParameter('esenio_security.jwt.algo'));
    }
}