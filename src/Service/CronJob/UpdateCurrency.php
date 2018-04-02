<?php

namespace App\Service\CronJob;

use Symfony\Component\HttpKernel\KernelInterface;

class UpdateCurrency
{
    public function __construct(KernelInterface $kernel)
    {
        $kernel->getContainer()->get('helper.ecbcurrencyconverter')->update();
    }
}
