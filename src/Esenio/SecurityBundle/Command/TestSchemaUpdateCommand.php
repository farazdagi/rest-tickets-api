<?php

namespace Esenio\SecurityBundle\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TestSchemaUpdateCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('esenio:security:test-schema-update')
            ->setDescription('Updates/creates schema for unit tests.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cmd = 'php app/console doctrine:schema:update --force --env=test';
        $output->writeln('Command: ' . $cmd);

        $output->writeln(shell_exec($cmd));
    }
}