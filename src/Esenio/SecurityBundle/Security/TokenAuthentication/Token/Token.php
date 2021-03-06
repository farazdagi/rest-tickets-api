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
     * Constructor.
     */
    public function __construct($user, $credentials, array $roles = array())
    {
        parent::__construct($roles);

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
        return serialize(array($this->credentials, parent::serialize()));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($str)
    {
        list($this->credentials, $parentStr) = unserialize($str);
        parent::unserialize($parentStr);
    }
}