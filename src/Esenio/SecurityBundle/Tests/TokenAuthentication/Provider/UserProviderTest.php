<?php

namespace Esenio\SecurityBundle\Tests\TokenAuthentication\Provider;

use Symfony\Component\Security\Core\User\UserInterface as BaseUserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface as BaseUserProviderInterface;
use Faker;

use Esenio\TestingBundle\UnitTesting\TestCase;
use Esenio\SecurityBundle\Security\TokenAuthentication\Provider\UserProviderInterface;
use Esenio\SecurityBundle\Model\UserManagerInterface;
use Esenio\SecurityBundle\Model\UserInterface;
use Esenio\SecurityBundle\Security\TokenAuthentication\Token\TokenInterface;
use Esenio\SecurityBundle\Security\TokenAuthentication\Token\Token;
use Esenio\SecurityBundle\Security\TokenAuthentication\Token\TokenEncoder;


class MockUser implements BaseUserInterface
{
    public function getRoles() {}
    public function getPassword() {}
    public function getSalt() {}
    public function getUsername() {}
    public function eraseCredentials() {}
}


class UserProviderTest extends TestCase
{
    /**
     * @var UserProviderInterface
     */
    protected $service;

    protected function setUp()
    {
        parent::setUp();

        $this->boot();
        $this->service = $this->container->get('esenio_security.token_authentication.user_provider');
    }

    public function testInit()
    {
        $this->assertTrue($this->service instanceof BaseUserProviderInterface);
        $this->assertTrue($this->service instanceof UserProviderInterface);
    }

    public function testGetUsernameForToken()
    {
        $this->loadFixtures();

        /** @var UserManagerInterface $userManager */
        $userManager = $this->container->get('esenio_security.user_manager');
        $users = $userManager->findUsers();
        /** @var UserInterface $user */
        $user = $users[0];

        /** @var TokenEncoder $encoder */
        $encoder = new TokenEncoder(
            $this->container->getParameter('esenio_security.secret'),
            $this->container->getParameter('esenio_security.jwt.algo'));

        $payload = array(
            'username' => $user->getUsername(),
            'exp' => time() + 3600
        );

        /** @var TokenInterface $token */
        $token = new Token($user, $encoder->encodeToken($payload));

        $username = $this->service->getUsernameForToken($token);
        $this->assertEquals($username, $user->getUsername());
    }

    public function testGetUsernameForTokenInvalidTokenExample()
    {
        $this->setExpectedException('Symfony\Component\Security\Core\Exception\UsernameNotFoundException');

        /** @var TokenEncoder $encoder */
        $encoder = new TokenEncoder(
            $this->container->getParameter('esenio_security.secret'),
            $this->container->getParameter('esenio_security.jwt.algo'));

        $payload = array(
            'username' => Token::USER_ANONYMOUS,
            'exp' => time() + 3600
        );

        /** @var TokenInterface $token */
        $token = new Token(Token::USER_ANONYMOUS, $encoder->encodeToken($payload));

        $this->service->getUsernameForToken($token);
    }

    public function testLoadByUsername()
    {
        $this->loadFixtures();

        /** @var UserManagerInterface $userManager */
        $userManager = $this->container->get('esenio_security.user_manager');

        /** @var UserInterface $user1 */
        foreach ($userManager->findUsers() as $user1) {
            $user2 = $this->service->loadUserByUsername($user1->getUsername());
            $this->assertTrue($user1->isEqualTo($user2)); // compare directly loaded with ones loaded via UserProvider
        }
    }

    public function testLoadByUserNameNotFoundException()
    {
        $faker = Faker\Factory::create();
        $username = $faker->userName;

        $this->setExpectedException('Symfony\Component\Security\Core\Exception\UsernameNotFoundException', sprintf(
            'Username "%s" does not exist.', $username
        ));

        $this->service->loadUserByUsername($username);
    }

    public function testRefreshUser()
    {
        $this->loadFixtures();

        $faker = Faker\Factory::create();

        /** @var UserManagerInterface $userManager */
        $userManager = $this->container->get('esenio_security.user_manager');
        $users = $userManager->findUsers();

        /** @var UserInterface $user */
        $user = $users[0];

        $username = $user->getUsername();
        $email = $user->getEmail();

        $user->setEmail('changed-email@gmail.com');

        $this->assertNotEquals($email, $user->getEmail());
        $this->service->refreshUser($user);
        $this->assertEquals($email, $user->getEmail());
    }

    public function testRefreshUserInvalidBaseClass()
    {
        $user = new MockUser();

        $this->setExpectedException('Symfony\Component\Security\Core\Exception\UnsupportedUserException',
            sprintf('Instances of "%s" are not supported.', get_class($user))
        );
        $this->service->refreshUser($user);
    }

    public function testSupportsClassMethod()
    {
        /** @var UserManagerInterface $userManager */
        $userManager = $this->container->get('esenio_security.user_manager');
        $users = $userManager->findUsers();

        /** @var UserInterface $user */
        $user = $users[0];

        $this->assertTrue($this->service->supportsClass(get_class($user)));
    }
}
 