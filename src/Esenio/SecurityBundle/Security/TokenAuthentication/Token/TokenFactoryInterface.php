<?php

namespace Esenio\SecurityBundle\Security\TokenAuthentication\Token;

use Esenio\SecurityBundle\Model\UserInterface;

/**
 * Defines factory for creation of TokenInterface objects.
 *
 * @package Esenio\SecurityBundle\Security\TokenAuthentication\Token
 */
interface TokenFactoryInterface
{
    /**
     * Creates TokenInterface object. If UserInterface parameter is not supplied, anonymous token is created.
     *
     * @param string|UserInterface $user User associated with token
     * @param $credentials String representation of token (encoded)
     * @param array $roles Roles assigned to token, token gets authenticated if roles are not empty
     *
     * @return TokenInterface
     */
    public function createToken($user = null, $credentials = '', array $roles = array());
}