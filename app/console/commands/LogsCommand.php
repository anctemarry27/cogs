<?php namespace App\Console\Commands;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */
class LogsCommand extends Command
{
    /**
     * Configure the standard framework properties
     */
    protected function configure()
    {
        $local_logs = LOCAL_LOGS . '*';
        $this->setName("logs:clear")
             ->setDescription("Clear local logs.")
             ->setHelp(<<<EOT

Clears the logs in:
    $local_logs

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
        $header_style = new OutputFormatterStyle('green', 'default', ['bold']);
        $output->getFormatter()->setStyle('header', $header_style);

        # check for files
        $cache = glob(LOCAL_LOGS . '*');

        if (empty($cache))
        {
            $output->writeln('<header>No logs to clear.</header>');
        }
        else
        {
            $output->writeln('<header>Clearing local logs...</header>');
            $local_logs = LOCAL_LOGS . '*';
            $command = `rm -R $local_logs`;

            echo $command;
            $output->writeln('<header>Local logs have been cleared.</header>');
            dlog('cleared the local logs.', 'info');
        }
    }
}
