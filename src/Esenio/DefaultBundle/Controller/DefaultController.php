<?php

namespace Esenio\DefaultBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Controller\FOSRestController as Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View as RestView;

use Esenio\SecurityBundle\Model\UserManagerInterface;

/**
 * API Entry Point
 *
 * @package Esenio\DefaultBundle\Controller
 * @Route("")
 */
class DefaultController extends Controller
{
    /**
     * @Route("/", name="default_index")
     */
    public function indexAction()
    {
        /** @var UserManagerInterface $userManager */
        $userManager = $this->container->get('esenio_security.user_manager');
        $data = $userManager->findUsers();

        $view = RestView::create($data, 200);
        return $view;
    }

    /**
     * @Route("/secured/", name="default_secured")
     */
    public function securedAction()
    {
        $view = RestView::create(array('message' => 'Welcome, to secret zone!'), 200);
        return $view;
    }
}
