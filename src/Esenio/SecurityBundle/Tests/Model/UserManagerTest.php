<?php

namespace Esenio\SecurityBundle\Tests\Model;

use Esenio\SecurityBundle\Entity\UserRepository;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Faker;

use Esenio\TestingBundle\UnitTesting\TestCase;
use Esenio\SecurityBundle\Model\UserManager;
use Esenio\SecurityBundle\Model\UserManagerInterface;
use Esenio\SecurityBundle\Model\UserInterface;
use Esenio\SecurityBundle\Entity\User;

class UserManagerTest extends TestCase
{
    /**
     * @var UserManagerInterface
     */
    protected $service;

    protected function setUp()
    {
        parent::setUp();
        $this->boot();
        $this->service = $this->container->get('esenio_security.user_manager');
    }

    public function testInit()
    {
        $this->assertTrue($this->service instanceof UserManagerInterface);
        $this->assertTrue($this->service instanceof UserManager);
    }

    public function testUserCreation()
    {
        $this->loadFixtures();

        $user = $this->service->createUser();

        $this->assertTrue($user instanceof UserInterface);
        $this->assertTrue($user instanceof User);

        $this->assertNotNull($user->getSalt());

        $this->assertFalse($user->isLocked());
        $this->assertTrue($user->isEnabled());
        $this->assertTrue($user->isAccountNonExpired());
        $this->assertTrue($user->isAccountNonLocked());
        $this->assertTrue($user->isCredentialsNonExpired());

        $this->assertFalse($user->isSuperAdmin());
        $this->assertFalse($user->isPasswordRequestNonExpired(3600));

        $this->assertArrayHasKey(UserInterface::ROLE_USER, array_flip($user->getRoles()));
    }

    public function testSaveUser()
    {
        $this->loadFixtures();

        $email = 'xxx@gmail.com';
        $user = $this->service->createUser();
        $user->setEmail($email);
        $user->setPlainPassword('topsecret');

        $this->service
            ->populateUser($user)
            ->saveUser($user);

        $this->assertNull($user->getPlainPassword());
        $this->assertEquals($user->getEmail(), $user->getUsername());

        $user1 = $this->service->findUserByEmail($email);
        $this->assertTrue($user->isEnabled($user1));
    }

    public function testFindUsers()
    {
        $this->loadFixtures();

        $users = $this->service->findUsers();
        $this->assertCount(10, $users);

        /** @var User $user */
        foreach ($users as $user) {
            $this->assertTrue($user instanceof UserInterface);
            $this->assertTrue($user instanceof User);
            $this->assertNotNull($user->getUsername());
            $this->assertNotNull($user->getEmail());
            $this->assertNull($user->getPlainPassword());
            $this->assertNotNull($user->getPassword());
        }
    }

    public function testUserDeletion()
    {
        $this->loadFixtures();

        $users = $this->service->findUsers();
        $this->assertCount(10, $users);

        $userInd = rand(0, 9);
        $username = $users[$userInd]->getUsername();

        $user = $this->service->findUserByUsername($username);

        $this->assertTrue($user instanceof UserInterface);
        $this->assertTrue($user instanceof User);
        $this->assertEquals($users[$userInd]->getEmail(), $user->getEmail());
        $this->assertTrue($user->isEqualTo($users[$userInd]));

        $this->service->deleteUser($user);

        $user = $this->service->findUserByUsername($username);
        $this->assertNull($user);

        $users = $this->service->findUsers();
        $this->assertCount(9, $users);
    }

    public function testPopulateUser()
    {
        $this->loadFixtures();

        $user = $this->service->createUser();

        $this->assertNull($user->getEmail());
        $this->assertNull($user->getUsername());
        $this->assertNull($user->getPassword());
        $this->assertNull($user->getPlainPassword());

        $faker = Faker\Factory::create();

        // we only need email + password to get user in!
        $user->setEmail($faker->email);
        $user->setPlainPassword($faker->uuid);

        $this->assertNull($user->getPassword());
        $this->assertNotNull($user->getPlainPassword());
        $this->service->populateUser($user);

        $this->assertNotNull($user->getEmail());
        $this->assertNotNull($user->getUsername());
        $this->assertNotNull($user->getPassword());
        $this->assertNull($user->getPlainPassword());
    }

    public function testFindBy()
    {
        $this->loadFixtures();

        $email = 'xxx@gmail.com';
        $user = $this->service->createUser();
        $user->setEmail($email);
        $user->setUsername('foobar');
        $user->setPlainPassword('topsecret');

        $this->service
            ->populateUser($user)
            ->saveUser($user);

        $this->assertNotNull($this->service->findUserByUsername($user->getUsername()));
        $this->assertNotNull($this->service->findUserByEmail($user->getEmail()));
        $this->assertNotNull($this->service->findUserBy(array('username' => $user->getUsername())));
        $this->assertNotNull($this->service->findUserBy(array('email' => $user->getEmail())));
        $this->assertNotNull($this->service->findUserBy(array('id' => $user->getId())));

        $this->assertNotEquals($user->getEmail(), $user->getUsername());
        $user1 = $this->service->findUserByUsernameOrEmail($user->getEmail());
        $user2 = $this->service->findUserByUsernameOrEmail($user->getUsername());
        $this->assertTrue($user1->isEqualTo($user2));
    }

    public function testReloadUser()
    {
        $this->loadFixtures();

        $faker = Faker\Factory::create();

        $users = $this->service->findUsers();
        /** @var UserInterface $user */
        $user = $users[0];

        $username = $user->getUsername();
        $email = $user->getEmail();
        $password = $user->getPassword();
        $fname = $user->getFname();
        $lname = $user->getLname();

        $user->setEmail($faker->uuid);
        $user->setPassword($faker->uuid);
        $user->setFname($faker->uuid);
        $user->setLname($faker->uuid);

        $this->assertNotEquals($email, $user->getEmail());
        $this->assertNotEquals($password, $user->getPassword());
        $this->assertNotEquals($fname, $user->getFname());
        $this->assertNotEquals($lname, $user->getLname());

        $this->service->reloadUser($user);

        $this->assertEquals($username, $user->getUsername());
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($password, $user->getPassword());
        $this->assertEquals($fname, $user->getFname());
        $this->assertEquals($lname, $user->getLname());
    }

    public function testReloadUserInvalidUserException()
    {
        $this->loadFixtures();

        $faker = Faker\Factory::create();

        $users = $this->service->findUsers();
        /** @var UserInterface $user */
        $user = $users[0];

        $user->setUsername($faker->uuid);
        $this->setExpectedException(
            '\Symfony\Component\Security\Core\Exception\UsernameNotFoundException',
            sprintf('No user with name "%s" was found.', $user->getUsername())
        );

        $this->service->reloadUser($user);
    }

    public function testGetEncoder()
    {
        $encoder = $this->service->getEncoder($this->service->createUser());
        $this->assertTrue($encoder instanceof PasswordEncoderInterface);
    }

    public function testGetRepository()
    {
        $repository = $this->service->getRepository();
        $this->assertTrue($repository instanceof UserRepository);
    }
}
 