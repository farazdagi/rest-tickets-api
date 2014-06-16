<?php

namespace Esenio\SecurityBundle\Model;


use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Esenio\SecurityBundle\Entity\UserRepository;

/**
 * Interface serves as additional layer of abstraction between application and user repository.
 * Note: Based on FOSUserBundle implementation.
 * @package Esenio\SecurityBundle\Security\Model
 */
interface UserManagerInterface
{
    /**
     * Creates an empty user instance.
     *
     * @return UserInterface
     */
    public function createUser();

    /**
     * Saves user to persistent storage.
     *
     * @param UserInterface $user
     * @return UserInterface
     */
    public function saveUser(UserInterface $user);

    /**
     * Deletes a user.
     *
     * @param UserInterface $user
     *
     * @return void
     */
    public function deleteUser(UserInterface $user);

    /**
     * Finds one user by the given criteria.
     *
     * @param array $criteria
     *
     * @return UserInterface
     */
    public function findUserBy(array $criteria);

    /**
     * Find a user by its username.
     *
     * @param string $username
     *
     * @return UserInterface or null if user does not exist
     */
    public function findUserByUsername($username);

    /**
     * Finds a user by its email.
     *
     * @param string $email
     *
     * @return UserInterface or null if user does not exist
     */
    public function findUserByEmail($email);

    /**
     * Finds a user by its username or email.
     *
     * @param string $usernameOrEmail
     *
     * @return UserInterface or null if user does not exist
     */
    public function findUserByUsernameOrEmail($usernameOrEmail);

    /**
     * Returns a collection with all user instances.
     *
     * @return \Traversable
     */
    public function findUsers();

    /**
     * Returns the user's fully qualified class name.
     *
     * @return string
     */
    public function getClass();

    /**
     * Reloads a user.
     *
     * @param UserInterface $user
     *
     * @return UserManager
     */
    public function reloadUser(UserInterface $user);

    /**
     * Populates a user (recalculate/copy/transform internal user fields).
     *
     * @param UserInterface $user
     *
     * @return UserManagerInterface
     */
    public function populateUser(UserInterface $user);

    /**
     * Updates a user password if a plain password is set.
     *
     * @param UserInterface $user
     *
     * @return UserManagerInterface
     */
    public function encodePassword(UserInterface $user);

    /**
     * @param UserInterface $user
     * @return PasswordEncoderInterface
     */
    public function getEncoder(UserInterface $user);

    /**
     * @return UserRepository
     */
    public function getRepository();
}

