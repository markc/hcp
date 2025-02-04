<?php

declare(strict_types=1);
// Created: 20150101 - Updated: 20250203
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

readonly class Config
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

    public function __construct(
        string $self = '/',
        string $host = ''
    )
    {
        Util::elog(__METHOD__);

        $this->email = 'admin@example.com';
        $this->admpw = 'admin123';
        $this->file = '../src/.ht_conf.php';
        $this->hash = 'SHA512-CRYPT';
        $this->host = $host;
        $this->perp = 25;
        $this->self = $self;
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
                    ['Accounts',    '?o=Accounts',  'bi bi-people'],
                    ['Vhosts',      '?o=Vhosts',    'bi bi-globe'],
                    ['Mailboxes',   '?o=Vmails',    'bi bi-envelope'],
                    ['Aliases',     '?o=Valias',    'bi bi-envelope-fill'],
                    ['DKIM',        '?o=Dkim',      'bi bi-person-vcard'],
                    ['Domains',     '?o=Domains',   'bi bi-server'],
                ], 'bi bi-gear-fill'],
                ['Stats',       [
                    ['Sys Info',    '?o=InfoSys',   'bi bi-speedometer'],
                    ['Processes',   '?o=Processes', 'bi bi-diagram-2'],
                    ['Mail Info',   '?o=Infomail',  'bi bi-envelope-fill'],
                    ['Mail Graph',  '?o=Mailgraph', 'bi bi-envelope'],
                ], 'bi bi-graph-up'],
            ],
        ];
        $this->nav2 = [
            ['TopNav', '?t=TopNav', 'bi bi-list'],
            ['SideBar', '?t=SideBar', 'bi bi-layout-sidebar']
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
