<?php

namespace Domos\Core\Pages\AdminPage;

class MenuItem
{
//    page_title: 'My Top Level Menu Example',
//   menu_title: 'Top Level Menu',
//   capability: 'manage_options',
//   menu_slug: 'myplugin/myplugin-admin-page.php',
//   callback: 'myplguin_admin_page',
//   icon_url: 'dashicons-tickets',
//   position: 6

    public string $title;
    public string $menu_title;
    public string $capability;
    public string $slug;
    public string $icon;
    public int $position;

    public function __construct(
        string $title,
        string $menu_title,
        string $capability,
        string $slug,
        string $icon,
        int $position
    ) {
        $this->title = $title;
        $this->menu_title = $menu_title;
        $this->capability = $capability;
        $this->slug = $slug;
        $this->icon = $icon;
        $this->position = $position;
    }
}
