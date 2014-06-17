<?php

namespace Esenio\SecurityBundle\Security\TokenAuthentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface as BaseTokenInterface;


/**
 * Use this interface for any kind of pre-authenticated token string encoding.
 *
 * @package Esenio\SecurityBundle\Security\TokenAuthentication\Token
 */
interface TokenEncoderInterface
{
    /**
     * Encodes the raw token.
     *
     * @param array $raw The token payload to encode
     *
     * @return string The encoded token
     */
    public function encodeToken(array $raw);

    /**
     * Checks whether encoded token can be decoded.
     *
     * @param string $encoded   An encoded token
     *
     * @return bool true if the token is valid, false otherwise
     */
    public function isTokenValid($encoded);

    /**
     * Checks whether supplied token is supported
     *
     * @param BaseTokenInterface $token
     * @return boolean
     */
    public function supportsToken(BaseTokenInterface $token);
}