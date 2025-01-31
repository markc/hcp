<?php

declare(strict_types=1);

namespace HCP;

use HCP\Util;

class Init
{
    private $t;

    public function __construct(public object $g)
    {
        elog(__METHOD__);

        $this->g = $g;

        session_start();

        elog('GET=' . var_export($_GET, true));
        elog('POST=' . var_export($_POST, true));

        $g->cfg['host'] ??= getenv('HOSTNAME');

        Util::cfg($g);

        $g->in = Util::esc($g->in);

        $g->cfg['self'] = str_replace('index.php', '', $_SERVER['PHP_SELF']);

        if (!isset($_SESSION['c']))
        {
            $_SESSION['c'] = Util::random_token(32);
        }

        Util::ses('o');
        Util::ses('m');
        Util::ses('l');

        //        $t = Util::ses('t', '', $g->in['t']);
        $t = Util::ses('t', $g->in['t']);
        elog("t=$t");

        $t1 = "HCP\\Plugins\\{$g->in['o']}\\View";
        elog("t1=$t1");

        $t2 = "HCP\\Themes\\$t";
        elog("t2=$t2");

        $this->t = $g->t = $thm = class_exists($t1)
            ? new $t1($g)
            : new Theme($g);

        if (class_exists($t2))
        {
            $thm->themeImpl = new $t2($g);
        }

        $p = "HCP\\Plugins\\{$g->in['o']}\\Model";

        if (class_exists($p))
        {
            $g->in['a'] ? Util::chkapi($g) : Util::remember($g);
            $g->out['main'] = (string) new $p($thm);
        }
        else
        {
            $g->out['main'] = 'Error: no plugin object!';
        }

        if (empty($g->in['x']))
        {
            if ($thm->themeImpl)
            {
                foreach ($g->out as $k => $v)
                {
                    $g->out[$k] = method_exists($thm->themeImpl, $k)
                        ? $thm->themeImpl->{$k}()
                        : (method_exists($thm, $k) ? $thm->{$k}() : $v);
                }
            }
            else
            {
                foreach ($g->out as $k => $v)
                {
                    $g->out[$k] = method_exists($thm, $k) ? $thm->{$k}() : $v;
                }
            }
        }
    }

    public function __destruct()
    {
        elog(__METHOD__ . ' SESSION=' . var_export($_SESSION, true));
        elog(__FILE__ . ' ' . $_SERVER['REMOTE_ADDR'] . ' ' . round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']), 4) . "\n");
    }

    public function __toString(): string
    {
        elog(__METHOD__);
        $g = $this->g;
        $x = $g->in['x'];

        if ('text' === $x)
        {
            return preg_replace('/^\h*\v+/m', '', strip_tags($g->out['main']));
        }

        if ('json' === $x)
        {
            header('Content-Type: application/json');
            return $g->out['main'];
        }

        if ($x)
        {
            $out = $g->out[$x] ?? '';
            if ($out)
            {
                header('Content-Type: application/json');
                return json_encode($out, JSON_PRETTY_PRINT);
            }
        }

        return $this->t->html();
    }
}

function dbg($var = null): void
{
    if (is_object($var))
    {
        $refobj = new \ReflectionObject($var);
        $var = $refobj->getProperties(\ReflectionProperty::IS_PUBLIC);
        $var = \array_merge($var, $refobj->getProperties(\ReflectionProperty::IS_PROTECTED));
    }
    ob_start();
    print_r($var);
    $ob = ob_get_contents();
    ob_end_clean();
    error_log($ob);
}

function elog(string $content): void
{
    if (DBG)
    {
        error_log($content);
    }
}
