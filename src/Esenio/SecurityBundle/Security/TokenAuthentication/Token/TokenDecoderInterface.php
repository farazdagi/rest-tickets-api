<?php

namespace Esenio\SecurityBundle\Security\TokenAuthentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface as BaseTokenInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

/**
 * Decodes token into its payload
 *
 * @package Esenio\SecurityBundle\Security\TokenAuthentication\Token
 */
interface TokenDecoderInterface
{
    /**
     * Decodes token, and returns its payload.
     *
     * @param string $encoded Encoded token string
     *
     * @throws BadCredentialsException
     * @return array Decoded token payload is returned on success. Exceptions are thrown otherwise.
     */
    public function decodeToken($encoded);

    /**
     * Checks whether supplied token is supported
     *
     * @param BaseTokenInterface $token
     * @return boolean
     */
    public function supportsToken(BaseTokenInterface $token);
}