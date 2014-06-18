<?php

namespace Esenio\SecurityBundle\Security\TokenAuthentication\Token;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface as BaseTokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;


/**
 * Authenticator handles token authentication process. It is expected to be used as a backend
 * for AuthenticationProviderInterface objects.
 *
 * @package Esenio\SecurityBundle\Security\TokenAuthentication\Token
 */
class TokenAuthenticator
    implements TokenAuthenticatorInterface, AuthenticationFailureHandlerInterface, AuthenticationSuccessHandlerInterface
{
    /**
     * @var TokenIssuerInterface
     */
    private $tokenIssuer;

    /**
     * @param TokenIssuerInterface $tokenIssuer
     */
    public function __construct(TokenIssuerInterface $tokenIssuer)
    {
        $this->tokenIssuer = $tokenIssuer;
    }

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
    public function createToken(Request $request)
    {
        try {
            $token = $this->tokenIssuer->issueToken($request);
        } catch (\Exception $e) {
            throw new BadCredentialsException($e->getMessage());
        }

        return $token;
    }

    /**
     * Authenticate provided anonymous token, returning authenticated token with UserInterface object injected.
     *
     * @param TokenInterface $token
     *
     * @throws AuthenticationException
     * @return TokenInterface
     */
    public function authenticateToken(TokenInterface $token)
    {
        try {
            $token = $this->tokenIssuer->signToken($token);
        } catch (\Exception $e) {
            throw new AuthenticationException($e->getMessage());
        }

        return $token;
    }

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
    public function supportsToken(BaseTokenInterface $token)
    {
        return $token instanceof TokenInterface;
    }

    /**
     * This is called when an interactive authentication attempt fails. This is
     * called by authentication listeners inheriting from
     * AbstractAuthenticationListener.
     *
     * @param Request $request
     * @param AuthenticationException $exception
     *
     * @return Response The response to return, never null
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $response = new Response();
        $response->setStatusCode(Response::HTTP_UNAUTHORIZED);
        $response->headers->add(array(
            'WWW-Authenticate' => sprintf(
                'Bearer error="%s" error_description="%s"',
                TokenInterface::ERROR_INVALID_TOKEN, "Token authentication failed")
        ));

        return $response;
    }

    /**
     * This is called when an interactive authentication attempt succeeds. This
     * is called by authentication listeners inheriting from
     * AbstractAuthenticationListener.
     *
     * @param Request $request
     * @param BaseTokenInterface $token
     *
     * @return Response never null
     */
    public function onAuthenticationSuccess(Request $request, BaseTokenInterface $token)
    {
        return null; // no need to do anything (you can trigger some event here, like "user logged" event)
    }
}