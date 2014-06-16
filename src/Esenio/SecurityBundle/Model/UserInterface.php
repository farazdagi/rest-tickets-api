<?php

namespace Esenio\SecurityBundle\Model;

use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * User interface, based on FOSUserBundle implementation.
 * @package Esenio\SecurityBundle\Security\Model
 */
interface UserInterface extends AdvancedUserInterface, EquatableInterface
{
    const ROLE_USER = 'ROLE_USER';
    const ROLE_STAFF = 'ROLE_STAFF';
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    /**
     * Gets entity id.
     *
     * @return string
     */
    public function getId();

    /**
     * Gets first name
     *
     * @return string
     */
    public function getFname();

    /**
     * Sets first name
     *
     * @param string $firstName
     * @return UserInterface
     */
    public function setFname($firstName);

    /**
     * Gets last name
     *
     * @return string
     */
    public function getLname();

    /**
     * Sets last name
     *
     * @param string $lastName
     * @return UserInterface
     */
    public function setLname($lastName);


    /**
     * Gets first name + last name (if any)
     *
     * @return string
     */
    public function getFullName();

    /**
     * Gets the username
     *
     * @return string
     */
    public function getUsername();

    /**
     * Sets the username.
     *
     * @param string $username
     * @return UserInterface
     */
    public function setUsername($username);

    /**
     * Gets email.
     *
     * @return string
     */
    public function getEmail();

    /**
     * Sets the email.
     *
     * @param string $email
     *
     * @return UserInterface
     */
    public function setEmail($email);

    /**
     * Gets the plain password.
     *
     * @return string
     */
    public function getPlainPassword();

    /**
     * Sets the plain password.
     *
     * @param string $password
     * @return UserInterface
     */
    public function setPlainPassword($password);

    /**
     * Sets the hashed password.
     *
     * @param string $password
     * @return UserInterface
     */
    public function setPassword($password);

    /**
     * Tells if the the given user has the super admin role.
     *
     * @return boolean
     */
    public function isSuperAdmin();

    /**
     * Sets the super admin status
     *
     * @param boolean $boolean
     *
     * @return UserInterface
     */
    public function setSuperAdmin($boolean);

    /**
     * @param boolean $boolean
     *
     * @return UserInterface
     */
    public function setEnabled($boolean);

    /**
     * @param boolean $boolean
     *
     * @return UserInterface
     */
    public function setExpired($boolean);

    /**
     * Tells whether account is locked or not
     *
     * @return boolean
     */
    public function isLocked();

    /**
     * Sets the locking status of the user.
     *
     * @param boolean $boolean
     *
     * @return UserInterface
     */
    public function setLocked($boolean);

    /**
     * Gets the timestamp that the user requested a password reset.
     *
     * @return null|\Datetime
     */
    public function getPasswordRequestedAt();

    /**
     * Sets the timestamp that the user requested a password reset.
     *
     * @param null|\DateTime $date
     *
     * @return UserInterface
     */
    public function setPasswordRequestedAt(\DateTime $date = null);

    /**
     * Gets the timestamp that the user account expires.
     *
     * @return null|\Datetime
     */
    public function getExpiresAt();

    /**
     * Sets the timestamp that the user account expires.
     *
     * @param null|\DateTime $date
     *
     * @return UserInterface
     */
    public function setExpiresAt(\DateTime $date);

    /**
     * Checks whether the password reset request has expired.
     *
     * @param integer $ttl Requests older than this many seconds will be considered expired
     *
     * @return boolean true if the user's password request is non expired, false otherwise
     */
    public function isPasswordRequestNonExpired($ttl);

    /**
     * Gets the last login time
     * @return \DateTime
     */
    public function getLastLogin();

    /**
     * Sets the last login time
     *
     * @param \DateTime $time
     *
     * @return UserInterface
     */
    public function setLastLogin(\DateTime $time = null);

    /**
     * Sets the roles of the user.
     *
     * This overwrites any previous roles.
     *
     * @param array $roles
     *
     * @return UserInterface
     */
    public function setRoles(array $roles);

    /**
     * Adds a role to the user.
     *
     * @param string $role
     *
     * @return UserInterface
     */
    public function addRole($role);

    /**
     * Removes a role to the user.
     *
     * @param string $role
     *
     * @return UserInterface
     */
    public function removeRole($role);
} 