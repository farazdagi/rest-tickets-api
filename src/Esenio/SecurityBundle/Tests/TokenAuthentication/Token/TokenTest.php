<?php

namespace Esenio\SecurityBundle\Tests\TokenAuthentication\Token;

use Esenio\TestingBundle\UnitTesting\TestCase;
use Esenio\SecurityBundle\Model\UserManagerInterface;
use Esenio\SecurityBundle\Model\UserInterface;
use Esenio\SecurityBundle\Security\TokenAuthentication\Token\TokenFactoryInterface;
use Esenio\SecurityBundle\Security\TokenAuthentication\Token\TokenEncoderInterface;
use Esenio\SecurityBundle\Security\TokenAuthentication\Token\TokenInterface;


class TokenTest extends TestCase
{
    /**
     * @var TokenEncoderInterface
     */
    protected $encoder;

    /**
     * @var TokenFactoryInterface
     */
    protected $factory;

    protected function setUp()
    {
        parent::setUp();
        $this->boot();

        $this->encoder = $this->container->get('esenio_security.token_authentication.token_encoder');
        $this->factory = $this->container->get('esenio_security.token_factory');
    }

    public function testConstructionWithUserObject()
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

        $token = $this->factory->createToken($user, $this->encoder->encodeToken($payload));

        $this->assertTrue($token instanceof TokenInterface);
        $this->assertTrue($token->getUser() instanceof UserInterface);
        $this->assertEquals($user->getUsername(), $token->getUsername());
    }

    public function testConstructionWithAnonUser()
    {
        $token = $this->factory->createToken();

        $this->assertTrue($token instanceof TokenInterface);
        $this->assertNull($token->getUser() );
        $this->assertEquals('', $token->getUsername());
    }

    public function testConstructionWithAnonUserWithRoles()
    {
        $payload = array(
            'username' => TokenInterface::USER_ANONYMOUS,
            'exp' => time() + 3600
        );

        $roles = array(UserInterface::ROLE_USER, UserInterface::ROLE_ADMIN);

        $token = $this->factory->createToken(null, $this->encoder->encodeToken($payload), $roles);

        $this->assertTrue($token instanceof TokenInterface);
        $this->assertNull($token->getUser() );
        $this->assertEquals('', $token->getUsername());
        $this->assertTrue($token->isAuthenticated());

        $rolesExtracted = array();
        foreach ($token->getRoles() as $role) {
            $rolesExtracted[] = $role->getRole();
        }
        sort($roles);
        sort($rolesExtracted);
        $this->assertEquals($roles, $rolesExtracted);
    }

    public function testGetCredentials()
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

        $token = $this->factory->createToken($user, $this->encoder->encodeToken($payload));

        $this->assertTrue($token instanceof TokenInterface);
        $this->assertTrue($token->getUser() instanceof UserInterface);
        $this->assertEquals($user->getUsername(), $token->getUsername());
        $this->assertEquals($this->encoder->encodeToken($payload), $token->getCredentials());
    }

    public function testEraseCredentials()
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

        $token = $this->factory->createToken($user, $this->encoder->encodeToken($payload));

        $this->assertEquals($this->encoder->encodeToken($payload), $token->getCredentials());
        $token->eraseCredentials();
        $this->assertNull($token->getCredentials());
    }

    public function testTokenSerialization()
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

        $token = $this->factory->createToken($user, $this->encoder->encodeToken($payload));

        $this->assertEquals($token, unserialize(serialize($token)));
    }
}
 