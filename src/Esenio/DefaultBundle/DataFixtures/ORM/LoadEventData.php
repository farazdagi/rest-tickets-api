<?php

namespace Esenio\DefaultBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Esenio\SecurityBundle\Model\UserManagerInterface;
use Esenio\SecurityBundle\Model\UserInterface;
use Esenio\DefaultBundle\Entity\Venue;
use Esenio\DefaultBundle\Entity\Event;
use Esenio\DefaultBundle\Entity\Talk;
use Faker;


class LoadEventData extends AbstractFixture implements FixtureInterface, OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ObjectManager
     */
    private $em;

    /**
     * @var Venue[]
     */
    private $venues;

    /**
     * @var UserInterface[]
     */
    private $users;

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->em = $manager;
        $faker = Faker\Factory::create();


        $techs = array('PHP', 'Go', 'C++', 'Python', 'HTML', 'JavaScript', 'Java');
        $types = array('Conference', 'Meetup', 'Hackaton', 'Orgy', 'Sleepless Night', 'Event', 'Barcamp', 'Unconf', 'Conf');
        $periods = array('', 'Annual ', 'Monthly ', 'Weekly ', 'Ultimate ', 'The only ', 'Poor Man\'s ');

        for ($i = 0; $i < 20; $i++) {
            $event = new Event();
            $venue = $this->getRandomVenue();

            shuffle($techs);
            shuffle($types);
            shuffle($periods);

            $starts = $faker->dateTimeThisYear;
            $ends = clone $starts;
            $ends->add(new \DateInterval('P' . rand(1, 7) . 'D'));

            $event->setName(sprintf('%s%s %s %d', $periods[0], $techs[0], $types[0], $faker->year));
            $event->setStartDate($starts);
            $event->setEndDate($ends);
            $event->setVenue($venue);

            // attach attendees
            foreach ($this->getRandomUsers(rand(35, 120)) as $user) {
                $event->addAttendee($user);
            }

            $venue->addEvent($event);

            $manager->persist($event);
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
        return 3;
    }

    protected function getRandomVenue()
    {
        if (!$this->venues) {
            foreach ($this->em->getRepository('EsenioDefaultBundle:Venue')->findAll() as $venue) {
                $this->venues[] = $venue;
            }
        }
        return $this->venues[rand(0, count($this->venues) - 1)]; // allow duplicates
    }

    protected function getRandomUsers($limit)
    {
        if (!$this->users) {
            foreach ($this->em->getRepository('EsenioSecurityBundle:User')->findAll() as $user) {
                $this->users[] = $user;
            }
        }
        shuffle($this->users);
        return array_slice($this->users, 0, $limit);
    }
}