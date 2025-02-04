<?php

declare(strict_types=1);
// Created: 20150101 - Updated: 20250203
// Copyright (C) 2015-2025 Mark Constable <markc@renta.net> (AGPL-3.0)

namespace HCP;

const DBG = true;

require_once __DIR__ . '/../vendor/autoload.php';

// Initialize with correct self path and hostname
$self = str_replace('index.php', '', $_SERVER['PHP_SELF']);
$host = getenv('HOSTNAME') ?: '';
echo new Init(new Config($self, $host));

function dbg(mixed $var = null): void
{
    error_log(var_export($var, true));
}
