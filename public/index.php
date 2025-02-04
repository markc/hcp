<?php

declare(strict_types=1);
// Created: 20150101 - Updated: 20250205
// Copyright (C) 2015-2025 Mark Constable <markc@renta.net> (AGPL-3.0)

namespace HCP;

require_once __DIR__ . '/../vendor/autoload.php';

echo new Init(new Config());
