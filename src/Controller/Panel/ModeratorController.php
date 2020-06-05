<?php

namespace App\Controller\Panel;

use App\Entity\Store;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ModeratorController extends AbstractController
{
    public static function __setupNavigation()
    {
        return [
            [
                'type' => 'group',
                'parent' => 'root',
                'id' => 'moderator',
                'title' => 'Moderator',
                'icon' => 'hs-admin-brush-alt',
            ],
            [
                'type' => 'link',
                'parent' => 'moderator',
                'id' => 'advertisement',
                'title' => 'Advertisement',
                'href' => 'advertisement',
                'view' => 'ModeratorController::advertisement',
            ],
            [
                'type' => 'link',
                'parent' => 'moderator',
                'id' => 'mod_tools',
                'title' => 'Mod tools',
                'href' => 'mod-tools',
                'view' => 'ModeratorController::modTools',
            ],
        ];
    }

    public static function __callNumber()
    {
        return 10;
    }

    public function advertisement(Store $store, $navigation)
    {
        return $this->forward(
            'App\\Controller\\Panel\\DefaultController::notFound',
            [
                'navigation' => $navigation,
                'store' => $store,
            ]
        );
    }

    public function modTools(Store $store, $navigation)
    {
        return $this->forward(
            'App\\Controller\\Panel\\DefaultController::notFound',
            [
                'navigation' => $navigation,
                'store' => $store,
            ]
        );
    }
}
