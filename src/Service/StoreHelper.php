<?php

namespace App\Service;

use App\Entity\Store;
use Doctrine\ORM\EntityManagerInterface;

class StoreHelper
{
    public static $settings = [
        'store' => [
            'name' => [
                'min_length' => 4,
            ],
            'url' => [
                'min_length' => 3,
            ],
        ],
    ];

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->em = $manager;
    }

    /**
     * Checks whether the given url exists, in other words, if the store exists.
     *
     * @param string $url
     *
     * @return bool
     */
    public function urlExists($url)
    {
        /** @var Store[] $find */
        $find = $this->em->getRepository(Store::class)->findBy(['url' => $url]);

        if (count($find)) {
            return true;
        }

        return false;
    }
}
