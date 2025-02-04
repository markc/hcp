<?php

declare(strict_types=1);
// lib/php/plugins/home.php 20150101 - 20250128
// Copyright (C) 2015-2025 Mark Constable <markc@renta.net> (AGPL-3.0)

namespace HCP\Plugins\Home;

use HCP\Plugin;
use HCP\Util;

class Model extends Plugin
{
    public function list(): array
    {
        Util::elog(__METHOD__);

        $tpl_path = __DIR__ . '/Home.tpl';
        $tpl_buf = $tpl_path . ' does not exist';

        if (file_exists($tpl_path))
        {
            ob_start();
            include $tpl_path;
            $tpl_buf = ob_get_clean();
        }

        return [
            'status' => 'success',
            'message' => $tpl_buf
        ];
    }
}
