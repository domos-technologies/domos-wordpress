<?php

namespace Domos\Core\Pages\AdminPage;

use Domos\Core\Support\View;

trait OutputsHTML
{
    public function renderAndOutput(): void
    {
        echo $this->render();
    }

    protected function view($view, $data = [])
    {
        return View::render($view, $data);
    }
}
