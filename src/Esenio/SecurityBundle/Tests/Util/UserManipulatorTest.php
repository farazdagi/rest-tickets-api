<?php

namespace Esenio\SecurityBundle\Tests\Util;


use Symfony\Component\Security\Core\User\UserInterface as BaseUserInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Faker;

use Esenio\TestingBundle\UnitTesting\TestCase;
use Esenio\SecurityBundle\Model\UserInterface;
use Esenio\SecurityBundle\Model\UserManagerInterface;
use Esenio\SecurityBundle\Util\UserManipulatorInterface;
use Esenio\SecurityBundle\Util\UserManipulator;

class UserManipulatorTest extends TestCase
{
    /**
     * @var UserManipulatorInterface
     */
    protected $service;

    protected function setUp()
    {
        parent::setUp();
        $this->boot();
        $this->service = $this->container->get('esenio_security.user_manipulator');
    }

    public function testInitialization()
    {
        $service = $this->container->get('esenio_security.user_manipulator');
        $this->assertTrue($service instanceof UserManipulatorInterface);
        $this->assertTrue($service instanceof UserManipulator);
    }

    public function testCreateUser()
    {
        $faker = Faker\Factory::create();

        // disabled, normal user
        $user = $this->service->create('newly-created-user', 'topsecret', $faker->email, false, false);
        $this->assertTrue($user instanceof BaseUserInterface);
        $this->assertTrue($user instanceof UserInterface);
        $this->assertArrayNotHasKey(UserInterface::ROLE_SUPER_ADMIN, array_flip($user->getRoles()));
        $this->assertFalse($user->isSuperAdmin());
        $this->assertFalse($user->isEnabled());
        $this->assertTrue($user->isAccountNonLocked());
        $this->assertTrue($user->isAccountNonExpired());
        $this->assertFalse($user->isLocked());

        // try creating duplicate
        $user = $this->service->create('newly-created-user', 'topsecret', $faker->email, true, false);
        $this->assertNull($user);

        // enabled, normal admin
        $user = $this->service->create($faker->userName, 'topsecret', $faker->email, true, false);
        $this->assertTrue($user instanceof BaseUserInterface);
        $this->assertTrue($user instanceof UserInterface);
        $this->assertArrayNotHasKey(UserInterface::ROLE_SUPER_ADMIN, array_flip($user->getRoles()));
        $this->assertFalse($user->isSuperAdmin());
        $this->assertTrue($user->isEnabled());
        $this->assertTrue($user->isAccountNonLocked());
        $this->assertTrue($user->isAccountNonExpired());
        $this->assertFalse($user->isLocked());

        // enabled, super-admin
        $user = $this->service->create($faker->userName, 'topsecret', $faker->email, true, true);
        $this->assertTrue($user instanceof BaseUserInterface);
        $this->assertTrue($user instanceof UserInterface);
        $this->assertArrayHasKey(UserInterface::ROLE_SUPER_ADMIN, array_flip($user->getRoles()));
        $this->assertTrue($user->isSuperAdmin());
        $this->assertTrue($user->isEnabled());
        $this->assertTrue($user->isAccountNonLocked());
        $this->assertTrue($user->isAccountNonExpired());
        $this->assertFalse($user->isLocked());
    }

    public function testUserActivation()
    {
        $faker = Faker\Factory::create();

        $username = 'inactive-user';
        $user = $this->service->create($username, 'topsecret', $faker->email, false, false);
        $this->assertTrue($user instanceof BaseUserInterface);
        $this->assertTrue($user instanceof UserInterface);
        $this->assertFalse($user->isSuperAdmin());
        $this->assertFalse($user->isEnabled());
        $this->assertTrue($user->isAccountNonLocked());
        $this->assertTrue($user->isAccountNonExpired());
        $this->assertFalse($user->isLocked());

        $user = $this->service->activate($username);

        $this->assertTrue($user instanceof BaseUserInterface);
        $this->assertTrue($user instanceof UserInterface);
        $this->assertFalse($user->isSuperAdmin());
        $this->assertTrue($user->isEnabled());
        $this->assertTrue($user->isAccountNonLocked());
        $this->assertTrue($user->isAccountNonExpired());
        $this->assertFalse($user->isLocked());
    }

