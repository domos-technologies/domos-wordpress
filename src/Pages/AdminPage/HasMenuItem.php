<?php

namespace Domos\Core\Pages\AdminPage;

interface HasMenuItem
{
    public function getMenuItem(): MenuItem;
}
