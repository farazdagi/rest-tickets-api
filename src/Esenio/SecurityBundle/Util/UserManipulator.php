<?php

namespace Esenio\SecurityBundle\Util;


use Esenio\SecurityBundle\Model\UserManagerInterface;
use Esenio\SecurityBundle\Model\UserInterface;
use Esenio\SecurityBundle\Util\UserManipulatorInterface;

/**
 * Heavily based on FOSUserBundle implementation.
 *
 * Class UserManipulator
 * @package Esenio\SecurityBundle\Util
 */
class UserManipulator implements UserManipulatorInterface
{
    /**
     * @var UserManagerInterface
     */
    protected $userManager;

    public function __construct(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * Creates a user and returns it.
     *
     * @param string  $username
     * @param string  $password
     * @param string  $email
     * @param Boolean $active
     * @param Boolean $superadmin
     *
     * @return UserInterface
     */
    public function create($username, $password, $email, $active, $superadmin)
    {
        // disallow duplicate usernames (ORM will throw exception anyway)
        $user = $this->userManager->findUserByUsername($username);
        if ($user) { // user already exists
            return null;
        }

        $user = $this->userManager->createUser();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPlainPassword($password);
        $user->setEnabled((Boolean) $active);
        $user->setSuperAdmin((Boolean) $superadmin);

        $this->userManager
            ->populateUser($user)
            ->saveUser($user)
        ;

        return $user;
    }

    /**
     * Activates the given user.
     *
     * @param string $username
     * @return UserInterface
     */
    public function activate($username)
    {
        $user = $this->findUserByUsernameOrThrowException($username);
        $user->setEnabled(true);
        $this->userManager->saveUser($user);

        return $user;
    }

    /**
     * Deactivates the given user.
     *
     * @param string $username
     * @return UserInterface
     */
    public function deactivate($username)
    {
        $user = $this->findUserByUsernameOrThrowException($username);
        $user->setEnabled(false);
        $this->userManager->saveUser($user);

        return $user;
    }

    /**
     * Changes the password for the given user.
     *
     * @param string $username
     * @param string $password
     * @return UserInterface
     */
    public function changePassword($username, $password)
    {
        $user = $this->findUserByUsernameOrThrowException($username);
        $user->setPlainPassword($password);
        $this->userManager
            ->encodePassword($user)
            ->saveUser($user);

        return $user;
    }

    /**
     * Promotes the given user.
     *
     * @param string $username
     * @return UserInterface
     */
    public function promote($username)
    {
        $user = $this->findUserByUsernameOrThrowException($username);
        $user->setSuperAdmin(true);
        $this->userManager->saveUser($user);

        return $user;
    }

    /**
     * Demotes the given user.
     *
     * @param string $username
     * @return UserInterface
     */
    public function demote($username)
    {
        $user = $this->findUserByUsernameOrThrowException($username);
        $user->setSuperAdmin(false);
        $this->userManager->saveUser($user);

        return $user;
    }

    /**
     * Adds role to the given user.
     *
     * @param string $username
     * @param string $role
     *
     * @return Boolean true if role was added, false if user already had the role
     */
    public function addRole($username, $role)
    {
        $user = $this->findUserByUsernameOrThrowException($username);
        if ($this->isRoleInList($user->getRoles(), $role)) {
            return false;
        }
        $user->addRole($role);
        $this->userManager->saveUser($user);

        return true;
    }

    /**
     * Removes role from the given user.
     *
     * @param string $username
     * @param string $role
     *
     * @return Boolean true if role was removed, false if user didn't have the role
     */
    public function removeRole($username, $role)
    {
        $user = $this->findUserByUsernameOrThrowException($username);
        if (!$this->isRoleInList($user->getRoles(), $role)) {
            return false;
        }
        $user->removeRole($role);
        $this->userManager->saveUser($user);

        return true;
    }

    /**
     * Finds a user by his username and throws an exception if we can't find it.
     *
     * @param string $username
     * @return UserInterface
     * @throws \InvalidArgumentException
     */
    protected  function findUserByUsernameOrThrowException($username)
    {
        $user = $this->userManager->findUserByUsername($username);

        if (!$user) {
            throw new \InvalidArgumentException(sprintf('User identified by "%s" username does not exist.', $username));
        }

        return $user;
    }

    /**
     * Checks if role exists within roles array
     *
     * @param array $roles Roles to look through
     * @param string $role User role
     * @return bool
     */
    protected  function isRoleInList(array $roles, $role)
    {
        return in_array(strtoupper($role), $roles, true);
    }
}
