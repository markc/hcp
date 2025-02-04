<?php

declare(strict_types=1);
// Created: 20250101 - Updated: 20250204
// Copyright (C) 2015-2025 Mark Constable <markc@renta.net> (AGPL-3.0)

namespace HCP\Plugins\Home;

use HCP\Util;

class View
{
    public function list(array $in = []): string
    {
        Util::elog(__METHOD__);

        return '
            <div class="container py-5">
                <div class="text-center mb-5">
                    <h1 class="display-4 mb-4"><i class="bi bi-boxes"></i> NetServa HCP</h1>
                    <p class="lead col-lg-8 mx-auto">
                        This is a lightweight Web, Mail and DNS server with a PHP
                        based <strong>Hosting Control Panel</strong> for servicing
                        multiple virtually hosted domains. The operating system is
                        based on the latest Debian or Ubuntu packages and can use
                        either SQLite or MySQL as a backend database. The entire 
                        server can run in as little as 256 MB of ram when paired
                        with SQLite and still serve a dozen lightly loaded virtual
                        hosts so it is ideal for Proxmox and Incus virtual machines
                        and containers.
                    </p>
                    <div class="mt-4">
                        <a href="https://github.com/markc/hcp" class="btn btn-primary mx-2">
                            <i class="bi bi-github"></i> Project Page
                        </a>
                        <a href="https://github.com/markc/hcp/issues" class="btn btn-primary mx-2">
                            <i class="bi bi-bug"></i> Issue Tracker
                        </a>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-12 col-lg-7">
                        <div class="card h-100">
                            <div class="card-body">
                                <h2 class="card-title h4 mb-4"><i class="bi bi-stars"></i> Features</h2>
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-3"><i class="bi bi-check2-circle text-success"></i> <strong>NetServa HCP</strong> does not require Python or Ruby, just PHP and Bash</li>
                                    <li class="mb-3"><i class="bi bi-check2-circle text-success"></i> Fully functional Mail server with personalised Spam filtering</li>
                                    <li class="mb-3"><i class="bi bi-check2-circle text-success"></i> Secure SSL enabled <a href="http://nginx.org/">nginx</a> web server with <a href="http://www.php.net/manual/en/install.fpm.php">PHP FPM 8+</a></li>
                                    <li class="mb-3"><i class="bi bi-check2-circle text-success"></i> Always based and tested on the latest release of <a href="https://ubuntu.com">Ubuntu</a> and <a href="https://www.debian.org">Debian</a></li>
                                    <li class="mb-3"><i class="bi bi-check2-circle text-success"></i> Optional DNS server for local LAN or real-world DNS provisioning</li>
                                    <li class="mb-0"><i class="bi bi-check2-circle text-success"></i> Built from the ground up using <a href="https://getbootstrap.com">Bootstrap 5</a> and <a href="https://datatables.net">DataTables</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-5">
                        <div class="card h-100">
                            <div class="card-body">
                                <h2 class="card-title h4 mb-4"><i class="bi bi-info-circle"></i> Notes</h2>
                                <p>You can change the content of this page by creating a file called <code>lib/php/home.tpl</code> and add any Bootstrap 5 based layout and text you care to.</p>
                                <p class="mb-4">Modifying the navigation menus above can be done by creating a <code>lib/.ht_conf.php</code> file and copying the <a href="https://github.com/netserva/hcp/blob/master/index.php#L60">$nav1 array</a> from <code>index.php</code> into that optional config override file.</p>
                                <p class="mb-0">Comments and pull requests are most welcome via the Issue Tracker link above.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';
    }
}
