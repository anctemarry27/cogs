<?php namespace App\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */
class CacheCommand extends Command
{
    /**
     * Configure the standard framework properties
     */
    protected function configure()
    {
        $local_cache = LOCAL_CACHE . '*';
        $this->setName("cache:clear")
             ->setDescription("Clear the local and view caches.")
             ->setHelp(<<<EOT

Clears the local cache at:
    $local_cache

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
        $cache = glob(LOCAL_CACHE . '/*');

        if (empty($cache))
        {
            $output->writeln('<header>The cache is empty.</header>');
        }
        else
        {
            $output->writeln('<header>Clearing local caches...</header>');
            $local_cache = LOCAL_CACHE . '/*';
            $command = `rm -R $local_cache`;

            echo $command;
            $output->writeln('<header>Local cache has been cleared.</header>');
            dlog('cleared the local cache', 'info');
        }
    }
}
