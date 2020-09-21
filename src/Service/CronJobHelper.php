<?php

namespace App\Service;

use App\Entity\CronJob;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpKernel\KernelInterface;

class CronJobHelper
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var KernelInterface
     */
    private $kernel;

    public function __construct(EntityManagerInterface $manager, KernelInterface $kernel)
    {
        $this->em = $manager;
        $this->kernel = $kernel;
    }

    /**
     * @throws Exception
     */
    public function execute()
    {
        $cronJobs = $this->em->getRepository(CronJob::class)->findBy(['enabled' => true], ['priority' => 'ASC']);

        if (count($cronJobs)) {
            foreach ($cronJobs as $job) {
                if ($this->getNextExec($job) <= time()) {
                    $this->runJob($job);
                }
            }
        }
    }

    /**
     * @param CronJob $job The job
     *
     * @return int
     */
    public function getNextExec(CronJob $job)
    {
        $lastExec = $job->getLastExec()->getTimestamp();
        $execEvery = $job->getExecEvery();

        return $lastExec + $execEvery;
    }

    /**
     * @param CronJob $job The job
     *
     * @throws Exception
     */
    public function runJob(CronJob $job)
    {
        $className = '\\App\\Service\\CronJob\\' . $job->getScriptFile();
        if (class_exists($className)) {
            new $className($this->kernel);

            $job->setLastExec(new \DateTime());
            $this->em->flush();
        } else {
            $message = '[CronJob][Fatal Error]: Could not execute cron job. Could not locate script file ("' .
                $job->getScriptFile() .
                '")';
            throw new Exception($message);
        }
    }
}
