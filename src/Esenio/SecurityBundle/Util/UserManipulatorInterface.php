<?php

namespace Esenio\SecurityBundle\Util;


use Esenio\SecurityBundle\Model\UserInterface;

/**
 * Interface defines some handy tasks that you might do using UserManagerInterface (w/o actually using it directly).
 *
 * @package Esenio\SecurityBundle\Tests\Util
 */
interface UserManipulatorInterface
{
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
    public function create($username, $password, $email, $active, $superadmin);

    /**
     * Activates the given user.
     *
     * @param string $username
     * @return UserInterface
     */
    public function activate($username);

    /**
     * Deactivates the given user.
     *
     * @param string $username
     * @return UserInterface
     */
    public function deactivate($username);

    /**
     * Changes the password for the given user.
     *
     * @param string $username
     * @param string $password
     * @return UserInterface
     */
    public function changePassword($username, $password);

    /**
     * Promotes the given user.
     *
     * @param string $username
     * @return UserInterface
     */
    public function promote($username);

    /**
     * Demotes the given user.
     *
     * @param string $username
     * @return UserInterface
     */
    public function demote($username);

    /**
     * Adds role to the given user.
     *
     * @param string $username
     * @param string $role
     *
     * @return Boolean true if role was added, false if user already had the role
     */
    public function addRole($username, $role);

    /**
     * Removes role from the given user.
     *
     * @param string $username
     * @param string $role
     *
     * @return Boolean true if role was removed, false if user didn't have the role
     */
    public function removeRole($username, $role);
}