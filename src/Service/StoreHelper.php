<?php

namespace App\Service;

use App\Entity\Store;
use Doctrine\Common\Persistence\ObjectManager;

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
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    private $em;

    public function __construct(ObjectManager $manager)
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
        /** @var \App\Entity\Store[] $find */
        $find = $this->em->getRepository(Store::class)->findBy(['url' => $url]);

        if (null !== $find) {
            if (count($find)) {
                return true;
            }
        }

        return false;
    }
}
