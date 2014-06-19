<?php

namespace Esenio\SecurityBundle\EventListener;


use Esenio\SecurityBundle\Event\UserEvent;

interface UserEventListenerInterface
{
    public function onUserCreationEvent(UserEvent $event);
    public function onUserBeforeDeleteEvent(UserEvent $event);
}