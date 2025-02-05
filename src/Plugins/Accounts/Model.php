<?php

declare(strict_types=1);
// Created: 20150101 - Updated: 20250202
// Copyright (C) 2015-2025 Mark Constable <markc@renta.net> (AGPL-3.0)

namespace HCP\Plugins\Accounts;

use HCP\Plugin;
use HCP\Util;
use HCP\Db;

class Model extends Plugin
{
    protected string $tbl = 'accounts';
    protected array $in = [
        'grp'       => 1,
        'acl'       => 2,
        'vhosts'    => 1,
        'login'     => '',
        'fname'     => '',
        'lname'     => '',
        'altemail'  => '',
    ];

    public function create(): array
    {
        Util::elog(__METHOD__);

        if (!Util::is_adm())
        {
            return [
                'status' => 'error',
                'message' => 'You are not authorized to perform this action, please contact your administrator.'
            ];
        }

        return parent::create();
    }

    public function read(): array
    {
        Util::elog(__METHOD__);

        $usr = Db::read('*', 'id', $this->controller->input['i'], '', 'one');
        if (!$usr)
        {
            return [
                'status' => 'error',
                'message' => 'User not found.'
            ];
        }

        if (Util::is_acl(0))
        {
            // superadmin - allow access
            return [
                'status' => 'success',
                'message' => $usr
            ];
        }
        elseif (Util::is_acl(1))
        {
            // normal admin
            if ((int)$_SESSION['usr']['grp'] !== (int)$usr['grp'])
            {
                return [
                    'status' => 'error',
                    'message' => 'You are not authorized to perform this action.'
                ];
            }
        }
        else
        {
            // Other users
            if ((int)$_SESSION['usr']['id'] !== (int)$usr['id'])
            {
                return [
                    'status' => 'error',
                    'message' => 'You are not authorized to perform this action.'
                ];
            }
        }

        return [
            'status' => 'success',
            'message' => $usr
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

        if (Util::is_post())
        {
            parent::delete();
            return [
                'status' => 'success',
                'message' => 'Delete operation completed'
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Confirm deletion'
        ];
    }

    public function list(): array
    {
        Util::elog(__METHOD__);

        if ($this->controller->input['f'] === 'json')
        {
            $columns = [
                ['dt' => null, 'db' => 'id'],
                ['dt' => 0, 'db' => 'login', 'formatter' => function ($d, array $row): string
                {
                    return '<b><a href="?plugin=Accounts&action=read&i=' . $row['id'] . '&x=modal" class="bslink">' . $d . '</a></b>';
                }],
                ['dt' => 1, 'db' => 'fname'],
                ['dt' => 2, 'db' => 'lname'],
                ['dt' => 3, 'db' => 'altemail'],
                ['dt' => 4, 'db' => 'acl', 'formatter' => function ($d): string
                {
                    return is_string($d) ? (int)$d : $d;
                }],
                ['dt' => 5, 'db' => 'grp'],
            ];
            return [
                'status' => 'success',
                'message' => Db::simple($_GET, 'accounts', 'id', $columns)
            ];
        }

        return [
            'status' => 'success',
            'message' => Db::read('*', '', '', 'ORDER BY `updated` DESC')
        ];
    }

    public function switch_user(): array
    {
        Util::elog(__METHOD__);

        if (!Util::is_adm() || is_null($this->controller->input['i']))
        {
            return [
                'status' => 'error',
                'message' => 'Not authorized to switch users'
            ];
        }

        $usr = Db::read('id,acl,grp,login,fname,lname,webpw,cookie', 'id', $this->controller->input['i'], '', 'one');
        if ($usr)
        {
            $_SESSION['usr'] = $usr;
            return [
                'status' => 'success',
                'message' => 'Switch to user: ' . $usr['login']
            ];
        }

        return [
            'status' => 'error',
            'message' => 'User not found'
        ];
    }
}
