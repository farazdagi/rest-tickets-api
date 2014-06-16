<?php

namespace Esenio\SecurityBundle\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UnitTestCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('esenio:security:unit-test')
            ->setDescription('Runs specified UT within SecurityBundle')
            ->addArgument('test', InputArgument::OPTIONAL, 'Specify test to run', 'src/Esenio/SecurityBundle')
            ->addArgument('filter', InputArgument::OPTIONAL, 'Additionally filter by test case', '')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $test = $input->getArgument('test');
        $filter = $input->getArgument('filter');
        $cmd = '';

        if ($filter) {
            $cmd .= ' --filter ' . $filter;
        }

        if ($test) {
            $cmd .= ' ' . $test;
        }

        $cmd = sprintf('fswatch src "clear && bin/phpunit.phar -c app%s"',  $cmd);
        $output->writeln('Command: ' . $cmd);

        \passthru($cmd, $result);
    }
}