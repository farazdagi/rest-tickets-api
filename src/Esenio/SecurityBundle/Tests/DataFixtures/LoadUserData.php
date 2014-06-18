<?php

namespace Esenio\SecurityBundle\Tests\DataFixtures;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker;

use Esenio\TestingBundle\UnitTesting\TestFixture;
use Esenio\SecurityBundle\Entity\User;
use Esenio\SecurityBundle\Model\UserManagerInterface;

class LoadUserData extends TestFixture
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        parent::load($manager);

        $faker = Faker\Factory::create();

        /** @var UserManagerInterface $userManager */
        $userManager = $this->container->get('esenio_security.user_manager');

        // random users
        for ($i = 0; $i < 10; $i++) {
            $user = new User();

            $user->setUsername($faker->userName);
            $user->setEmail($faker->email);
            $user->setPlainPassword($faker->userName);
            $userManager->encodePassword($user);

            $manager->persist($user);
        }
        $manager->flush();
    }
}