<?php

namespace Esenio\SecurityBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Esenio\SecurityBundle\DependencyInjection\Compiler\SetupTokenEncoderPass;
use Esenio\SecurityBundle\DependencyInjection\Security\Factory\TokenAuthenticationFactory;

class EsenioSecurityBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        /** @var SecurityExtension $extension */
        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new TokenAuthenticationFactory());

        // setup compiler passes
        $container->addCompilerPass(new SetupTokenEncoderPass());
    }
}