    public function testUserDeactivation()
    {
        $faker = Faker\Factory::create();

        $username = 'active-user';
        $user = $this->service->create($username, 'topsecret', $faker->email, true, false);
        $this->assertTrue($user instanceof BaseUserInterface);
        $this->assertTrue($user instanceof UserInterface);
        $this->assertFalse($user->isSuperAdmin());
        $this->assertTrue($user->isEnabled());
        $this->assertTrue($user->isAccountNonLocked());
        $this->assertTrue($user->isAccountNonExpired());
        $this->assertFalse($user->isLocked());

        $user = $this->service->deactivate($username);

        $this->assertTrue($user instanceof BaseUserInterface);
        $this->assertTrue($user instanceof UserInterface);
        $this->assertFalse($user->isSuperAdmin());
        $this->assertFalse($user->isEnabled());
        $this->assertTrue($user->isAccountNonLocked());
        $this->assertTrue($user->isAccountNonExpired());
        $this->assertFalse($user->isLocked());
    }

    public function testPasswordChange()
    {
        $encoderFactory = $this->container->get('security.encoder_factory');
        $faker = Faker\Factory::create();

        $username = $faker->userName;
        $password1 = 'not-topsecret';
        $password2 = 'topsecret';

        $user = $this->service->create($username, 'not-topsecret', $faker->email, true, false);
        /** @var PasswordEncoderInterface $encoder */
        $encoder = $encoderFactory->getEncoder($user);

        // test that password is actually set
        $this->assertEquals($encoder->encodePassword($password1, $user->getSalt()), $user->getPassword());

        $this->service->changePassword($username, $password2);
        $this->assertNotEquals($encoder->encodePassword($password1, $user->getSalt()), $user->getPassword());
        $this->assertEquals($encoder->encodePassword($password2, $user->getSalt()), $user->getPassword());
    }

    public function testPromote()
    {
        $faker = Faker\Factory::create();

        $username = $faker->userName;
        $user = $this->service->create($username, 'topsecret', $faker->email, true, false);

        $this->assertFalse($user->isSuperAdmin());
        $this->service->promote($username);
        $this->assertTrue($user->isSuperAdmin());
    }

    public function testDemote()
    {
        $faker = Faker\Factory::create();

        $username = $faker->userName;
        $user = $this->service->create($username, 'topsecret', $faker->email, true, true);

        $this->assertTrue($user->isSuperAdmin());
        $this->service->demote($username);
        $this->assertFalse($user->isSuperAdmin());
    }

    public function testAddRole()
    {
        $faker = Faker\Factory::create();
        $username = $faker->userName;

        $user = $this->service->create($username, 'top-secret', $faker->email, true, false);

        $this->assertArrayHasKey(UserInterface::ROLE_USER, array_flip($user->getRoles()));
        $this->assertArrayNotHasKey(UserInterface::ROLE_STAFF, array_flip($user->getRoles()));
        $this->assertArrayNotHasKey(UserInterface::ROLE_ADMIN, array_flip($user->getRoles()));
        $this->assertArrayNotHasKey(UserInterface::ROLE_SUPER_ADMIN, array_flip($user->getRoles()));
        $this->assertFalse($user->isSuperAdmin());

        // add ROLE_STAFF
        $result = $this->service->addRole($username, UserInterface::ROLE_STAFF);
        $this->assertTrue($result);
        $this->assertArrayHasKey(UserInterface::ROLE_USER, array_flip($user->getRoles()));
        $this->assertArrayHasKey(UserInterface::ROLE_STAFF, array_flip($user->getRoles()));
        $this->assertArrayNotHasKey(UserInterface::ROLE_ADMIN, array_flip($user->getRoles()));
        $this->assertArrayNotHasKey(UserInterface::ROLE_SUPER_ADMIN, array_flip($user->getRoles()));
        $this->assertFalse($user->isSuperAdmin());

        // try adding duplicate role
        $result = $this->service->addRole($username, UserInterface::ROLE_STAFF);
        $this->assertFalse($result);
        $this->assertArrayHasKey(UserInterface::ROLE_USER, array_flip($user->getRoles()));
        $this->assertArrayHasKey(UserInterface::ROLE_STAFF, array_flip($user->getRoles()));
        $this->assertArrayNotHasKey(UserInterface::ROLE_ADMIN, array_flip($user->getRoles()));
        $this->assertArrayNotHasKey(UserInterface::ROLE_SUPER_ADMIN, array_flip($user->getRoles()));

        // add ROLE_ADMIN
        $result = $this->service->addRole($username, UserInterface::ROLE_ADMIN);
        $this->assertTrue($result);
        $this->assertArrayHasKey(UserInterface::ROLE_USER, array_flip($user->getRoles()));
        $this->assertArrayHasKey(UserInterface::ROLE_STAFF, array_flip($user->getRoles()));
        $this->assertArrayHasKey(UserInterface::ROLE_ADMIN, array_flip($user->getRoles()));
        $this->assertArrayNotHasKey(UserInterface::ROLE_SUPER_ADMIN, array_flip($user->getRoles()));
        $this->assertFalse($user->isSuperAdmin());

        // add ROLE_SUPER_ADMIN
        $result = $this->service->addRole($username, UserInterface::ROLE_SUPER_ADMIN);
        $this->assertTrue($result);
        $this->assertArrayHasKey(UserInterface::ROLE_USER, array_flip($user->getRoles()));
        $this->assertArrayHasKey(UserInterface::ROLE_STAFF, array_flip($user->getRoles()));
        $this->assertArrayHasKey(UserInterface::ROLE_ADMIN, array_flip($user->getRoles()));
        $this->assertArrayHasKey(UserInterface::ROLE_SUPER_ADMIN, array_flip($user->getRoles()));
        $this->assertTrue($user->isSuperAdmin());
    }

