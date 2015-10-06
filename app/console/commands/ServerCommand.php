<?php namespace App\Console\Commands;

/**
 * @package Radium Codex
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ServerCommand extends Command
{
    /**
     * Configure the standard framework properties
     */
    protected function configure()
    {
        $this->setName("server:run")
             ->setDescription("Run PHP Server")
             ->setHelp(<<<EOT

Runs 'PHP Server -t www'

EOT
            );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        echo `clear`;
        
        $header_style = new OutputFormatterStyle('green', 'default', ['bold']);
        $output->getFormatter()->setStyle('header', $header_style);

        $output->writeln('<header>Running COGS PHP Server.</header>');
        //$output->writeln('Use `ps -A | grep localhost` to locate sever PID.');
        $output->writeln('php -S http://localhost:8080 -t public');
        $output->writeln('Use ^c to terminate.');
        $output->writeln('--------------------------------------');

        $command = `php -S localhost:8080 -t public`;
        //$command = `php -S localhost:8080 -t public  &> /dev/null &`;
        echo $command;
    }

}
