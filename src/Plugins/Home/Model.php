<?php

declare(strict_types=1);
// lib/php/plugins/home.php 20150101 - 20250128
// Copyright (C) 2015-2025 Mark Constable <markc@renta.net> (AGPL-3.0)

namespace HCP\Plugins\Home;

use HCP\Plugin;
use HCP\Util;
use HCP\Init;
use HCP\Theme;

class Model extends Plugin
{
    protected Init $init;
    protected Theme $theme;

    public function __construct(Theme $theme, Init $init)
    {
        Util::elog(__METHOD__);

        \HCP\dbg($theme);

        parent::__construct($theme, $init);
    }

    public function list(): string
    {
        Util::elog(__METHOD__);

        $tpl_path = __DIR__ . '/Home.tpl';

        if (file_exists($tpl_path))
        {
            ob_start();
            include $tpl_path;
            return ob_get_clean();
        }

        return $this->theme->list([]);
    }
}
