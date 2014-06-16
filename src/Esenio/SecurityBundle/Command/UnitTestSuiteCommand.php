<?php

namespace Esenio\SecurityBundle\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UnitTestSuiteCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('esenio:security:unit-test-suite')
            ->setDescription('Runs specified UT suite within SecurityBundle')
            ->addArgument('suite', InputArgument::OPTIONAL, 'Specify UT Suite to run', 'Default')
            ->addArgument('filter', InputArgument::OPTIONAL, 'Additionally filter by test case', '')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $suite = $input->getArgument('suite');
        $filter = $input->getArgument('filter');

        $cmd = '';

        if ($filter) {
            $cmd .= ' --filter ' . $filter;
        }

        if ($suite) {
            $cmd .= ' --testsuite ' . $suite;
        }

        $cmd = sprintf('fswatch src "clear && bin/phpunit.phar -c app%s"',  $cmd);
        $output->writeln('Command: ' . $cmd);

        \passthru($cmd, $result);
    }
}