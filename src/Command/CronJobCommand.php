<?php

namespace App\Command;

use App\Service\CronJobHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CronJobCommand extends Command
{
    private $cronJobHelper;

    public function __construct(CronJobHelper $cronJobHelper)
    {
        $this->cronJobHelper = $cronJobHelper;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:cron:run')
            ->setDescription('Runs cron jobs')
            ->setHelp('This command executes all cron job script who need to update')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Checking jobs...');
        $this->cronJobHelper->execute();
        $output->writeln('Jobs (eventually) executed!');
    }
}
