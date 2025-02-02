<?php

declare(strict_types=1);

namespace HCP;

use HCP\Util;
use HCP\Db;

abstract class Plugin
{
    protected string $buf = '';
    protected mixed $dbh = null;
    protected string $tbl = '';
    protected array $in = [];
    public object $g;

    public function __construct(public object $t)
    {
        elog(__METHOD__);

        $this->t = $t;
        $this->g = $t->g;
        $this->in = Util::esc($this->in);

        $this->initializeDatabase();
        $this->processAction();
    }

    protected function initializeDatabase(): void
    {
        elog(__METHOD__);

        if ($this->tbl)
        {
            if (!is_null($this->dbh))
            {
                Db::$dbh = $this->dbh;
            }
            elseif (is_null(Db::$dbh))
            {
                Db::$dbh = new Db($this->t->g->db);
            }
            Db::$tbl = $this->tbl;
        }
    }

    protected function processAction(): void
    {
        elog(__METHOD__);

        $method = $this->g->in['m'];
        if (method_exists($this, $method))
        {
            $this->buf .= $this->{$method}();
        }
        else
        {
            $this->buf .= 'Method not found: ' . $method;
        }
    }

    public function __toString(): string
    {
        elog(__METHOD__);

        return $this->buf;
    }

    protected function create(): string
    {
        elog(__METHOD__);

        if (Util::is_post())
        {
            $this->in['updated'] = date('Y-m-d H:i:s');
            $this->in['created'] = date('Y-m-d H:i:s');
            $lid = Db::create($this->in);
            Util::log('Item number ' . $lid . ' created', 'success');
            Util::relist();
        }
        return $this->t->create($this->in);
    }

    protected function read(): string
    {
        elog(__METHOD__);
        return $this->t->read(Db::read('*', 'id', $this->g->in['i'], '', 'one'));
    }

    protected function update(): string
    {
        elog(__METHOD__);

        if (Util::is_post())
        {
            $this->in['updated'] = date('Y-m-d H:i:s');
            if (Db::update($this->in, [['id', '=', $this->g->in['i']]]))
            {
                Util::log('Item number ' . $this->g->in['i'] . ' updated', 'success');
                Util::relist();
            }
            else
            {
                Util::log('Error updating item.');
            }
        }
        return $this->read();
    }

    protected function delete(): string
    {
        elog(__METHOD__);

        if (Util::is_post())
        {
            if ($this->g->in['i'])
            {
                Db::delete([['id', '=', $this->g->in['i']]]);
                Util::log('Item number ' . $this->g->in['i'] . ' removed', 'success');
                Util::relist();
            }
            else
            {
                Util::log('Error deleting item');
            }
        }
        return '';
    }

    protected function list(): string
    {
        elog(__METHOD__);
        return $this->t->list(Db::read('*', '', '', 'ORDER BY `updated` DESC'));
    }

    protected function validateAccess(): bool
    {
        $o = $this->t->g->in['o'];
        $m = $this->t->g->in['m'];

        if (!Util::is_usr() && ('Auth' !== $o || !in_array($m, ['list', 'create', 'resetpw'])))
        {
            Util::redirect($this->t->g->cfg['self'] . '?o=Auth');
            return false;
        }
        return true;
    }
}
