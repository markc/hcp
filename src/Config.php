<?php

declare(strict_types=1);
// Created: 20250101 - Updated: 20250204
// Copyright (C) 2015-2025 Mark Constable <markc@renta.net> (AGPL-3.0)

namespace HCP;

use HCP\Util;

enum ACL: int
{
    case SuperAdmin = 0;
    case Administrator = 1;
    case User = 2;
    case Suspended = 3;
    case Anonymous = 9;
}

class Config
{
    public string $email;
    public string $admpw;
    public string $file;
    public string $hash;
    public string $host;
    public int $perp;
    public string $self;
    public array $db;
    public array $nav1;
    public array $nav2;
    public array $nav3;
    public array $dns;
    public string $acl;

    public function __construct()
    {
        Util::elog(__METHOD__);

        $this->email = 'admin@example.com';
        $this->admpw = 'admin123';
        $this->file = '../src/.ht_conf.php';
        $this->hash = 'SHA512-CRYPT';
        $this->host = getenv('HOSTNAME') ?: '';
        $this->perp = 25;
        $this->self = str_replace('index.php', '', $_SERVER['PHP_SELF']);
        $this->db = [
            'host' => '127.0.0.1',
            'name' => 'sysadm',
            'pass' => '../src/.ht_pw',
            'path' => '../sysadm/sysadm.db',
            'port' => '3306',
            'sock' => '',
            'type' => 'sqlite',
            'user' => 'sysadm',
        ];
        $this->nav1 = [
            'non' => [
                ['Webmail',     'webmail/',         'bi bi-envelope'],
                ['Phpmyadmin',  'phpmyadmin/',      'bi bi-globe'],
            ],
            'usr' => [
                ['Webmail',     'webmail/',         'bi bi-envelope'],
                ['Phpmyadmin',  'phpmyadmin/',      'bi bi-globe'],
            ],
            'adm' => [
                ['Menu',        [
                    ['Webmail',     'webmail/',     'bi bi-envelope'],
                    ['Phpmyadmin',  'phpmyadmin/',  'bi bi-globe'],
                ], 'bi bi-list'],
                ['Admin',       [
                    ['Accounts',    '?plugin=Accounts',  'bi bi-people'],
                    ['Vhosts',      '?plugin=Vhosts',    'bi bi-globe'],
                    ['Mailboxes',   '?plugin=Vmails',    'bi bi-envelope'],
                    ['Aliases',     '?plugin=Valias',    'bi bi-envelope-fill'],
                    ['DKIM',        '?plugin=Dkim',      'bi bi-person-vcard'],
                    ['Domains',     '?plugin=Domains',   'bi bi-server'],
                ], 'bi bi-gear-fill'],
                ['Stats',       [
                    ['Sys Info',    '?plugin=InfoSys',   'bi bi-speedometer'],
                    ['Processes',   '?plugin=Processes', 'bi bi-diagram-2'],
                    ['Mail Info',   '?plugin=Infomail',  'bi bi-envelope-fill'],
                    ['Mail Graph',  '?plugin=Mailgraph', 'bi bi-envelope'],
                ], 'bi bi-graph-up'],
                ['Example', '?plugin=Example', 'bi bi-globe']
            ],
        ];
        $this->nav2 = [
            ['TopNav', '?theme=TopNav', 'bi bi-list'],
            ['SideBar', '?theme=SideBar', 'bi bi-layout-sidebar']
        ];
        $this->nav3 = [];
        $this->dns = [
            'a' => '127.0.0.1',
            'mx' => '',
            'ns1' => 'ns1.',
            'ns2' => 'ns2.',
            'prio' => 0,
            'ttl' => 300,
            'soa' => [
                'primary' => 'ns1.',
                'email' => 'admin.',
                'refresh' => 7200,
                'retry' => 540,
                'expire' => 604800,
                'ttl' => 3600,
            ],
            'db' => [
                'host' => '127.0.0.1',
                'name' => 'pdns',
                'pass' => '../src/.ht_dns_pw',
                'path' => '../sysadm/pdns.db',
                'port' => '3306',
                'sock' => '',
                'type' => 'sqlite',
                'user' => 'pdns',
            ],
        ];
        $this->acl = ACL::class;
    }
}
