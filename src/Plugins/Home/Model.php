<?php

declare(strict_types=1);
// lib/php/plugins/home.php 20150101 - 20250128
// Copyright (C) 2015-2025 Mark Constable <markc@renta.net> (AGPL-3.0)

namespace HCP\Plugins\Home;

use HCP\Plugin;

class Model extends Plugin
{
    public function list(): string
    {
        elog(__METHOD__);

        if (file_exists('Home.tpl'))
        {
            ob_start();
            include 'Home.tpl';
            return ob_get_clean();
        }

        return $this->t->list([]);
    }
}
