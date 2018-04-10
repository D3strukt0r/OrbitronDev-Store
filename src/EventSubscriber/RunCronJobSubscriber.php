<?php

namespace App\EventSubscriber;

use App\Service\CronJobHelper;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RunCronJobSubscriber implements EventSubscriberInterface
{
    private $cronJobHelper;
    private $logger;
    private $em;

    public function __construct(CronJobHelper $cronJobHelper, LoggerInterface $logger, EntityManagerInterface $em)
    {
        $this->cronJobHelper = $cronJobHelper;
        $this->logger = $logger;
        $this->em = $em;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            // don't do anything if it's not the master request
            return;
        }

        $schemaManager = $this->em->getConnection()->getSchemaManager();
        if (true === $schemaManager->tablesExist(['app_cronjob'])) {
            $this->logger->info('Running cron jobs...');
            $this->cronJobHelper->execute();
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 0]],
        ];
    }
}
