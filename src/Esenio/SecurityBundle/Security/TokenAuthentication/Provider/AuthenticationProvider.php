<?php

namespace Esenio\SecurityBundle\Security\TokenAuthentication\Provider;

use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface as BaseTokenInterface;

use Esenio\SecurityBundle\Security\TokenAuthentication\Token\TokenAuthenticatorInterface;


class AuthenticationProvider implements AuthenticationProviderInterface
{
    /**
     * @var TokenAuthenticatorInterface
     */
    protected  $authenticator;

    /**
     * @param TokenAuthenticatorInterface $authenticator
     */
    public function __construct(TokenAuthenticatorInterface $authenticator)
    {
        $this->authenticator = $authenticator;
    }

    /**
     * Attempts to authenticate a TokenInterface object.
     *
     * @param BaseTokenInterface $token The TokenInterface instance to authenticate
     * @return BaseTokenInterface An authenticated TokenInterface instance, never null
     * @throws AuthenticationException if the authentication fails
     */
    public function authenticate(BaseTokenInterface $token)
    {
        if (!$this->supports($token)) {
            return null;
        }

        // return authenticated token, or throw exception on failure
        return $this->authenticator->authenticateToken($token);
    }


    /**
     * Checks whether this provider supports the given token.
     *
     * @param BaseTokenInterface $token A TokenInterface instance
     * @return bool    true if the implementation supports the Token, false otherwise
     */
    public function supports(BaseTokenInterface $token)
    {
        return $this->authenticator->supportsToken($token); // let authenticator decide if token is supported or not
    }
}