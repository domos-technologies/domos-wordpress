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
        return view('admin/page', [
			'url' => DOMOS::instance()->url(),
	        'token' => null
        ])->render();
    }

    public function getMenuItem(): MenuItem
    {
        return new MenuItem(
            'immocore',
            'immocore',
            'manage_options',
            'immocore',
            'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iOTE2IiBoZWlnaHQ9IjkxNiIgdmlld0JveD0iMCAwIDkxNiA5MTYiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxtYXNrIGlkPSJtYXNrMF8xMDFfMzM5IiBzdHlsZT0ibWFzay10eXBlOmx1bWluYW5jZSIgbWFza1VuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeD0iODIiIHk9IjE4NCIgd2lkdGg9IjQ1MSIgaGVpZ2h0PSI1NDgiPgo8cGF0aCBkPSJNNTMyLjg0OSAxODRIODIuNTg0NVY3MzEuNDIxSDUzMi44NDlWMTg0WiIgZmlsbD0id2hpdGUiLz4KPC9tYXNrPgo8ZyBtYXNrPSJ1cmwoI21hc2swXzEwMV8zMzkpIj4KPHBhdGggZD0iTTI5Mi4yMDYgMTg0SDgyLjk2NzVMMzI3LjA1NyA0NTcuNzExTDgyLjk2NzUgNzMxLjQyMUgyOTIuMjA2TDUzNi4xNjggNDU3LjcxMUwyOTIuMjA2IDE4NFoiIGZpbGw9IiMyMzNERkYiLz4KPC9nPgo8bWFzayBpZD0ibWFzazFfMTAxXzMzOSIgc3R5bGU9Im1hc2stdHlwZTpsdW1pbmFuY2UiIG1hc2tVbml0cz0idXNlclNwYWNlT25Vc2UiIHg9IjM4MyIgeT0iMTg0IiB3aWR0aD0iNDUyIiBoZWlnaHQ9IjU0OCI+CjxwYXRoIGQ9Ik04MzQuMDAzIDE4NEgzODMuNzM5VjczMS40MjFIODM0LjAwM1YxODRaIiBmaWxsPSJ3aGl0ZSIvPgo8L21hc2s+CjxnIG1hc2s9InVybCgjbWFzazFfMTAxXzMzOSkiPgo8cGF0aCBkPSJNNTkzLjM2MSAxODRIMzg0LjEyM0w2MjguMjEyIDQ1Ny43MTFMMzg0LjEyMyA3MzEuNDIxSDU5My4zNjFMODM3LjMyMyA0NTcuNzExTDU5My4zNjEgMTg0WiIgZmlsbD0iIzIzM0RGRiIvPgo8L2c+Cjwvc3ZnPgo=',
            100
        );
    }
}
