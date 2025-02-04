<?php

declare(strict_types=1);
// Created: 20150225 - Updated: 20250204
// Copyright (C) 2015-2025 Mark Constable <markc@renta.net> (AGPL-3.0)

namespace HCP;

use HCP\Db;

final class Util
{
    private function __construct()
    {
    }

    public static function elog(string $content): void
    {
        if (defined('\HCP\DBG') && \HCP\DBG)
        {
            error_log($content);
        }
    }

    public static function log(string $msg = '', string $lvl = 'danger'): array
    {
        self::elog(__METHOD__);

        if (!isset($_SESSION['log']))
        {
            $_SESSION['log'] = [];
        }

        if ($msg)
        {
            if (!isset($_SESSION['log'][$lvl]))
            {
                $_SESSION['log'][$lvl] = '';
            }
            $_SESSION['log'][$lvl] = empty($_SESSION['log'][$lvl]) ? $msg : $_SESSION['log'][$lvl] . '<br>' . $msg;
        }
        elseif (!empty($_SESSION['log']))
        {
            $l = $_SESSION['log'];
            $_SESSION['log'] = [];
            return $l;
        }

        return [];
    }

    public static function enc(string $v): string
    {
        self::elog(__METHOD__ . "({$v})");

        return htmlentities(trim($v), ENT_QUOTES, 'UTF-8');
    }

    public static function esc(array $in): array
    {
        self::elog(__METHOD__);

        foreach ($in as $k => $v)
        {
            $in[$k] = isset($_REQUEST[$k]) && !is_array($_REQUEST[$k])
                ? self::enc($_REQUEST[$k]) : $v;
        }

        return $in;
    }

    public static function ses(string $k, mixed $v = '', mixed $x = null): mixed
    {
        self::elog(__METHOD__ . "({$k}, " . var_export($v, true) . ", " . var_export($x, true) . ")");

        if (isset($_REQUEST[$k]))
        {
            $_SESSION[$k] = is_array($_REQUEST[$k]) ? $_REQUEST[$k] : self::enc($_REQUEST[$k]);
        }
        elseif (!isset($_SESSION[$k]))
        {
            $_SESSION[$k] = $x ?? $v;
        }

        return $_SESSION[$k];
    }

    public static function cfg(Init $init): void
    {
        self::elog(__METHOD__);

        $config = $init->getConfig();
        if (file_exists($config->file))
        {
            $loadedConfig = include $config->file;
            foreach ($loadedConfig as $key => $value)
            {
                if (property_exists($config, $key) && is_array($config->$key))
                {
                    $config->$key = array_merge($config->$key, $value);
                }
            }
        }
    }

    public static function exe(string $cmd, bool $ret = false): bool
    {
        self::elog(__METHOD__ . "({$cmd})");

        exec('sudo ' . escapeshellcmd($cmd) . ' 2>&1', $retArr, $retVal);
        util::log('<pre>' . trim(implode("\n", $retArr)) . '</pre>', $retVal ? 'danger' : 'success');

        return (boolval($retVal) ? true : false);
    }

    public static function run(string $cmd): string
    {
        self::elog(__METHOD__ . "({$cmd})");

        return exec('sudo ' . escapeshellcmd($cmd) . ' 2>&1');
    }

    public static function now(string|int $date1, string|int|null $date2 = null): string
    {
        self::elog(__METHOD__);

        $timestamp1 = is_numeric($date1) ? (int)$date1 : strtotime($date1);
        $timestamp2 = (int)($date2 ? (is_numeric($date2) ? $date2 : strtotime($date2)) : time());

        $diff = abs($timestamp1 - $timestamp2);

        if ($diff < 10)
        {
            return ' just now';
        }

        $blocks = [
            'year' => 31536000,
            'month' => 2678400,
            'week' => 604800,
            'day' => 86400,
            'hour' => 3600,
            'min' => 60,
            'sec' => 1,
        ];

        $result = [];
        foreach ($blocks as $unit => $seconds)
        {
            if (count($result) >= 2) break;

            if ($amount = floor($diff / $seconds))
            {
                $result[] = "$amount $unit" . ($amount > 1 ? 's' : '');
                $diff -= $amount * $seconds;
            }
        }

        return implode(' ', $result) . ' ago';
    }

    public static function is_adm(): bool
    {
        self::elog(__METHOD__);

        return isset($_SESSION['adm']);
    }

