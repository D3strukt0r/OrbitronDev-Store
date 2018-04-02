<?php

namespace App\EventSubscriber;

use App\Service\CronJobHelper;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RunCronJobSubscriber implements EventSubscriberInterface
{
    private $cronJobHelper;
    private $logger;

    public function __construct(CronJobHelper $cronJobHelper, LoggerInterface $logger)
    {
        $this->cronJobHelper = $cronJobHelper;
        $this->logger = $logger;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            // don't do anything if it's not the master request
            return;
        }

        $this->logger->info('Running cron jobs...');
        $this->cronJobHelper->execute();
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 0]],
        ];
    }
}
