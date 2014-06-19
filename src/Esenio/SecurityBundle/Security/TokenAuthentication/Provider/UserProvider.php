<?php

namespace Esenio\SecurityBundle\Security\TokenAuthentication\Provider;

use Symfony\Component\Security\Core\User\UserInterface as BaseUserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

use Esenio\SecurityBundle\Model\UserInterface;
use Esenio\SecurityBundle\Model\UserManagerInterface;
use Esenio\SecurityBundle\Security\TokenAuthentication\Token\TokenInterface;


/**
 * Provides user lookup service (by username, and using token).
 *
 * @package Esenio\SecurityBundle\Security\TokenAuthentication\Provider
 */
class UserProvider implements UserProviderInterface
{
    /**
     * @var UserManagerInterface
     */
    protected $userManager;

    /**
     * @param UserManagerInterface $userManager Service manipulating user profiles
     */
    public function __construct(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * Lookup username based on the JWT Token passed.
     *
     * @param TokenInterface $token The token
     * @throws UsernameNotFoundException if the user is not found
     * @return string
     */
    public function getUsernameForToken(TokenInterface $token)
    {
        $username = $token->getUsername();

        if (!$username) {
            throw new UsernameNotFoundException('Cannot extract username for Bearer Token');
        }

        return $username;
    }

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not found.
     *
     * @param string $username The username
     * @see UsernameNotFoundException
     * @throws UsernameNotFoundException if the user is not found
     * @return UserInterface
     */
    public function loadUserByUsername($username)
    {
        $user = $this->userManager->findUserByUsername($username);
        if (!$user) {
            throw new UsernameNotFoundException(
                sprintf("Username '%s' does not exist.", $username)
            );
        }

        return $user;
    }

    /**
     * Refreshes the user for the account interface.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @param BaseUserInterface $user
     * @return UserInterface
     * @throws UnsupportedUserException if the account is not supported
     */
    public function refreshUser(BaseUserInterface $user)
    {
        if (!$user instanceof UserInterface) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        $this->userManager->reloadUser($user);

        return $user;
    }

    /**
     * Whether this provider supports the given user class
     *
     * @param string $class
     * @return bool
     */
    public function supportsClass($class)
    {
        return $class === 'Esenio\SecurityBundle\Entity\User';
    }
}