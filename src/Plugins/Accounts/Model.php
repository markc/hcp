<?php

declare(strict_types=1);
// lib/php/plugins/users.php 20150101 - 20200414
// Copyright (C) 2015-2020 Mark Constable <markc@renta.net> (AGPL-3.0)

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

    protected function create(): string
    {
        elog(__METHOD__);

        if (Util::is_adm()) return parent::create();
        Util::log('You are not authorized to perform this action, please contact your administrator.');
        Util::relist();
        return '';
    }

    protected function read(): string
    {
        elog(__METHOD__);

        $usr = Db::read('*', 'id', $this->g->in['i'], '', 'one');
        if (!$usr) {
            Util::log('User not found.');
            Util::relist();
            return '';
        }

        if (Util::is_acl(0)) {
            // superadmin
        } elseif (Util::is_acl(1)) { // normal admin
            if ((int)$_SESSION['usr']['grp'] !== (int)$usr['grp']) {
                Util::log('You are not authorized to perform this action.');
                Util::relist();
                return '';
            }
        } else { // Other users
            if ((int)$_SESSION['usr']['id'] !== (int)$usr['id']) {
                Util::log('You are not authorized to perform this action.');
                Util::relist();
                return '';
            }
        }
        return $this->g->t->read($usr);
    }

    protected function delete(): string
    {
        elog(__METHOD__);

        if (Util::is_post()) {
            parent::delete();
            return '';
        }
        return $this->g->t->delete();
    }

    protected function list(): string
    {
        elog(__METHOD__);

        if ($this->g->in['x'] === 'json') {
            $columns = [
                ['dt' => null, 'db' => 'id'],
                ['dt' => 0, 'db' => 'login', 'formatter' => function ($d, array $row): string {
                    return '<b><a href="?o=Accounts&m=read&i=' . $row['id'] . '&x=html" class="bslink">' . $d . '</a></b>';
                }],
                ['dt' => 1, 'db' => 'fname'],
                ['dt' => 2, 'db' => 'lname'],
                ['dt' => 3, 'db' => 'altemail'],
                ['dt' => 4, 'db' => 'acl', 'formatter' => function ($d): string {
                    return $this->g->acl[is_string($d) ? (int)$d : $d];
                }],
                ['dt' => 5, 'db' => 'grp'],
            ];
            return json_encode(Db::simple($_GET, 'accounts', 'id', $columns));
        }
        return $this->g->t->list($this->in);
    }

    protected function switch_user(): string
    {
        elog(__METHOD__);

        if (Util::is_adm() && !is_null($this->g->in['i'])) {
            $usr = Db::read('id,acl,grp,login,fname,lname,webpw,cookie', 'id', $this->g->in['i'], '', 'one');
            if ($usr) {
                $_SESSION['usr'] = $usr;
                Util::log('Switch to user: ' . $usr['login'], 'success');
            }
        } else {
            Util::log('Not authorized to switch users');
        }
        Util::relist();
        return '';
    }
}
