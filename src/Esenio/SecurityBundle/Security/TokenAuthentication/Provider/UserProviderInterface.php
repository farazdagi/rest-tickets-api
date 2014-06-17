<?php

namespace Esenio\SecurityBundle\Security\TokenAuthentication\Provider;

use Symfony\Component\Security\Core\User\UserProviderInterface as BaseInterface;

use Esenio\SecurityBundle\Security\TokenAuthentication\Token\TokenInterface;


/**
 * In addition to common UserProviderInterface methods, it allows to get
 * username from the TokenInterface object.
 *
 * @package Esenio\SecurityBundle\Security\TokenAuthentication\Provider
 */
interface UserProviderInterface extends BaseInterface
{
    /**
     * Lookup username based on the Token passed.
     *
     * @param TokenInterface $token
     * @return string
     */
    public function getUsernameForToken(TokenInterface $token);
}