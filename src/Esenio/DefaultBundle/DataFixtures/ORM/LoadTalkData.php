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
use Esenio\DefaultBundle\Entity\Event;
use Esenio\DefaultBundle\Entity\Talk;
use Faker;


class LoadTalkData extends AbstractFixture implements FixtureInterface, OrderedFixtureInterface, ContainerAwareInterface
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
     * @var Event[]
     */
    private $events;

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

        $totalTalksCnt = rand(5, 20);
        for ($i = 0; $i < $totalTalksCnt; $i++) {
            $talk = new Talk();
            $talk->setTitle($faker->text(50));
            $talk->setAbstract($faker->text(300));
            $talk->setSpeaker($this->getRandomUser());
            $talk->setEvent($this->getRandomEvent());

            $manager->persist($talk);
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
        return 4;
    }

    protected function getRandomEvent()
    {
        if (!$this->events) {
            foreach ($this->em->getRepository('EsenioDefaultBundle:Event')->findAll() as $event) {
                $this->events[] = $event;
            }
        }
        return $this->events[rand(0, count($this->events) - 1)]; // allow duplicates
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

    protected function getRandomUser()
    {
        if (!$this->users) {
            foreach ($this->em->getRepository('EsenioSecurityBundle:User')->findAll() as $user) {
                $this->users[] = $user;
            }
        }
        return $this->users[rand(0, count($this->users) - 1)]; // allow duplicates
    }
}