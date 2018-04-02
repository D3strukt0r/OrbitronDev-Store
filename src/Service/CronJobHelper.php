<?php

namespace App\Service;

use App\Entity\CronJob;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpKernel\KernelInterface;

class CronJobHelper
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager $em
     */
    private $em;

    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface $kernel
     */
    private $kernel;

    public function __construct(ObjectManager $manager, KernelInterface $kernel)
    {
        $this->em = $manager;
        $this->kernel = $kernel;
    }

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
     * @return integer
     */
    public function getNextExec(CronJob $job)
    {
        if ($job) {
            $lastExec = $job->getLastExec()->getTimestamp();
            $execEvery = $job->getExecEvery();

            return $lastExec + $execEvery;
        }

        return -1;
    }

    /**
     * @param \App\Entity\CronJob $job
     *
     * @throws \Exception
     */
    public function runJob(CronJob $job)
    {
        if ($job) {
            $className = '\\App\\Service\\CronJob\\'.$job->getScriptFile();
            if (class_exists($className)) {
                new $className($this->kernel);

                $job->setLastExec(new \DateTime());
                $this->em->flush();
            } else {
                throw new \Exception('[CronJob][Fatal Error]: Could not execute cron job. Could not locate script file ("'.$fileDir.'")');
            }
        }
    }
}
