<?php

namespace Esenio\TestingBundle\UnitTesting;

require_once(dirname(__FILE__) . '/../../../../app/AppKernel.php');

use Doctrine\ORM\EntityManager;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * Base class for unit tests.
 * NOTE: While tests do use container (and thus are coupled with it) in no case should
 * we pass container directly to tested objects i.e. tested modules should be based on some
 * specific objects/classes, not on the "entire world" (which happens if you require whole IoC
 * container injection)
 *
 * @package Esenio\TestingBundle\UnitTesting
 */
class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \AppKernel
     */
    protected $kernel;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var string
     */
    protected $environment = 'test';

    /**
     * @var bool
     */
    protected $debug = true;

    protected $appKernelBooted = false;

    /**
     * Boots AppKernel, should be called from w/i test cases that rely on it.
     * Call it w/i setUp() method, to make whole test booted.
     */
    public function boot()
    {
        $this->kernel = new \AppKernel($this->environment, $this->debug);
        $this->kernel->boot();

        $this->container = $this->kernel->getContainer();
        $this->em = $this->container->get('doctrine')->getManager();

        $this->appKernelBooted = true;
    }

    /**
     * @return null
     */
    public function tearDown()
    {
        parent::tearDown();
        if ($this->appKernelBooted) {
            $this->kernel->shutdown();
        }
    }

    /**
     * Executes fixtures
     * @param Loader $loader
     */
    protected function executeFixtures(Loader $loader)
    {
        $purger = new ORMPurger();
        $executor = new ORMExecutor($this->em, $purger);
        // manually inject controller into fixtures
        $fixtures =  $loader->getFixtures();

        foreach ($fixtures as $fixture) {
            if ($fixture instanceof ContainerAwareInterface) {
                $fixture->setContainer($this->container);
            }
        }
        $executor->execute($fixtures);
    }

    /**
     * Load and execute fixtures from a directory
     *
     * @param string $directory
     */
    protected function loadFixturesFromDirectory($directory)
    {
        $loader = new Loader();
        $loader->loadFromDirectory($directory);
        $this->executeFixtures($loader);
    }

    /**
     * Load fixtures from default directory
     */
    protected function loadFixtures()
    {
        $bt =  debug_backtrace();
        $testDirectory = dirname($bt[0]['file']);
        $endPos = stripos($testDirectory, 'Tests');

        $fixtureDirectory = $testDirectory;
        if ($endPos !== false) {
            $fixtureDirectory =  substr($testDirectory, 0, $endPos + strlen('Tests'));
        }

        $this->loadFixturesFromDirectory($fixtureDirectory . '/DataFixtures');
    }
}
