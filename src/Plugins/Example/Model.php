<?php

declare(strict_types=1);
// Created: 20250201 - Updated: 20250204
// Copyright (C) 2015-2025 Mark Constable <markc@renta.net> (AGPL-3.0)

namespace HCP\Plugins\Example;

use HCP\Plugin;
use HCP\Util;

class Model extends Plugin
{
    public function create(): array
    {
        Util::elog(__METHOD__);
        // Add your create logic here
        return [
            'status' => 'success',
            'message' => 'Create operation'
        ];
    }

    public function read(): array
    {
        Util::elog(__METHOD__);
        // Add your read logic here
        return [
            'status' => 'success',
            'message' => 'Read operation'
        ];
    }

    public function update(): array
    {
        Util::elog(__METHOD__);
        // Add your update logic here
        return [
            'status' => 'success',
            'message' => 'Update operation'
        ];
    }

    public function delete(): array
    {
        Util::elog(__METHOD__);
        // Add your delete logic here
        return [
            'status' => 'success',
            'message' => 'Delete operation'
        ];
    }

    public function list(): array
    {
        Util::elog(__METHOD__);
        // Add your list logic here
        return [
            'status' => 'success',
            'message' => 'List operation'
        ];
    }
}
