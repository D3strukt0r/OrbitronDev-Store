<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CurrencySubscriber implements EventSubscriberInterface
{
    private $defaultCurrency;

    public function __construct($defaultCurrency = 'USD')
    {
        $this->defaultCurrency = $defaultCurrency;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        // try to see if the currency has been set as a _currency routing parameter or is present as a cookie
        if ($currency = $request->attributes->get('_currency')) {
            $request->getSession()->set('_currency', $currency);
            $request->attributes->set('set_currency_cookie', $currency);
        } elseif ($currency = $request->query->get('_currency')) {
            $request->getSession()->set('_currency', $currency);
            $request->attributes->set('set_currency_cookie', $currency);
        } elseif ($currency = $request->cookies->get('_currency')) {
            $request->getSession()->set('_currency', $currency);
        } else {
            // if no explicit currency has been set on this request, use one from the cookie
            $request->getSession()->set('_currency', $this->defaultCurrency);
            $request->attributes->set('set_currency_cookie', $this->defaultCurrency);
        }
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        $response = $event->getResponse();
        $request = $event->getRequest();

        if ($currency = $request->attributes->get('set_currency_cookie')) {
            $response->headers->setCookie(new Cookie('_currency', $currency, (new \DateTime())->modify('+1 year')));
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 0]],
            KernelEvents::RESPONSE => [['onKernelResponse', 0]],
        ];
    }
}
