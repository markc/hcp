<?php

declare(strict_types=1);
// lib/php/themes/bootstrap/theme.php 20150101 - 20250128
// Copyright (C) 2015-2025 Mark Constable <markc@renta.net> (AGPL-3.0)

namespace HCP;

use HCP\Theme;
use HCP\Util;

class TopNav extends Theme
{
    protected array $defaultNav = [];

    public function css(): string
    {
        elog(__METHOD__);

        return '
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
body{min-height:75rem;padding-top:5rem;}
table,form{width:100%;}
table.dataTable{border-collapse: collapse !important;}
.table{table-layout: fixed;}

.media {
  flex-direction: column;
  align-items: center;
  margin-top: 1.5rem;
  margin-bottom: 1.5rem;
}
.media-img {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 0.75rem;
}
.media img {
  max-width: 100%;
  height: auto;
}
.media-blank {
  width: 300px;
}
.media-title {
  margin-bottom: 0.75rem;
}
.alert pre {
  margin: 0;
}
.columns {
  column-gap:1.5em;columns:1;}

@media (min-width:768px) {
  .columns {column-gap:1.5em;columns:2;}
  .media {
    flex-direction: row;
    align-items: flex-start;
  }
  .media-body {
    margin-left: 1.5rem;
  }
  .media-img, .media-blank, .media img {
    max-width: 200px;
  }
}

@media (min-width:992px) {
  .media-img, .media-blank, .media img {
    max-width: 100%;
  }
}

@media (min-width:1200px) {
  .columns {column-gap:1.5em;columns:3;}
  .media-title {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: flex-end;
  }
}
    </style>';
    }

    public function log(): string
    {
        elog(__METHOD__);

        $alts = '';
        foreach (Util::log() as $lvl => $msg) {
            if ($msg) {
                $alts .= sprintf(
                    '
            <div class="col-12">
              <div class="alert alert-%s alert-dismissible fade show" role="alert">
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>%s
              </div>
            </div>',
                    $lvl,
                    $msg
                );
            }
        }
        return $alts;
    }

    public function head(): string
    {
        elog(__METHOD__);

        return sprintf(
            '
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top bg-dark">
      <div class="container">
        <a class="navbar-brand" href="%s">
          <b><i class="bi bi-server" aria-hidden="true"></i> %s</b>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsDefault" aria-controls="navbarsDefault" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarsDefault">
          <ul class="navbar-nav me-auto">%s</ul>
          <ul class="navbar-nav ms-auto">%s</ul>
        </div>
      </div>
    </nav>',
            $this->g->cfg['self'],
            $this->g->out['head'],
            $this->g->out['nav1'],
            $this->g->out['nav3']
        );
    }

    public function nav1(array $a = []): string
    {
        elog(__METHOD__);

        $a = $a[0] ?? Util::get_nav($this->g->nav1);
        $o = '?o=' . $this->g->in['o'];
        $t = '?t=' . Util::ses('t');

        return implode('', array_map(
            fn($n) =>
            is_array($n[1])
                ? $this->nav_dropdown($n)
                : sprintf(
                    '<li class="nav-item%s"><a class="nav-link%s" href="%s">%s%s</a></li>',
                    $o === $n[1] || $t === $n[1] ? ' active' : '',
                    $o === $n[1] || $t === $n[1] ? ' active' : '',
                    $n[1],
                    $n[2] ?? '' ? '<i class="' . $n[2] . '" aria-hidden="true"></i> ' : '',
                    $n[0]
                ),
            $a
        ));
    }

    public function nav2(): string
    {
        elog(__METHOD__);
        return $this->nav_dropdown(['Theme', $this->g->nav2, 'fa fa-th fa-fw']);
    }

    public function nav3(): string
    {
        elog(__METHOD__);

        if (!Util::is_usr()) {
            return '';
        }

        $usr = [
            ['Change Profile', '?o=Accounts&m=read&i=' . $_SESSION['usr']['id'], 'fas fa-user fa-fw'],
            ['Change Password', '?o=Auth&m=update&i=' . $_SESSION['usr']['id'], 'fas fa-key fa-fw'],
            ['Sign out', '?o=Auth&m=delete', 'fas fa-sign-out-alt fa-fw']
        ];

        if (Util::is_adm() && !Util::is_acl(0)) {
            $usr[] = ['Switch to sysadm', '?o=Accounts&m=switch_user&i=' . $_SESSION['adm'], 'fas fa-user fa-fw'];
        }

        return $this->nav_dropdown([$_SESSION['usr']['login'], $usr, 'fas fa-user fa-fw']);
    }

    public function nav_dropdown(array $a = []): string
    {
        elog(__METHOD__);

        $o = '?o=' . $this->g->in['o'];
        $icon = $a[2] ?? '';

        return sprintf(
            '
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">%s%s</a>
              <div class="dropdown-menu">%s</div>
            </li>',
            $icon ? sprintf('<i class="%s" aria-hidden="true"></i> ', $icon) : '',
            $a[0],
            implode('', array_map(fn($n) => sprintf(
                '
                <a class="dropdown-item%s" href="%s">%s%s</a>',
                $o === $n[1] ? ' active' : '',
                $n[1],
                $n[2] ?? '' ? sprintf('<i class="%s" aria-hidden="true"></i> ', $n[2]) : '',
                $n[0]
            ), $a[1]))
        );
    }

    public function main(): string
    {
        elog(__METHOD__);

        return sprintf(
            '
    <main class="container">
      <div class="row">%s%s</div>
    </main>',
            $this->g->out['log'],
            $this->g->out['main']
        );
    }

    public function js(): string
    {
        elog(__METHOD__);

        return '
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>';
    }

    protected function modal(array $ary): string
    {
        elog(__METHOD__);

        ['id' => $id, 'title' => $title, 'body' => $body, 'action' => $action] = $ary;
        $hidden = $ary['hidden'] ?? '';
        $footer = isset($ary['footer']) ? sprintf('
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    %s
                </div>', $ary['footer']) : '';

        return sprintf(
            '
        <div class="modal fade" id="%1$s" tabindex="-1" aria-labelledby="%1$s-label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="%1$s-label">%2$s</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="post" action="%3$s">
                        <input type="hidden" name="c" value="%4$s">
                        <input type="hidden" name="o" value="%5$s">
                        <input type="hidden" name="m" value="%6$s">
                        <input type="hidden" name="i" value="%7$s">%8$s
                        <div class="modal-body">%9$s</div>
                        %10$s
                    </form>
                </div>
            </div>
        </div>',
            $id,
            $title,
            $this->g->cfg['self'],
            $_SESSION['c'],
            $this->g->in['o'],
            $action,
            $this->g->in['i'],
            $hidden,
            $body,
            $footer
        );
    }
}
