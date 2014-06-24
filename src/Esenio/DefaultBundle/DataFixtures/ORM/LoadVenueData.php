<?php

namespace Esenio\DefaultBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Esenio\SecurityBundle\Model\UserManagerInterface;
use Esenio\DefaultBundle\Entity\Venue;
use Faker;


class LoadVenueData extends AbstractFixture implements FixtureInterface, OrderedFixtureInterface, ContainerAwareInterface
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

        $types = array('Arena', 'Conference Hall', 'Lair', 'Cave', 'Business Center');
        for ($i = 0; $i < 10; $i++) {
            $venue = new Venue();
            shuffle($types);
            $venue->setName(sprintf('%s %s', $faker->city, $types[0]));
            $venue->setAddress($faker->address);

            $manager->persist($venue);
            $manager->flush();
        }
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
        return 2;
    }
}