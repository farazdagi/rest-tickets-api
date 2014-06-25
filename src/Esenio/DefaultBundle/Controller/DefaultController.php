<?php

namespace Esenio\DefaultBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Controller\FOSRestController as Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View as RestView;

use Esenio\SecurityBundle\Model\UserManagerInterface;
use Esenio\SecurityBundle\Security\TokenAuthentication\Token\TokenInterface;

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
        return $this->view(array('message' => 'Everything is always kuul in Unnkulian Underworld!'));
    }

    /**
     * @Route("/secured/", name="default_secured")
     */
    public function securedAction()
    {
        /** @var TokenInterface $token */
        $token = $this->get('security.context')->getToken();

        $data = array(
            'message' => sprintf('Welcome, %s! This area is protected (or should be!)', $token->getUsername())
        );

        $view = RestView::create($data, 200);
        return $view;
    }
}
