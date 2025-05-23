<?php

declare(strict_types=1);
// Created: 20150101 - Updated: 20250419
// Copyright (C) 2015-2025 Mark Constable <markc@renta.net> (AGPL-3.0)

const DS = DIRECTORY_SEPARATOR;
const INC = __DIR__ . DS . 'lib' . DS . 'php' . DS;
const DBG = true;

spl_autoload_register(function ($c): void {
    $f = INC . str_replace(['\\', '_'], [DS, DS], strtolower($c)) . '.php';
    if (file_exists($f)) {
        include $f;
        if (DBG) {
            error_log("include $f");
        }
    } else {
        error_log("!!! {$f} does not exist");
    }
});

echo new Init(new class()
{
    public $cfg = [
        'email' => 'markc@renta.net',
        'file' => __DIR__ . DS . 'lib' . DS . '.ht_conf.php', // settings override
        'hash' => 'SHA512-CRYPT',
        'host' => '',
        'perp' => 25,
        'self' => '/hcp/',
    ];
    public $in = [
        'a' => '',           // API (apiusr:apikey)
        'd' => '',           // Domain (current)
        'g' => null,         // Group/Category
        'i' => null,         // Item or ID
        'l' => '',           // Log (message)
        'm' => 'list',       // Method (action)
        'o' => 'home',       // Object (content)
        't' => 'bootstrap5', // Theme
        'x' => '',           // XHR (request)
    ];
    public $out = [
        'doc' => 'NetServa',
        'css' => '',
        'log' => '',
        'nav1' => '',
        'nav2' => '',
        'nav3' => '',
        'head' => 'NetServa',
        'main' => 'Error: missing page!',
        'foot' => 'Copyright (C) 2015-2019 Mark Constable (AGPL-3.0)',
        'js' => '',
        'end' => '',
    ];
    public $db = [
        'host' => 'localhost', // DB site
        'name' => 'sysadm',    // DB name
        'pass' => 'lib' . DS . '.ht_pw', // MySQL password override
        'path' => '/var/lib/sqlite/sysadm/sysadm.db', // SQLite DB
        'port' => '3306',      // DB port
        'sock' => '',          // '/run/mysqld/mysqld.sock',
        'type' => 'mysql',     // mysql | sqlite
        'user' => 'sysadm',    // DB user
    ];
    public $nav1 = [
        'non' => [
            ['Webmail',     'webmail/',         'fas fa-envelope fa-fw'],
            ['Phpmyadmin',  'phpmyadmin/',      'fas fa-globe fa-fw'],
        ],
        'usr' => [
            ['Webmail',     'webmail/',         'fas fa-envelope fa-fw'],
            ['Phpmyadmin',  'phpmyadmin/',      'fas fa-globe fa-fw'],
        ],
        'adm' => [
            ['Menu',        [
                ['Webmail',     'webmail/',     'fas fa-envelope fa-fw'],
                ['Phpmyadmin',  'phpmyadmin/',  'fas fa-globe fa-fw'],
            ], 'fas fa-bars fa-fw'],
            ['Admin',       [
                ['Accounts',    '?o=accounts',  'fas fa-users fa-fw'],
                ['Vhosts',      '?o=vhosts',    'fas fa-globe fa-fw'],
                ['Mailboxes',   '?o=vmails',    'fas fa-envelope fa-fw'],
                ['Aliases',     '?o=valias',    'fas fa-envelope-square fa-fw'],
                ['DKIM',        '?o=dkim',      'fas fa-address-card fa-fw'],
                ['Domains',     '?o=domains',   'fas fa-server fa-fw'],
            ], 'fas fa-user-cog fa-fw'],
            ['Stats',       [
                ['Sys Info',    '?o=infosys',   'fas fa-tachometer-alt fa-fw'],
                ['Processes',   '?o=processes', 'fas fa-code-branch fa-fw'],
                ['Mail Info',   '?o=infomail',  'fas fa-envelope-square fa-fw'],
                ['Mail Graph',  '?o=mailgraph', 'fas fa-envelope fa-fw'],
            ], 'fas fa-chart-line fa-fw'],
        ],
    ];
    public $nav2 = [];
    public $dns = [
        'a' => 'localhost',
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
            'host' => 'localhost', // Alt DNS DB site
            'name' => 'pdns',      // Alt DNS DB name
            'pass' => 'lib' . DS . '.ht_dns_pw', // MySQL DNS password override
            'path' => '/var/lib/sqlite/sysadm/pdns.db', // DNS SQLite DB
            'port' => '3306',      // Alt DNS DB port
            'sock' => '',          // '/run/mysqld/mysqld.sock',
            'type' => '',          // mysql | sqlite | '' to disable
            'user' => 'pdns',      // Alt DNS DB user
        ],
    ];
    public $acl = [
        0 => 'SuperAdmin',
        1 => 'Administrator',
        2 => 'User',
        3 => 'Suspended',
        9 => 'Anonymous',
    ];
});
