<?php

namespace App\Controller\Panel;

use App\Entity\Store;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    public static function __setupNavigation()
    {
        return [
            'type' => 'link',
            'parent' => 'root',
            'id' => 'home',
            'title' => 'Dashboard',
            'href' => 'home',
            'icon' => 'hs-admin-panel',
            'view' => 'HomeController::home',
        ];
    }

    public static function __callNumber()
    {
        return 1;
    }

    public function home(Store $store, $navigation)
    {
        return $this->render(
            'theme_admin1/home.html.twig',
            [
                'navigation_links' => $navigation,
                'current_store' => $store,
            ]
        );
    }
}
