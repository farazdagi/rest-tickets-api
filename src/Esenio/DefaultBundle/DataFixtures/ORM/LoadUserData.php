<?php

namespace Esenio\DefaultBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Esenio\SecurityBundle\Model\UserManagerInterface;
use Faker;


class LoadUserData extends AbstractFixture implements FixtureInterface, OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $faker = Faker\Factory::create();

        /** @var UserManagerInterface $um */
        $um = $this->container->get('esenio_security.user_manager');
        for ($i = 0; $i < 200; $i++) {
            $user = $um->createUser();
            $user->setEmail($faker->email);
            $user->setUsername($user->getEmail());
            $user->setFname($faker->firstName);
            $user->setLname($faker->lastName);
            $user->setPlainPassword($faker->uuid);
            $um
                ->populateUser($user)
                ->saveUser($user);
        }

        // test user
        $user = $um->createUser();
        $user->setEmail('victor@esen.io');
        $user->setUsername($user->getEmail());
        $user->setFname('Victor');
        $user->setLname('Farazdagi');
        $user->setPlainPassword('asdfasdf');
        $um
            ->populateUser($user)
            ->saveUser($user);
    }

    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 1;
    }
}