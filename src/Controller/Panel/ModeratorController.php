<?php

namespace App\Controller\Panel;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ModeratorController extends Controller
{
    public static function __setupNavigation()
    {
        return [
            [
                'type'   => 'group',
                'parent' => 'root',
                'id'     => 'moderator',
                'title'  => 'Moderator',
                'icon'   => 'hs-admin-brush-alt',
            ],
            [
                'type'   => 'link',
                'parent' => 'moderator',
                'id'     => 'advertisement',
                'title'  => 'Advertisement',
                'href'   => 'advertisement',
                'view'   => 'ModeratorController::advertisement',
            ],
            [
                'type'   => 'link',
                'parent' => 'moderator',
                'id'     => 'mod_tools',
                'title'  => 'Mod tools',
                'href'   => 'mod-tools',
                'view'   => 'ModeratorController::modTools',
            ],
        ];
    }

    public static function __callNumber()
    {
        return 10;
    }

    public function advertisement($navigation, $store)
    {
        return $response = $this->forward('App\\Controller\\Panel\\DefaultController::notFound', [
            'navigation' => $navigation,
            'store'      => $store,
        ]);
    }

    public function modTools($navigation, $store)
    {
        return $response = $this->forward('App\\Controller\\Panel\\DefaultController::notFound', [
            'navigation' => $navigation,
            'store'      => $store,
        ]);
    }
}
