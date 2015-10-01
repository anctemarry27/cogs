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
            //->setDefinition([
            //    new InputOption('start', 's', InputOption::VALUE_OPTIONAL, 'Start number of the range of Fibonacci number', $start),
            //    new InputOption('stop', 'e', InputOption::VALUE_OPTIONAL, 'stop number of the range of Fibonacci number', $stop),
            //])
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
        $header_style = new OutputFormatterStyle('white', 'green', ['bold']);
        $output->getFormatter()->setStyle('header', $header_style);

        //$start = intval($input->getOption('start'));
        //$stop  = intval($input->getOption('stop'));

        $output->writeln('<header>Running Server</header>');
        $output->writeln('Use `ps -A | grep localhost` to locate sever PID.');

        $command = `php -S localhost:8080 -t public  &> /dev/null &`;
        echo $command;
    }

}
