<?php

namespace Esenio\SecurityBundle\Security\TokenAuthentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface as BaseTokenInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;


/**
 * Encodes JWT token into string representation.
 * Is able to decode encoded string, back to JWT payload (array) form.
 *
 * @package Esenio\SecurityBundle\Security\TokenAuthentication\Token
 */
class TokenEncoder implements TokenEncoderInterface, TokenDecoderInterface
{
    /**
     * @var string Private key used for encoding.
     */
    private $secretKey;

    /**
     * @var string The signing algorithm. Supported algorithms are 'HS256', 'HS384' and 'HS512'.
     */
    private $algorithm;

    /**
     * @param string $secretKey Private key used for encoding/decoding
     * @param string $algorithm Encoding algorithm. Algorithms are 'HS256', 'HS384' and 'HS512'.
     * @throws \InvalidArgumentException
     */
    public function __construct($secretKey, $algorithm = 'HS256')
    {
        if (empty($secretKey)) {
            throw new \InvalidArgumentException('Please provide private key for encoding.');
        }

        if (empty($algorithm)) {
            throw new \InvalidArgumentException('Please provide encoding algorithm.');
        }

        $this->secretKey = $secretKey;
        $this->algorithm = $algorithm;
    }

    /**
     * Encodes the raw token.
     *
     * @param array $raw The token payload to encode
     *
     * @throws \InvalidArgumentException
     * @return string The encoded token
     */
    public function encodeToken(array $raw)
    {
        if (!isset($raw['username'], $raw['exp'])) {
            throw new \InvalidArgumentException('Token payload requires fields: "username", "exp"');
        }

        return \JWT::encode($raw, $this->secretKey, $this->algorithm);
    }

    /**
     * Checks whether encoded token can be decoded.
     *
     * @param string $encoded An encoded token
     *
     * @return bool true if the token is valid, false otherwise
     */
    public function isTokenValid($encoded)
    {
        try {
            \JWT::decode($encoded, $this->secretKey, true);
        } catch (\UnexpectedValueException $e) {
            return false;
        }
        return true;
    }

    /**
     * Decodes token, and returns its payload.
     *
     * @param string $encoded Encoded token string
     *
     * @throws BadCredentialsException
     * @return array Decoded token payload is returned on success. Exceptions are thrown otherwise.
     */
    public function decodeToken($encoded)
    {
        try {
            $payload = \JWT::decode($encoded, $this->secretKey, true);
        } catch (\UnexpectedValueException $e) {
            throw new BadCredentialsException($e->getMessage());
        }

        return get_object_vars($payload);
    }

    /**
     * Checks whether supplied token is supported
     *
     * @param BaseTokenInterface $token
     * @return boolean
     */
    public function supportsToken(BaseTokenInterface $token)
    {
        return $token instanceof TokenInterface;
    }
}