<?php

namespace Esenio\SecurityBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\FOSRestController as Controller;
use FOS\RestBundle\View\View as RestView;
use JMS\DiExtraBundle\Annotation as DI;

use Esenio\SecurityBundle\Security\TokenAuthentication\Token\TokenIssuerInterface;


/**
 * This controller provides token-based security actions.
 *
 * @package Esenio\DefaultBundle\Controller
 * @Route("")
 */
class TokenAuthenticationController extends Controller
{
    /**
     * @var TokenIssuerInterface
     * @DI\Inject("esenio_security.token_issuer")
     */
    private $tokenIssuer;

    /**
     * Check user credentials, issue token on success
     *
     * @Route("/authenticate")
     * @Method({"POST", "OPTIONS"})
     */
    public function authenticateAction(Request $request)
    {
        // no need to proceed if client sends pre-flight request
        if ($request->isMethod('OPTIONS')) {
            return RestView::create(null, 204);
        }

        // obtain configuration options
        $params = $this->container->getParameter('esenio_security.jwt');

        try {
            $token = $this->tokenIssuer->issueToken($request);
            $token = $this->tokenIssuer->signToken($token); // after this $token is valid authentication token!
        } catch (BadCredentialsException $e) {
            return $this->issueAuthenticationDemand($e->getMessage());
        } catch (\Exception $e) {
            return $this->issueAuthenticationDemand($e->getMessage());
        }

        return array(
            'access_token'  => $token->getCredentials(),
            'token_type'    => $params['token_type'],
            'expires_in'    => $params['token_lifetime'],
            'links'         => array(
                array('rel' => 'proceed', 'url' => $params['return_url'])
            )
        );
    }

    /**
     * Send WWW-Authenticate response.
     *
     * @param string $message Demand message
     * @return RestView
     */
    private function issueAuthenticationDemand($message)
    {
        return RestView::create(array(
            'status' => 'error',
            'message' => $message
        ), 401, array(
            'WWW-Authenticate' => sprintf(
                'FormBased realm="%s"', $this->container->getParameter('esenio_security.realm')
            )
        ));

    }
}
