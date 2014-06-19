<?php

namespace Esenio\SecurityBundle\Event;


use Symfony\Component\EventDispatcher\Event;

use Esenio\SecurityBundle\Model\UserInterface;

/**
 * Events are dispatched by UserManger whenever it manipulates User entity.
 * @package Esenio\SecurityBundle\Event
 */
class UserEvent extends Event
{
    /**
     * @var UserInterface
     */
    protected $user;

    public function __construct(UserInterface $user)
    {
        $this->user;
    }

    /**
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }
}