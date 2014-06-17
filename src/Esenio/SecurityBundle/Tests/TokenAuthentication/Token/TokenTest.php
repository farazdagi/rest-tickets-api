<?php

namespace Esenio\SecurityBundle\Tests\TokenAuthentication\Token;

use Esenio\TestingBundle\UnitTesting\TestCase;
use Esenio\SecurityBundle\Model\UserManagerInterface;
use Esenio\SecurityBundle\Model\UserInterface;
use Esenio\SecurityBundle\Security\TokenAuthentication\Token\TokenEncoderInterface;
use Esenio\SecurityBundle\Security\TokenAuthentication\Token\TokenDecoderInterface;
use Esenio\SecurityBundle\Security\TokenAuthentication\Token\TokenEncoder;
use Esenio\SecurityBundle\Security\TokenAuthentication\Token\TokenInterface;
use Esenio\SecurityBundle\Security\TokenAuthentication\Token\Token;


class TokenTest extends TestCase
{
    /**
     * @var TokenEncoderInterface
     */
    protected $encoder;

    /**
     * @var TokenDecoderInterface
     */
    protected $decoder;

    protected function setUp()
    {
        parent::setUp();
        $this->boot();

        /** @var TokenEncoder $encoder */
        /** @var TokenDecoderInterface $decoder */
        $this->decoder = $this->encoder = new TokenEncoder(
            $this->container->getParameter('esenio_security.secret'),
            $this->container->getParameter('esenio_security.jwt.algo'));

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

        /** @var TokenInterface $token */
        $token = new Token($this->decoder, $user, $this->encoder->encodeToken($payload));

        $this->assertTrue($token instanceof TokenInterface);
        $this->assertTrue($token->getUser() instanceof UserInterface);
        $this->assertEquals($user->getUsername(), $token->getUsername());
    }

    public function testConstructionWithUserObjectAndInvalidPayload()
    {
        $this->loadFixtures();

        $this->setExpectedException('InvalidArgumentException', 'Cannot extract username from token.');

        /** @var UserManagerInterface $userManager */
        $userManager = $this->container->get('esenio_security.user_manager');
        $users = $userManager->findUsers();
        /** @var UserInterface $user */
        $user = $users[0];

        /** @var TokenInterface $token */
        $token = new Token($this->decoder, $user, 'somejunk');
    }

    public function testConstructionWithUserObjectWithTokenForged()
    {
        $this->loadFixtures();

        $this->setExpectedException('InvalidArgumentException', 'Invalid token supplied..');

        /** @var UserManagerInterface $userManager */
        $userManager = $this->container->get('esenio_security.user_manager');
        $users = $userManager->findUsers();
        /** @var UserInterface $user */
        $user = $users[0];

        $payload = array(
            'username' => $user->getUsername(),
            'exp' => time() + 3600
        );

        $user->setUsername('root'); // we try to pass user having different user name then the one contained in token

        /** @var TokenInterface $token */
        $token = new Token($this->decoder, $user, $this->encoder->encodeToken($payload));
    }

    public function testConstructionWithAnonUser()
    {
        $payload = array(
            'username' => TokenInterface::USER_ANONYMOUS,
            'exp' => time() + 3600
        );

        /** @var TokenInterface $token */
        $token = new Token($this->decoder, TokenInterface::USER_ANONYMOUS, $this->encoder->encodeToken($payload));

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

        /** @var TokenInterface $token */
        $token = new Token($this->decoder, TokenInterface::USER_ANONYMOUS, $this->encoder->encodeToken($payload), $roles);

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

        /** @var TokenInterface $token */
        $token = new Token($this->decoder, $user, $this->encoder->encodeToken($payload));

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

        /** @var TokenInterface $token */
        $token = new Token($this->decoder, $user, $this->encoder->encodeToken($payload));

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

        /** @var TokenInterface $token */
        $token = new Token($this->decoder, $user, $this->encoder->encodeToken($payload));

        $this->assertEquals($token, unserialize(serialize($token)));
    }
}
 