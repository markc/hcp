<?php

declare(strict_types=1);
// lib/php/plugins/valias.php 20170225 - 20230624
// Copyright (C) 1995-2023 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_Valias extends Plugin
{
    protected string $tbl = 'valias';

    public array $inp = [
        'aid'    => 1,
        'hid'    => 1,
        'source' => '',
        'target' => '',
        'active' => 0,
    ];

    // TODO: recfactor common parts of create() and update() into private methods
    // yep, as of 20170704 this is still a low to medium priority TODO

    protected function create(): string
    {
        if (util::is_post()) {
            extract($this->inp);
            $active = $active ? 1 : 0;
            $sources = array_map('trim', preg_split("/( |,|;|\n)/", $source));
            $targets = array_map('trim', preg_split("/( |,|;|\n)/", $target));

            if (empty($source[0])) {
                util::log('Alias source address is empty');
                $_POST = [];

                return $this->g->t->list($this->inp);
            }

            if (empty($targets[0])) {
                util::log('Alias target address is empty');
                $_POST = [];

                return $this->g->t->list($this->inp);
            }

            foreach ($sources as $s) {
                if (empty($s)) {
                    continue;
                }
                $lhs = '';
                $rhs = '';
                if (str_contains($s, '@')) {
                    [$lhs, $rhs] = explode('@', $s);
                } else {
                    $rhs = $s;
                }

                if (!$domain = idn_to_ascii($rhs)) {
                    util::log('Invalid source domain: ' . $rhs);
                    $_POST = [];

                    return $this->g->t->create($this->inp);
                }

                $sql = '
 SELECT `id`
   FROM `vhosts`
  WHERE `domain` = :domain';

                $hid = db::qry($sql, ['domain' => $domain], 'col');

                if (!$hid) {
                    util::log($domain . ' does not exist as a local domain');
                    $_POST = [];

                    return $this->g->t->create($this->inp);
                }

                if ((!filter_var($s, FILTER_VALIDATE_EMAIL)) && !empty($lhs)) {
                    util::log('Alias source address is invalid');
                    $_POST = [];

                    return $this->g->t->create($this->inp);
                }

                $sql = '
 SELECT 1 FROM `valias`
  WHERE `source` = :catchall';

                $catchall = db::qry($sql, ['catchall' => '@' . $domain], 'col');
                //elog("catchall=$catchall");

                if (1 !== $catchall) {
                    $sql = '
 SELECT `source`
   FROM `valias`
  WHERE `source` = :source';

                    $num_results = count(db::qry($sql, ['source' => $s]));

                    if ($num_results) {
                        util::log($s . ' already exists as an alias');
                        $_POST = [];

                        return $this->g->t->create($this->inp);
                    }
                }

                $sql = '
 SELECT `user`
   FROM `vmails`
  WHERE `user` = :source';

                $num_results = count(db::qry($sql, ['source' => $s]));

                if ($num_results) {
                    util::log($s . ' already exists as a regular mailbox');
                    $_POST = [];

                    return $this->g->t->create($this->inp);
                }

                foreach ($targets as $t) {
                    if (empty($t)) {
                        continue;
                    }
                    [$tlhs, $trhs] = explode('@', $t);

                    if (!$tdomain = idn_to_ascii($trhs)) {
                        util::log('Invalid target domain: ' . $tdomain);
                        $_POST = [];

                        return $this->g->t->create($this->inp);
                    }

                    if (!filter_var($t, FILTER_VALIDATE_EMAIL)) {
                        util::log('Alias target address is invalid');
                        $_POST = [];

                        return $this->g->t->create($this->inp);
                    }

                    if (1 !== $catchall) {
                        if ($t === $s) {
                            util::log('Alias source and target addresses must not be the same');
                            $_POST = [];

                            return $this->g->t->create($this->inp);
                        }
                    }
                }

                $target = implode(',', $targets);

                $sql = '
 INSERT INTO `valias` (
        `active`,
        `hid`,
        `source`,
        `target`,
        `updated`,
        `created`
) VALUES (
        :active,
        :hid,
        :source,
        :target,
        :updated,
        :created
)';
                $s = filter_var($s, FILTER_VALIDATE_EMAIL)
                    ? $s
                    : '@' . $domain;

                $result = db::qry($sql, [
                    'active'  => $active ? 1 : 0,
                    'hid'     => $hid,
                    'source'  => $s,
                    'target'  => $target,
                    'updated' => date('Y-m-d H:i:s'),
                    'created' => date('Y-m-d H:i:s'),
                ]);
                // test $result?
            }
            util::log('Alias added', 'success');
            util::ses('p', '', '1');
            util::redirect($this->g->cfg['self'] . '?o=' . $this->g->in['o'] . '&m=list');
        } else {
            return $this->g->t->create($this->inp);
        }
    }

    protected function read(): string
    {
        return $this->g->t->update(db::read('*', 'id', $this->g->in['i'], '', 'one'));
    }

    protected function update(): string
    {
        if (util::is_post()) {
            extract($this->inp);
            $active = $active ? 1 : 0;
            $sources = array_map('trim', preg_split("/( |,|;|\n)/", $source));
            $targets = array_map('trim', preg_split("/( |,|;|\n)/", $target));

            if (empty($source[0])) {
                util::log('Alias source address is empty');
                $_POST = [];

                return $this->read();
            }

            if (empty($targets[0])) {
                util::log('Alias target address is empty');
                $_POST = [];

                return $this->read();
            }

            foreach ($sources as $s) {
                if (empty($s)) {
                    continue;
                }
                $lhs = '';
                $rhs = '';
                if (str_contains($s, '@')) {
                    [$lhs, $rhs] = explode('@', $s);
                } else {
                    $rhs = $s;
                }

                if (!$domain = idn_to_ascii($rhs)) {
                    util::log('Invalid source domain: ' . $rhs);
                    $_POST = [];

                    return $this->read();
                }

                $sql = '
 SELECT `id`
   FROM `vhosts`
  WHERE `domain` = :domain';

                $hid = db::qry($sql, ['domain' => $domain], 'col');

                if (!$hid) {
                    util::log($domain . ' does not exist as a local domain');
                    $_POST = [];

                    return $this->read();
                }

                if ((!filter_var($s, FILTER_VALIDATE_EMAIL)) && !empty($lhs)) {
                    util::log('Alias source address is invalid');
                    $_POST = [];

                    return $this->read();
                }

                $sql = '
 SELECT 1
   FROM `valias`
  WHERE `source` = :catchall';

                $catchall = db::qry($sql, ['catchall' => '@' . $domain], 'col');
                //elog("catchall=$catchall");

                if (1 !== $catchall) {
                    $sql = '
 SELECT `user`
   FROM `vmails`
  WHERE `user` = :source';

                    $num_results = count(db::qry($sql, ['source' => $s]));

                    if ($num_results) {
                        util::log($s . ' already exists as a regular mailbox');
                        $_POST = [];

                        return $this->read();
                    }
                }

                foreach ($targets as $t) {
                    if (empty($t)) {
                        continue;
                    }
                    [$tlhs, $trhs] = explode('@', $t);

                    if (!$tdomain = idn_to_ascii($trhs)) {
                        util::log('Invalid target domain: ' . $tdomain);
                        $_POST = [];

                        return $this->read();
                    }

                    if (!filter_var($t, FILTER_VALIDATE_EMAIL)) {
                        util::log('Alias target address is invalid');
                        $_POST = [];

                        return $this->read();
                    }

                    if (1 !== $catchall) {
                        if ($t === $s) {
                            util::log('Alias source and target addresses must not be the same');
                            $_POST = [];

                            return $this->read();
                        }
                    }
                }

                $target = implode(',', $targets);
                $s = filter_var($s, FILTER_VALIDATE_EMAIL)
                    ? $s
                    : '@' . $domain;

                $sql = '
 SELECT `source`
   FROM `valias`
  WHERE `source` = :source';

                $exists = count(db::qry($sql, ['source' => $s]));

                if ($exists or (1 == count($sources))) {
                    $sql = '
 UPDATE `valias` SET
        `active`  = :active,
        `source`  = :source,
        `target`  = :target,
        `updated` = :updated
  WHERE `id` = :id';

                    $result = db::qry($sql, [
                        'id' => $this->g->in['i'],
                        'active' => $active,
                        'source' => $s,
                        'target' => $target,
                        'updated' => date('Y-m-d H:i:s'),
                    ]);
                } else {
                    $sql = '
 INSERT INTO `valias` (
        `active`,
        `hid`,
        `source`,
        `target`,
        `updated`,
        `created`
) VALUES (
        :active,
        :hid,
        :source,
        :target,
        :updated,
        :created
)';
                    $result = db::qry($sql, [
                        'active' => $active ? 1 : 0,
                        'hid' => $hid,
                        'source' => $s,
                        'target' => $target,
                        'updated' => date('Y-m-d H:i:s'),
                        'created' => date('Y-m-d H:i:s'),
                    ]);
                }
            }
            util::log('Changes to alias have been saved', 'success');
            util::relist();
        } elseif ($this->g->in['i']) {
            return $this->read();
        } else {
            return 'Error updating item';
        }
    }

    protected function list(): string
    {
        if ('json' === $this->g->in['x']) {
            $columns = [
                ['dt' => 0, 'db' => 'source', 'formatter' => function ($d, $row) {
                    return '
                    <a href="?o=valias&m=update&i=' . $row['id'] . '" class="bslink" title="Update entry for ' . $d . '">
                      <b>' . $d . ' </b></a>';
                }],
                ['dt' => 1, 'db' => 'target', 'formatter' => fn ($d) => str_replace(',', '<br>', $d)],
                ['dt' => 2, 'db' => 'domain'],
                ['dt' => 3, 'db' => 'active', 'formatter' => fn ($d) => '<i class="' . ($d ? 'bi-check-lg text-success' : 'bi-x-lg text-danger') . '"></i>'],
                ['dt' => 4, 'db' => 'id'],
                ['dt' => 5, 'db' => 'updated'],
            ];

            return json_encode(db::simple($_GET, 'valias_view', 'id', $columns), JSON_PRETTY_PRINT);
        }

        return $this->g->t->list([]);
    }
}
