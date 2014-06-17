<?php

namespace Esenio\SecurityBundle\Security\TokenAuthentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface as BaseTokenInterface;

/**
 * Marker Interface that defines token-based auth tokens (read as JWTs).
 *
 * Note: DO NOT define extra logic in this interface. Everything we need is provided by BaseTokenInterface.
 * For custom data attributes, rely on base BaseTokenInterface's setAttribute()/getAttribute() methods.
 *
 * @package Esenio\SecurityBundle\Security\TokenAuthentication\Token
 */
interface TokenInterface  extends BaseTokenInterface
{
    /**
     * Common errors
     */
    const ERROR_INVALID_TOKEN = 'invalid_token';
    const ERROR_INVALID_REQUEST = 'invalid_request';
    const ERROR_INSUFFICIENT_SCOPE = 'insufficient_scope';

    /**
     * Anon user is used for empty non-signed tokens.
     */
    const USER_ANONYMOUS = '~token.anon.';
}