    public function testRemoveRole()
    {
        $faker = Faker\Factory::create();
        $username = $faker->userName;

        $user = $this->service->create($username, 'top-secret', $faker->email, true, false);

        $this->assertArrayHasKey(UserInterface::ROLE_USER, array_flip($user->getRoles()));
        $this->assertArrayNotHasKey(UserInterface::ROLE_STAFF, array_flip($user->getRoles()));
        $this->assertArrayNotHasKey(UserInterface::ROLE_ADMIN, array_flip($user->getRoles()));
        $this->assertArrayNotHasKey(UserInterface::ROLE_SUPER_ADMIN, array_flip($user->getRoles()));
        $this->assertFalse($user->isSuperAdmin());


        // make sure that ROLE_USER is not removable
        $result = $this->service->removeRole($username, UserInterface::ROLE_USER);
        $this->assertTrue($result);
        $this->assertArrayHasKey(UserInterface::ROLE_USER, array_flip($user->getRoles()));


        // add ROLE_STAFF
        $result = $this->service->addRole($username, UserInterface::ROLE_STAFF);
        $this->assertTrue($result);
        $this->assertArrayHasKey(UserInterface::ROLE_USER, array_flip($user->getRoles()));
        $this->assertArrayHasKey(UserInterface::ROLE_STAFF, array_flip($user->getRoles()));
        $this->assertArrayNotHasKey(UserInterface::ROLE_ADMIN, array_flip($user->getRoles()));
        $this->assertArrayNotHasKey(UserInterface::ROLE_SUPER_ADMIN, array_flip($user->getRoles()));
        $this->assertFalse($user->isSuperAdmin());

        // remove ROLE_STAFF
        $result = $this->service->removeRole($username, UserInterface::ROLE_STAFF);
        $this->assertTrue($result);
        $this->assertArrayHasKey(UserInterface::ROLE_USER, array_flip($user->getRoles()));
        $this->assertArrayNotHasKey(UserInterface::ROLE_STAFF, array_flip($user->getRoles()));
        $this->assertArrayNotHasKey(UserInterface::ROLE_ADMIN, array_flip($user->getRoles()));
        $this->assertArrayNotHasKey(UserInterface::ROLE_SUPER_ADMIN, array_flip($user->getRoles()));
        $this->assertFalse($user->isSuperAdmin());

        // try removing ROLE_STAFF again
        $result = $this->service->removeRole($username, UserInterface::ROLE_STAFF);
        $this->assertFalse($result);
        $this->assertArrayHasKey(UserInterface::ROLE_USER, array_flip($user->getRoles()));
        $this->assertArrayNotHasKey(UserInterface::ROLE_STAFF, array_flip($user->getRoles()));
        $this->assertArrayNotHasKey(UserInterface::ROLE_ADMIN, array_flip($user->getRoles()));
        $this->assertArrayNotHasKey(UserInterface::ROLE_SUPER_ADMIN, array_flip($user->getRoles()));
        $this->assertFalse($user->isSuperAdmin());
    }

    public function testNonExistentUser()
    {
        $faker = Faker\Factory::create();
        $username = $faker->userName;

        $this->setExpectedException('\InvalidArgumentException', sprintf(
            'User identified by "%s" username does not exist.', $username
        ));
        $this->service->changePassword($username, 'top-secret');
    }
}
 