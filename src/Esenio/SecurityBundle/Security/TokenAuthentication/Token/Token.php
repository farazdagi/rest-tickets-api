<?php

namespace Esenio\SecurityBundle\Security\TokenAuthentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

use Esenio\SecurityBundle\Model\UserInterface;


class Token extends AbstractToken implements TokenInterface
{
    /**
     * @var string Encoded token string
     */
    private $credentials;

    /**
     * @var TokenDecoderInterface Decoder, able to decode token into original payload.
     */
    private $decoder;

    /**
     * Constructor.
     */
    public function __construct(TokenDecoderInterface $decoder, $user, $credentials, array $roles = array())
    {
        parent::__construct($roles);

        $this->decoder = $decoder;
        $this->credentials = $credentials;

        $this->setUser($user);

        if ($roles) {
            $this->setAuthenticated(true);
        }
    }

    /**
     * Sets the user in the token.
     *
     * The user can be a UserInterface instance, or an object implementing
     * a __toString method or the username as a regular string.
     *
     * @param mixed $user The user
     * @throws \InvalidArgumentException
     */
    public function setUser($user)
    {
        if ($user instanceof UserInterface) {
            $username = $user->getUsername();
        } else {
            $username = (string) $user;
        }

        // anonymous users are just ignored
        if ($username == self::USER_ANONYMOUS) {
            return;
        }

        // make sure that "username" in token is equals to that of supplied user
        $payload = $this->decoder->decodeToken($this->credentials);

        if (!isset($payload['username'])) {
            throw new \InvalidArgumentException('Cannot extract username from token.');
        }

        if ($payload['username'] !== $user->getUsername()) {
            throw new \InvalidArgumentException('Invalid token supplied..');
        }

        parent::setUser($user);
    }

    /**
     * Returns the user credentials.
     *
     * @return mixed The user credentials
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
        parent::eraseCredentials();

        $this->credentials = null;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array($this->credentials, $this->decoder, parent::serialize()));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($str)
    {
        list($this->credentials, $this->decoder, $parentStr) = unserialize($str);
        parent::unserialize($parentStr);
    }
}