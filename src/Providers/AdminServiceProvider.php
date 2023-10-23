<?php

namespace Domos\Core\Providers;

use Domos\Core\Pages\AdminPage\HasMenuItem;
use Domos\Core\Pages\MainSettings;

class AdminServiceProvider implements Provider
{
    public function register()
    {
        $pages = [
            new MainSettings
        ];

        add_action('admin_menu', function () use ($pages) {
            foreach ($pages as $page) {
                if ($page instanceof HasMenuItem) {
                    $menu = $page->getMenuItem();

                    add_menu_page(
                       $menu->title,
                       $menu->menu_title,
                       $menu->capability,
                       $menu->slug,
                       [$page, 'renderAndOutput'],
                       $menu->icon,
                       $menu->position
                   );
                }
            }

        });
    }

    public function boot()
    {
    }
}
