<?php

namespace Esenio\SecurityBundle\Security\TokenAuthentication\Token;

use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\HttpFoundation\Request;


/**
 * Interface is used to provide ability for services to issue token based on supplied
 * (in Request) token or username/password credentials.
 *
 * @package Esenio\SecurityBundle\Security\Authentication\TokenAuth
 */
interface TokenIssuerInterface
{
    /**
     * Initializes token using incoming Request values.
     *
     * If "Authorization: Bearer {{ token }}" header is passed, then token is initialized to anonymous user
     * with {{ token }} credentials i.e. it will FAIL verifyToken() test. To turn it into valid token
     * use signToken() on it.
     *
     * Otherwise, following steps are taken:
     *
     * 1. POST[username] and POST[password] parameters are extracted (from Request) into local variables.
     *    BadCredentialsException is thrown if values are not found.
     * 2. Some service (implementing UserProviderInterface) is used to get UserInterface object.
     *    This object is used to validate the incoming username/password pair.
     * 3. If credentials are valid, user TokenInterface object is created, and returned.
     *    UserInterface object obtained during step 2, is associated with the given token.
     *    Resultant token has valid credentials set as well i.e. it will PASS verifyToken() test.
     *    If credentials are invalid, BadCredentialsException is thrown.
     *
     * @param Request $request
     *
     * @throws BadCredentialsException
     * @return TokenInterface
     */
    public function issueToken(Request $request);

    /**
     * Creates signed version of a given token.
     *
     * Procedure:
     *
     * 1. If token has both user and credentials set: credentials get verified.
     *    If verification passes - the very same TokenInterface object is returned (as it is already signed).
     *    Otherwise, BadCredentialsException is thrown.
     *
     * 2. If credentials are set, but user is anonymous i.e. we got Authentication: Bearer {{ token }} type of request:
     *    Decode token, create signed token with a username encoded as a part of decoded token's payload.
     *    If token cannot be decoded, throw BadCredentialsException.
     *
     * 3. User is set, but no credentials provided i.e. we got POST['username'] + POST['password'] type of request:
     *    Create payload, using incoming username, produce signed version of token.
     *
     * @param TokenInterface $token Token to sign
     * @param int $lifeTime Token lifetime in seconds
     *
     * @throws BadCredentialsException
     * @return TokenInterface
     */
    public function signToken(TokenInterface $token, $lifeTime = 3600);

    /**
     * Verifies that token is valid and correctly signed.
     * Assumed input is valid token, therefore on validation failure exceptions will be thrown.
     *
     * Possible failures:
     * - no user supplied to token
     * - no credentials supplied to token
     * - username in token's user and payload do not match
     * - token is expired
     * - token can not be decoded
     *
     * @param TokenInterface $token
     *
     * @throws BadCredentialsException
     * @return void Nothing is returned, if token is valid - procedure will complete w/o exceptions.
     */
    public function verifyToken(TokenInterface $token);
}