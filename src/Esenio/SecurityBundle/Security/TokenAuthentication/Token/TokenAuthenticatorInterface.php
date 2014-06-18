<?php

namespace Esenio\SecurityBundle\Security\TokenAuthentication\Token;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface as BaseTokenInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;


/**
 * Interface is used to allow end-clients to specify token based authentication particularities such as how
 * token gets created, what response is sent on error etc..
 *
 * @package Esenio\SecurityBundle\Security\TokenAuthentication\Token
 */
interface TokenAuthenticatorInterface
{
    /**
     * Initializes token using incoming Request values (by analyzing query string, headers etc).
     * Resultant token must have all information necessary for token authentication.
     * If any information is not found in Request, throwing BadCredentialsException will cause
     * authentication to fail.
     *
     * @param Request $request
     * @throws BadCredentialsException
     * @return TokenInterface
     */
    public function createToken(Request $request);

    /**
     * Authenticates the anonymous token, returns authenticated token with UserInterface object injected.
     *
     * @param TokenInterface $token
     * @return TokenInterface
     */
    public function authenticateToken(TokenInterface $token);

    /**
     * Checks whether supplied token is supported
     *
     * This is just a way to allow several authentication mechanisms to be used for the same firewall
     * (that way, you can for instance first try to authenticate the user via a certificate or an API
     * key and fall back to a form login).
     *
     * @param BaseTokenInterface $token
     * @return boolean
     */
    public function supportsToken(BaseTokenInterface $token);
}