<?php

namespace Esenio\SecurityBundle\Security\TokenAuthentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface as BaseTokenInterface;

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
     * @return array|bool False if token cannot be decoded, decoded token payload otherwise.
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