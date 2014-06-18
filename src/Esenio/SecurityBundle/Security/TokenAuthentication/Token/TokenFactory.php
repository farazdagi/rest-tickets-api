<?php

namespace Esenio\SecurityBundle\Security\TokenAuthentication\Token;

use Esenio\SecurityBundle\Model\UserInterface;

/**
 * Creates TokenInterface objects. Encapsulates encoder/decoder setting.
 *
 * @package Esenio\SecurityBundle\Security\TokenAuthentication\Token
 */
class TokenFactory implements TokenFactoryInterface
{
    /**
     * @var TokenEncoderInterface
     */
    protected $encoder;

    /**
     * @var TokenDecoderInterface
     */
    protected $decoder;

    /**
     * @param TokenEncoderInterface $encoder Encoder to encode a token.
     * @param TokenDecoderInterface $decoder Decodet to decode a token.
     */
    public function __construct(TokenEncoderInterface $encoder, TokenDecoderInterface $decoder)
    {
        $this->encoder = $encoder;
        $this->decoder = $decoder;
    }

    /**
     * Creates TokenInterface object. If UserInterface parameter is not supplied, anonymous token is created.
     *
     * @param UserInterface $user User associated with token
     * @param $credentials String representation of token (encoded).
     * @param array $roles Roles assigned to token, token gets authenticated if roles are not empty
     *
     * @return TokenInterface
     */
    public function createToken(UserInterface $user = null, $credentials = '', array $roles = array())
    {
        if (!$user) {
            $user = TokenInterface::USER_ANONYMOUS;
        }

        $token = new Token($this->decoder, $user, $credentials, $roles);

        return $token;
    }
}