    public static function is_usr(mixed $id = null): bool
    {
        self::elog(__METHOD__);

        return (is_null($id))
            ? isset($_SESSION['usr'])
            : isset($_SESSION['usr']['id']) && $_SESSION['usr']['id'] == $id;
    }

    public static function is_acl(int $acl): bool
    {
        self::elog(__METHOD__);

        return isset($_SESSION['usr']['acl']) && $_SESSION['usr']['acl'] == $acl;
    }

    public static function genpw(int $length = 10): string
    {
        self::elog(__METHOD__);

        return str_replace(
            '.',
            '_',
            substr(
                password_hash((string) time(), PASSWORD_DEFAULT),
                random_int(10, 50),
                $length
            )
        );
    }

    public static function get_nav(array $nav = []): array
    {
        self::elog(__METHOD__);

        return isset($_SESSION['usr'])
            ? (isset($_SESSION['adm']) ? $nav['adm'] : $nav['usr'])
            : $nav['non'];
    }

    public static function get_cookie(string $name, string $default = ''): string
    {
        self::elog(__METHOD__);

        return $_COOKIE[$name] ?? $default;
    }

    public static function put_cookie(string $name, string $value, int $expiry = 604800): string
    {
        self::elog(__METHOD__);

        return setcookie($name, $value, time() + $expiry) ? $value : '';
    }

    public static function del_cookie(string $name): string
    {
        self::elog(__METHOD__);

        return self::put_cookie($name, '', -1);
    }

    public static function chkpw(string $pw, string $pw2 = ''): bool
    {
        self::elog(__METHOD__);

        $validationResult = match (true)
        {
            strlen($pw) <= 11 => 'Passwords must be at least 12 characters',
            !preg_match('/[0-9]+/', $pw) => 'Password must contains at least one number',
            !preg_match('/[A-Z]+/', $pw) => 'Password must contains at least one captital letter',
            !preg_match('/[a-z]+/', $pw) => 'Password must contains at least one lower case letter',
            $pw2 !== '' && $pw !== $pw2 => 'Passwords do not match, please try again',
            default => ''
        };

        if ($validationResult !== '')
        {
            util::log($validationResult);
            return false;
        }

        return true;
    }

    public static function chkapi(Init $init): void
    {
        self::elog(__METHOD__);

        [$apiusr, $apikey] = explode(':', $init->getController()->input['api'], 2);

        if (self::is_usr($apiusr))
        {
            self::elog("API id={$apiusr} is already logged in");
            return;
        }

        Db::$dbh ??= new Db($init->getConfig()->db);
        Db::$tbl = 'accounts';

        $usr = Db::read('id,grp,acl,login,fname,lname,webpw', 'id', $apiusr, '', 'one')
            ?? exit('Invalid Email Or Password');

        match (true)
        {
            $usr['acl'] === ACL::Suspended->value
            => exit('Account is disabled, contact your System Administrator'),

            !password_verify(
                html_entity_decode($apikey, ENT_QUOTES, 'UTF-8'),
                $usr['webpw']
            ) => exit('Invalid Email Or Password'),

            default => self::chkadm($apiusr, $usr)
        };
    }

    private static function chkadm(string $apiusr, array $usr): void
    {
        self::elog("API login for id={$apiusr}");

        $_SESSION['usr'] = $usr;

        if ($usr['acl'] === ACL::SuperAdmin->value)
        {
            $_SESSION['adm'] = $apiusr;
        }
    }

    public static function remember(Init $init): void
    {
        self::elog(__METHOD__);

        if (!self::is_usr())
        {
            if ($c = self::get_cookie('remember'))
            {
                if (is_null(Db::$dbh))
                {
                    Db::$dbh = new Db($init->getConfig()->db);
                }
                Db::$tbl = 'accounts';
                if ($usr = Db::read('id,grp,acl,login,fname,lname,cookie', 'cookie', $c, '', 'one'))
                {
                    extract($usr);
                    $_SESSION['usr'] = $usr;
                    if ($acl === ACL::SuperAdmin->value)
                    {
                        $_SESSION['adm'] = $id;
                    }
                    self::log($login . ' is remembered and logged back in', 'success');
                    self::ses('object', '', $init->getController()->input['object']);
                    self::ses('method', '', $init->getController()->input['method']);
                }
            }
        }
    }

