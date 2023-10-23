<?php

namespace Domos\Core\Pages;

interface AdminPage
{
    public function render(): string;
    public function renderAndOutput(): void;
}
