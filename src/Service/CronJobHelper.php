<?php

namespace App\Service;

use App\Entity\CronJob;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpKernel\KernelInterface;

class CronJobHelper
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    private $em;

    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface
     */
    private $kernel;

    public function __construct(ObjectManager $manager, KernelInterface $kernel)
    {
        $this->em = $manager;
        $this->kernel = $kernel;
    }

    /**
     * @throws \Exception
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
     * @param \App\Entity\CronJob $job
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
     * @param \App\Entity\CronJob $job
     *
     * @throws \Exception
     */
    public function runJob(CronJob $job)
    {
        $className = '\\App\\Service\\CronJob\\'.$job->getScriptFile();
        if (class_exists($className)) {
            new $className($this->kernel);

            $job->setLastExec(new \DateTime());
            $this->em->flush();
        } else {
            throw new \Exception('[CronJob][Fatal Error]: Could not execute cron job. Could not locate script file ("'.$job->getScriptFile().'")');
        }
    }
}
