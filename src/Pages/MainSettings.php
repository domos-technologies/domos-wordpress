<?php

namespace Domos\Core\Pages;

use Domos\Core\DOMOS;
use Domos\Core\Pages\AdminPage\HasMenuItem;
use Domos\Core\Pages\AdminPage\MenuItem;
use Domos\Core\Pages\AdminPage\OutputsHTML;
use Domos\Core\Sync\SyncManager;
use function Roots\bundle;

class MainSettings implements AdminPage, HasMenuItem
{
    use OutputsHTML;

    public function render(): string
    {
        $domos = DOMOS::instance();

        return view('admin/page', [
			'url' => $domos->url(),
	        'token' => $domos->options->token->get(),
			'languages' => $domos->options->languages->get(),
			'default_language' => $domos->options->default_language->get(),
        ])->render();
    }

    public function getMenuItem(): MenuItem
    {
        return new MenuItem(
            'immocore',
            'immocore',
            'manage_options',
            'immocore',
            'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAyNCIgaGVpZ2h0PSIxMDI0IiB2aWV3Qm94PSIwIDAgMTAyNCAxMDI0IiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPgo8cGF0aCBkPSJNMjQxLjA3OSA2NDAuNTQ1TDI0NC40MDEgMzYyLjRDMjQ0LjQwMSAzNTMuNTM1IDI0OC44MjkgMzQzLjU2MiAyNTYuNTc5IDMzOS4xMjlMNDg3Ljk2OSAyMDcuMjZWMjIuMkw5Mi43MjM3IDI0OS4zN0M4NC45NzM4IDI1My44MDIgODAuNTQ1MiAyNjIuNjY3IDgwLjU0NTIgMjcxLjUzM1Y3MzIuNTIxQzExMi42NTIgNzEzLjY4MiAyMDYuNzU4IDY1OS4zODMgMjQxLjA3OSA2MzkuNDM3VjY0MC41NDVaIiBmaWxsPSJ3aGl0ZSIvPgo8cGF0aCBkPSJNMjk4LjY1IDYyOS40NjNMNDg2Ljg2MiA3MzkuMTdWNTIwLjg2NUwyOTguNjUgNDEyLjI2N1Y2MjkuNDYzWiIgZmlsbD0id2hpdGUiLz4KPHBhdGggZD0iTTcyNi4wMDIgNjI5LjQ2M1Y0MTIuMjY3TDUzNi42ODMgNTIwLjg2NVY3MzkuMTdMNzI2LjAwMiA2MjkuNDYzWiIgZmlsbD0id2hpdGUiLz4KPHBhdGggZD0iTTMyMy4wMDcgMzY3Ljk0MUw1MTIuMzI2IDQ3Ny42NDhMNzAwLjUzOCAzNjcuOTQxTDUxMi4zMjYgMjU5LjM0M0wzMjMuMDA3IDM2Ny45NDFaIiBmaWxsPSJ3aGl0ZSIvPgo8cGF0aCBkPSJNMjY1LjQzNiA2ODMuNzYyTDE2MC4yNTkgNzQ0LjcxTDExMC40MzggNzczLjUyMkw0OTkuMDQxIDk5OC40NzVDNTA2Ljc5MSAxMDAyLjkxIDUxNi43NTUgMTAwMi45MSA1MjQuNTA1IDk5OC40NzVMOTEzLjEwOCA3NzMuNTIyQzg4MS4wMDEgNzU0LjY4NCA3OTIuNDMgNzAzLjcwOSA3NTguMTA5IDY4My43NjJDNzY1Ljg1OSA2ODguMTk1IDc1OS4yMTYgNjg0Ljg3IDc1OC4xMDkgNjgzLjc2Mkw1MzMuMzYyIDgxNC41MjNDNTI1LjYxMiA4MTguOTU2IDUwMS4yNTUgODE4Ljk1NiA0OTMuNTA1IDgxNC41MjMiIGZpbGw9IndoaXRlIi8+CjxwYXRoIGQ9Ik05MzAuODIyIDI0OS4zN0w1MzYuNjgzIDIyLjJWMjA1LjA0NEw3NjUuODU5IDMzOS4xMjlDNzczLjYwOSAzNDMuNTYyIDc4OC4wMDIgMzU5LjA3NiA3ODguMDAyIDM2Ny45NDFMNzkwLjIxNiA2NDMuODY5QzgyNC41MzcgNjYzLjgxNiA5MTAuODkzIDcxMy42ODIgOTQzIDczMi41MjFWMjcxLjUzM0M5NDMgMjYyLjY2NyA5MzguNTcxIDI1My44MDIgOTMwLjgyMiAyNDkuMzdaIiBmaWxsPSJ3aGl0ZSIvPgo8L3N2Zz4K',
            100
        );
    }
}
