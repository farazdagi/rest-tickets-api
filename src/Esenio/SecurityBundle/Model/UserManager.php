<?php

namespace Esenio\SecurityBundle\Model;

use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Esenio\SecurityBundle\Entity\UserRepository;
use Esenio\SecurityBundle\Model\UserInterface;
use Esenio\SecurityBundle\Entity\User;
use Esenio\SecurityBundle\Event\UserEvent;

class UserManager implements UserManagerInterface
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var EntityManagerInterface
     */
    protected $em;
    /**
     * @var UserRepository
     */
    protected $repository;

    /**
     * @var EncoderFactoryInterface
     */
    protected $encoderFactory;

    public function __construct(EventDispatcherInterface $eventDispatcher, EntityManagerInterface $em, UserRepository $repository, EncoderFactoryInterface $encoderFactory)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->em = $em;
        $this->repository = $repository;
        $this->encoderFactory = $encoderFactory;
    }

    /**
     * Creates an empty user instance.
     *
     * @return UserInterface
     */
    public function createUser()
    {
        $class = $this->getClass();
        $user = new $class;

        // notify listeners
        $this->eventDispatcher->dispatch('esenio_security.user.user_created', new UserEvent($user));

        return $user;
    }

    /**
     * Saves user to persistent storage.
     *
     * @param UserInterface $user
     * @return UserInterface
     */
    public function saveUser(UserInterface $user)
    {
        $this->em->persist($user);
        $this->em->flush();

        // notify listeners
        $this->eventDispatcher->dispatch('esenio_security.user.user_saved', new UserEvent($user));

        return $user;
    }

    /**
     * Deletes a user.
     *
     * @param UserInterface $user
     *
     * @return void
     */
    public function deleteUser(UserInterface $user)
    {
        // notify listeners
        $this->eventDispatcher->dispatch('esenio_security.user.user_before_delete', new UserEvent($user));

        $this->em->remove($user);
        $this->em->flush();
    }

    /**
     * Populates a user (recalculate/copy/transform internal user fields).
     *
     * @param UserInterface $user
     *
     * @return UserManagerInterface
     */
    public function populateUser(UserInterface $user)
    {
        $this->encodePassword($user);
        if ($user->getEmail() && !$user->getUsername()) { // if username is not set, try to set it to email
            $user->setUsername($user->getEmail());
        }
        return $this;
    }

    /**
     * Updates a user password if a plain password is set.
     *
     * @param UserInterface $user
     *
     * @return UserManagerInterface
     */
    public function encodePassword(UserInterface $user)
    {
        if (0 !== strlen($password = $user->getPlainPassword())) {
            $encoder = $this->getEncoder($user);
            $user->setPassword($encoder->encodePassword($password, $user->getSalt()));
            $user->eraseCredentials();
        }
        return $this;
    }

    /**
     * Finds one user by the given criteria.
     *
     * @param array $criteria
     *
     * @return UserInterface
     */
    public function findUserBy(array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * Find a user by its username.
     *
     * @param string $username
     *
     * @return UserInterface or null if user does not exist
     */
    public function findUserByUsername($username)
    {
        return $this->findUserBy(array(
            'username' => $username
        ));
    }

    /**
     * Finds a user by its email.
     *
     * @param string $email
     *
     * @return UserInterface or null if user does not exist
     */
    public function findUserByEmail($email)
    {
        return $this->findUserBy(array(
            'email' => $email
        ));
    }

    /**
     * Finds a user by its username or email.
     *
     * @param string $usernameOrEmail
     *
     * @return UserInterface or null if user does not exist
     */
    public function findUserByUsernameOrEmail($usernameOrEmail)
    {
        if (filter_var($usernameOrEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->findUserByEmail($usernameOrEmail);
        }

        return $this->findUserByUsername($usernameOrEmail);
    }

    /**
     * Returns a collection with all user instances.
     *
     * @return \Traversable
     */
    public function findUsers()
    {
        return $this->repository->findAll();
    }

    /**
     * Reloads a user.
     *
     * @param UserInterface $user
     * @return UserInterface|void
     * @throws UsernameNotFoundException
     */
    public function reloadUser(UserInterface $user)
    {
        $reloadedUser = $this->findUserByUsername($user->getUsername());
        if (!$reloadedUser) {
            throw new UsernameNotFoundException(sprintf("No user with name '%s' was found.", $user->getUsername()));
        }

        $this->em->refresh($reloadedUser);

        return $reloadedUser;
    }

    /**
     * Returns the user's fully qualified class name.
     *
     * @return string
     */
    public function getClass()
    {
        return '\Esenio\SecurityBundle\Entity\User';
    }

    /**
     * @param UserInterface $user
     * @return PasswordEncoderInterface
     */
    public function getEncoder(UserInterface $user)
    {
        return $this->encoderFactory->getEncoder($user);
    }

    /**
     * @return UserRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }
}