    public static function redirect(string $url, string $method = 'location', int $ttl = 5, string $msg = ''): void
    {
        self::elog(__METHOD__ . "({$url})");

        if ('refresh' == $method)
        {
            header('refresh:' . $ttl . '; url=' . $url);
            echo '<!DOCTYPE html>
<title>Redirect...</title>
<h2 style="text-align:center">Redirecting in ' . $ttl . ' seconds...</h2>
<pre style="width:50em;margin:0 auto;">' . $msg . '</pre>';
        }
        else
        {
            header('Location:' . $url);
        }

        exit;
    }

    public static function relist(): void
    {
        self::elog(__METHOD__);

        self::redirect('?plugin=' . $_SESSION['plugin'] . '&action=list');
    }

    public static function numfmt(float $size, ?int $precision = null): string
    {
        self::elog(__METHOD__);

        if (0 == $size)
        {
            return '0';
        }
        if ($size >= 1000000000000)
        {
            return round(($size / 1000000000000), $precision ?? 3) . ' TB';
        }
        if ($size >= 1000000000)
        {
            return round(($size / 1000000000), $precision ?? 2) . ' GB';
        }
        if ($size >= 1000000)
        {
            return round(($size / 1000000), $precision ?? 1) . ' MB';
        }
        if ($size >= 1000)
        {
            return round(($size / 1000), $precision ?? 0) . ' KB';
        }

        return $size . ' Bytes';
    }

    // numfmt() was wrong, we want MB not MiB
    public static function numfmtsi(float $size, int $precision = 2): string
    {
        self::elog(__METHOD__);

        if (0 == $size)
        {
            return '0';
        }
        $base = log($size, 1024);
        $suffixes = [' Bytes', ' KiB', ' MiB', ' GiB', ' TiB'];

        return round(1024 ** ($base - floor($base)), $precision) . $suffixes[floor($base)];
    }

    public static function is_valid_domain_name(string $domainname): bool
    {
        self::elog(__METHOD__);

        $domainname = idn_to_ascii($domainname);

        return preg_match('/^([a-z\\d](-*[a-z\\d])*)(\\.([a-z\\d](-*[a-z\\d])*))*$/i', $domainname)
            && preg_match('/^.{1,253}$/', $domainname)
            && preg_match('/^[^\\.]{1,63}(\\.[^\\.]{1,63})*$/', $domainname);
    }

    public static function mail_password(string $pw, string $hash = 'SHA512-CRYPT'): string
    {
        self::elog(__METHOD__);

        $salt_str = bin2hex(openssl_random_pseudo_bytes(8));

        return 'SHA512-CRYPT' === $hash
            ? '{SHA512-CRYPT}' . crypt($pw, '$6$' . $salt_str . '$')
            : '{SSHA256}' . base64_encode(hash('sha256', $pw . $salt_str, true) . $salt_str);
    }

    public static function sec2time(int $seconds): string
    {
        self::elog(__METHOD__);

        $dtF = new \DateTime('@0');
        $dtT = new \DateTime("@{$seconds}");

        return $dtF->diff($dtT)->format('%a days, %h hours, %i mins and %s secs');
    }

    public static function is_post(): bool
    {
        self::elog(__METHOD__);

        if ('POST' === $_SERVER['REQUEST_METHOD'])
        {
            if (!isset($_POST['c']) || $_SESSION['c'] !== $_POST['c'])
            {
                self::log('Possible CSRF attack');
                self::redirect('?plugin=' . $_SESSION['plugin'] . '&action=list');
            }

            return true;
        }

        return false;
    }

    public static function inc_soa(string $soa): string
    {
        self::elog(__METHOD__);

        $ary = explode(' ', $soa);
        $ymd = date('Ymd');
        $day = substr($ary[2], 0, 8);
        $rev = substr($ary[2], -2);
        $ary[2] = ($day == $ymd)
            ? "{$ymd}" . sprintf('%02d', $rev + 1)
            : "{$ymd}" . '00';

        return implode(' ', $ary);
    }

    public static function random_token(int $length = 32): string
    {
        self::elog(__METHOD__);

        $random_base64 = base64_encode(random_bytes($length));
        $random_base64 = str_replace(['+', '/', '='], '', $random_base64);

        if (strlen($random_base64) < $length)
        {
            return self::random_token($length);
        }

        return substr($random_base64, 0, $length);
    }
}
