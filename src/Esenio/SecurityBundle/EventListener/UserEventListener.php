<?php

namespace Esenio\SecurityBundle\EventListener;


use Esenio\SecurityBundle\Event\UserEvent;

class UserEventListener implements UserEventListenerInterface
{
    public function onUserBeforeDeleteEvent(UserEvent $event)
    {
        // TODO: Implement onUserBeforeDeleteEvent() method.
    }

    public function onUserCreationEvent(UserEvent $event)
    {
        // TODO: Implement onUserCreationEvent() method.
    }
}