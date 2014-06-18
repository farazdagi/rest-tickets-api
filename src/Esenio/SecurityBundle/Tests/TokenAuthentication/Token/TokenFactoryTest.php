<?php

namespace Esenio\SecurityBundle\Tests\TokenAuthentication\Token;

use Esenio\TestingBundle\UnitTesting\TestCase;
use Esenio\SecurityBundle\Model\UserManagerInterface;
use Esenio\SecurityBundle\Model\UserInterface;
use Esenio\SecurityBundle\Security\TokenAuthentication\Token\TokenInterface;
use Esenio\SecurityBundle\Security\TokenAuthentication\Token\TokenFactoryInterface;
use Esenio\SecurityBundle\Security\TokenAuthentication\Token\TokenEncoderInterface;


class TokenFactoryTest extends TestCase
{
    /**
     * @var TokenFactoryInterface
     */
    protected $service;

    /**
     * @var TokenEncoderInterface
     */
    protected $encoder;

    protected function setUp()
    {
        parent::setUp();
        $this->boot();

        $this->service = $this->container->get('esenio_security.token_factory');
        $this->encoder = $this->container->get('esenio_security.token_authentication.token_encoder');
    }

    public function testInit()
    {
        $this->assertTrue($this->service instanceof TokenFactoryInterface);
    }

    public function testCreateNormalToken()
    {
        $this->loadFixtures();

        /** @var UserManagerInterface $userManager */
        $userManager = $this->container->get('esenio_security.user_manager');
        $users = $userManager->findUsers();
        /** @var UserInterface $user */
        $user = $users[0];

        $payload = array(
            'username' => $user->getUsername(),
            'exp' => time() + 3600
        );

        $token = $this->service->createToken($user, $this->encoder->encodeToken($payload));

        $this->assertTrue($token instanceof TokenInterface);
        $this->assertTrue($token->getUser() instanceof UserInterface);
        $this->assertEquals($user->getUsername(), $token->getUsername());
    }

    public function testCreateAnonymousToken()
    {
        $this->loadFixtures();

        $token = $this->service->createToken();

        $this->assertTrue($token instanceof TokenInterface);
        $this->assertNull($token->getUser() );
        $this->assertEquals('', $token->getUsername());
    }
}
 