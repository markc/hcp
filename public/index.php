<?php

declare(strict_types=1);
// index.php 20150101 - 20250128
// Copyright (C) 2015-2025 Mark Constable <markc@renta.net> (AGPL-3.0)

const DBG = true;

require_once __DIR__ . '/../vendor/autoload.php';

use HCP\Init;

echo new Init(new class() {
    public $cfg = [
        'email' => 'admin@example.com',
        'admpw' => 'admin123',
        'file' => '../src/.ht_conf.php', // settings override
        'hash' => 'SHA512-CRYPT',
        'host' => '',
        'perp' => 25,
        'self' => '/',
    ];

    public $in = [
        'a' => '',                  // API (apiusr:apikey)
        'd' => '',                  // Domain (current)
        'g' => null,                // Group/Category
        'i' => null,                // Item or ID
        'l' => '',                  // Log (message)
        'm' => 'list',              // Method (action)
        'o' => 'Home',              // Object (content)
        'r' => 'local',             // Remotes (local)
        't' => 'SideBar',            // Theme (Default)
        'x' => '',                  // XHR (request)
    ];

    public $out = [
        'doc'  => 'NetServa HCP',
        'css'  => '',
        'log'  => '',
        'nav1' => '',
        'nav2' => '',
        'nav3' => '',
        'head' => 'NetServa',
        'main' => 'Error: missing page!',
        'foot' => 'Copyright (C) 2015-2025 Mark Constable (AGPL-3.0)',
        'js'   => '',
        'end'  => '',
    ];

    public $t;

    public $db = [
        'host' => '127.0.0.1',      // DB site
        'name' => 'sysadm',         // DB name
        'pass' => '../src/.ht_pw',  // MySQL password override
        'path' => '../sysadm/sysadm.db', // SQLite DB
        'port' => '3306',           // DB port
        'sock' => '',               // '/run/mysqld/mysqld.sock',
        'type' => 'sqlite',         // mysql | sqlite
        'user' => 'sysadm',         // DB user
    ];

    public $nav1 = [
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

    public $nav2 = [];

    public $nav3 = [];

    public $dns = [
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
            'host' => '127.0.0.1',  // Alt DNS DB site
            'name' => 'pdns',       // Alt DNS DB name
            'pass' => '../src/.ht_dns_pw', // MySQL DNS password override
            'path' => '../sysadm/pdns.db', // DNS SQLite DB
            'port' => '3306',       // Alt DNS DB port
            'sock' => '',           // '/run/mysqld/mysqld.sock',
            'type' => 'sqlite',     // mysql | sqlite | '' to disable
            'user' => 'pdns',       // Alt DNS DB user
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

function elog(string $content): void
{
    if (DBG)
    {
        error_log($content);
    }
}

function dbg($var = null): void
{
    if (is_object($var))
    {
        $refobj = new \ReflectionObject($var);
        // get all public and protected properties
        $var = $refobj->getProperties(\ReflectionProperty::IS_PUBLIC);
        $var = \array_merge($var, $refobj->getProperties(\ReflectionProperty::IS_PROTECTED));
    }
    ob_start();
    print_r($var);
    $ob = ob_get_contents();
    ob_end_clean();
    error_log($ob);
}
