<?php

declare(strict_types=1);
// Created: 20170225 - Updated: 20250202
// Copyright (C) 2015-2025 Mark Constable <markc@renta.net> (AGPL-3.0)

namespace HCP\Plugins\Processes;

use HCP\Plugin;
use HCP\Util;

final class Model extends Plugin
{
    public function list(): array
    {
        Util::elog(__METHOD__);

        $cmd = "ps -eo rss:10,vsz:10,%cpu:5,cmd --sort=-%cpu,-rss | grep -v \"^\s\+0\"";
        return [
            'status' => 'success',
            'message' => shell_exec($cmd)
        ];
    }
}
