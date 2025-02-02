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
            $thm->theme = new $t2($g);
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
            foreach ($g->out as $k => $v)
            {
                $g->out[$k] = match (true)
                {
                    method_exists($thm, $k) => $thm->{$k}(),
                    $thm->theme && method_exists($thm->theme, $k) => $thm->theme->{$k}(),
                    method_exists('HCP\\Theme', $k) => (new Theme($g))->{$k}(),
                    default => $v
                };
            }
        }
        /*
        if (empty($g->in['x']))
        {
            foreach ($g->out as $k => $v)
            {
                elog("$k => $v");
                // Skip modal method as it requires parameters
                if ($k === 'modal')
                {
                    continue;
                }
                // Simple method resolution order:
                // 1. Plugin View (if method exists)
                // 2. Current Theme (if method exists)
                // 3. Base Theme (if method exists)
                // 4. Default from index.php

                // Try Plugin View first
                if (method_exists($thm, $k))
                {
                    $g->out[$k] = $thm->{$k}();
                }
                // Then try current theme
                else if ($thm->theme && method_exists($thm->theme, $k))
                {
                    $g->out[$k] = $thm->theme->{$k}();
                }
                // Finally try base Theme class
                else if (method_exists('HCP\\Theme', $k))
                {
                    $g->out[$k] = (new Theme($g))->{$k}();
                }
                // Fall back to default
                else
                {
                    $g->out[$k] = $v;
                }
            }
        }
        */
    }

    public function __toString(): string
    {
        elog(__METHOD__);

        $x = $this->g->in['x'];
        $f = $this->g->in['f']; // Default 'html' is already set in $in array

        // If no specific section requested, return full page HTML
        if (!$x)
        {
            return $this->t->html();
        }

        // Get the requested section content
        $content = $this->g->out[$x] ?? $this->g->out['main'] ?? '';
        if (!$content)
        {
            return "Error: Content is empty";
        }

        // Handle different output formats
        switch ($f)
        {
            case 'json':
                header('Content-Type: application/json');
                return json_encode($content, JSON_PRETTY_PRINT);

            case 'text':
                header('Content-Type: text/plain');
                return preg_replace('/^\h*\v+/m', '', strip_tags($content));

            case 'markdown':
                header('Content-Type: text/markdown');
                // You might want to add specific markdown processing here
                return preg_replace('/^\h*\v+/m', '', $content);

            case 'html':
            default:
                header('Content-Type: text/html');
                return $content;
        }
    }

    public function __destruct()
    {
        //elog(__METHOD__ . ' SESSION=' . var_export($this->g->out, true));
        elog(__FILE__ . ' ' . $_SERVER['REMOTE_ADDR'] . ' ' . round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']), 4) . "\n");
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
