<?php

declare(strict_types=1);
// Created 20250101 - Updated: 20250202
// Copyright (C) 2015-2020 Mark Constable <markc@renta.net> (AGPL-3.0)

namespace HCP\Plugins\Auth;

use HCP\Db;
use HCP\Util;
use HCP\Plugin;

class Model extends Plugin
{
    private const OTP_LENGTH = 10;
    private const REMEMBER_ME_EXP = 604800; // 7 days;

    protected string $tbl = 'accounts';
    protected array $in = [
        'id'        => null,
        'acl'       => null,
        'grp'       => null,
        'login'     => '',
        'webpw'     => '',
        'remember'  => '',
        'otp'       => '',
        'passwd1'   => '',
        'passwd2'   => '',
    ];

    // forgotpw
    public function create(): array
    {
        Util::elog(__METHOD__);

        $u = (string)$this->in['login'];

        if (util::is_post())
        {
            if (filter_var($u, FILTER_VALIDATE_EMAIL))
            {
                if ($usr = db::read('id,acl', 'login', $u, '', 'one'))
                {
                    if ($usr['acl'] != 9)
                    {
                        $newpass = util::genpw(self::OTP_LENGTH);
                        if ($this->mail_forgotpw($u, $newpass, 'From: ' . $this->controller->config->email))
                        {
                            db::update([
                                'otp' => $newpass,
                                'otpttl' => time()
                            ], [['id', '=', $usr['id']]]);
                            util::log('Sent reset password key for "' . $u . '" so please check your mailbox and click on the supplied link.', 'success');
                            return [
                                'status' => 'success',
                                'message' => 'Password reset email sent',
                                'redirect' => $this->controller->config->self . '?plugin=Auth&action=list'
                            ];
                        }
                        else
                        {
                            util::log('Problem sending message to ' . $u, 'danger');
                            return [
                                'status' => 'error',
                                'message' => 'Failed to send password reset email'
                            ];
                        }
                    }
                    else return [
                        'status' => 'error',
                        'message' => 'Account is disabled, contact your System Administrator'
                    ];
                }
                else return [
                    'status' => 'error',
                    'message' => 'User does not exist'
                ];
            }
            else return [
                'status' => 'error',
                'message' => 'You must provide a valid email address'
            ];
        }

        // Return form view state
        return [
            'status' => 'form',
            'message' => 'Reset password',
            'data' => ['login' => $u]
        ];
    }

    // login
    public function list(): array
    {
        Util::elog(__METHOD__);

        $u = (string)$this->in['login'];
        $p = (string)$this->in['webpw'];

        if (!empty($this->controller->config->email) && !empty($this->controller->config->admpw))
        {
            $_SESSION['usr'] = [
                'id' => 0,
                'grp' => 0,
                'acl' => 0,
                'login' => $this->controller->config->email,
                'fname' => 'Admin',
                'lname' => 'User'
            ];
            $_SESSION['adm'] = 0;
            util::log($u . ' is now logged in', 'success');
            $_SESSION['m'] = 'list';
            util::redirect($this->controller->config->self);
            return [
                'status' => 'success',
                'message' => 'Logged in successfully',
                'redirect' => true
            ];
        }

        if ($u)
        {
            if ($usr = db::read('id,grp,acl,login,fname,lname,webpw,cookie', 'login', $u, '', 'one'))
            {
                $id = (int)$usr['id'];
                $acl = (int)$usr['acl'];
                $login = (string)$usr['login'];
                $webpw = (string)$usr['webpw'];

                if ($acl !== 9)
                {
                    if (password_verify(html_entity_decode($p, ENT_QUOTES, 'UTF-8'), $webpw))
                    {
                        if ($this->in['remember'])
                        {
                            $uniq = util::random_token(32);
                            db::update(['cookie' => $uniq], [['id', '=', $id]]);
                            util::put_cookie('remember', $uniq, self::REMEMBER_ME_EXP);
                        }
                        $_SESSION['usr'] = $usr;
                        util::log($login . ' is now logged in', 'success');
                        if ($acl === 0) $_SESSION['adm'] = $id;
                        $_SESSION['m'] = 'list';
                        util::redirect($this->controller->config->self);
                        return [
                            'status' => 'success',
                            'message' => 'Logged in successfully',
                            'redirect' => true
                        ];
                    }
                    else
                    {
                        util::log('Invalid Email Or Password');
                        return [
                            'status' => 'error',
                            'message' => 'Invalid Email Or Password'
                        ];
                    }
                }
                else
                {
                    util::log('Account is disabled, contact your System Administrator');
                    return [
                        'status' => 'error',
                        'message' => 'Account is disabled'
                    ];
                }
            }
            else
            {
                util::log('Invalid Email Or Password');
                return [
                    'status' => 'error',
                    'message' => 'Invalid Email Or Password'
                ];
            }
        }

        return [
            'status' => 'form',
            'message' => 'Login',
            'data' => ['login' => $u]
        ];
    }

    // resetpw
    public function update(): array
    {
        Util::elog(__METHOD__);

        if (!(util::is_usr() || isset($_SESSION['resetpw'])))
        {
            util::log('Session expired! Please login and try again.');
            return [
                'status' => 'error',
                'message' => 'Session expired',
                'redirect' => $this->controller->config->self . '?plugin=Auth'
            ];
        }

        $i = (util::is_usr()) ? (int)$_SESSION['usr']['id'] : (int)$_SESSION['resetpw']['usr']['id'];
        $u = (util::is_usr()) ? (string)$_SESSION['usr']['login'] : (string)$_SESSION['resetpw']['usr']['login'];

        if (util::is_post())
        {
            if ($usr = db::read('login,acl,otpttl', 'id', (string)$i, '', 'one'))
            {
                $p1 = html_entity_decode($this->in['passwd1'], ENT_QUOTES, 'UTF-8');
                $p2 = html_entity_decode($this->in['passwd2'], ENT_QUOTES, 'UTF-8');
                if (util::chkpw($p1, $p2))
                {
                    if (util::is_usr() || ($usr['otpttl'] && ((int)$usr['otpttl'] + 3600) > time()))
                    {
                        if (!is_null($usr['acl']))
                        {
                            if (db::update([
                                'webpw'   => password_hash($p1, PASSWORD_DEFAULT),
                                'otp'     => '',
                                'otpttl'  => 0,
                                'updated' => date('Y-m-d H:i:s'),
                            ], [['id', '=', $i]]))
                            {
                                util::log('Password reset for ' . $usr['login'], 'success');
                                if (util::is_usr())
                                {
                                    return [
                                        'status' => 'success',
                                        'message' => 'Password updated successfully',
                                        'redirect' => $this->controller->config->self
                                    ];
                                }
                                else
                                {
                                    unset($_SESSION['resetpw']);
                                    return [
                                        'status' => 'success',
                                        'message' => 'Password reset successfully',
                                        'redirect' => $this->controller->config->self . '?plugin=Auth'
                                    ];
                                }
                            }
                            else
                            {
                                util::log('Problem updating database');
                                return [
                                    'status' => 'error',
                                    'message' => 'Database update failed'
                                ];
                            }
                        }
                        else
                        {
                            util::log($usr['login'] . ' is not allowed access');
                            return [
                                'status' => 'error',
                                'message' => 'Access denied'
                            ];
                        }
                    }
                    else
                    {
                        util::log('Your one time password key has expired');
                        return [
                            'status' => 'error',
                            'message' => 'Password reset key expired'
                        ];
                    }
                }
                return [
                    'status' => 'error',
                    'message' => 'Password validation failed'
                ];
            }
            else
            {
                util::log('User does not exist');
                return [
                    'status' => 'error',
                    'message' => 'User not found'
                ];
            }
        }

        return [
            'status' => 'form',
            'message' => 'Reset password',
            'data' => ['id' => $i, 'login' => $u]
        ];
    }

    public function delete(): array
    {
        Util::elog(__METHOD__);

        if (util::is_usr())
        {
            $u = (string)$_SESSION['usr']['login'];
            $id = (int)$_SESSION['usr']['id'];
            if (isset($_SESSION['adm']) && $_SESSION['usr']['id'] === $_SESSION['adm'])
            {
                unset($_SESSION['adm']);
            }
            unset($_SESSION['usr']);
            if (isset($_COOKIE['remember']))
            {
                db::update(['cookie' => ''], [['id', '=', $id]]);
                setcookie('remember', '', strtotime('-1 hour', 0));
            }
            util::log($u . ' is now logged out', 'success');
            return [
                'status' => 'success',
                'message' => 'Logged out successfully',
                'redirect' => $this->controller->config->self
            ];
        }

        return [
            'status' => 'error',
            'message' => 'Not logged in',
            'redirect' => $this->controller->config->self
        ];
    }

    // Utilities
    public function resetpw(): array
    {
        Util::elog(__METHOD__);

        $otp = html_entity_decode((string)$this->in['otp']);
        if (strlen($otp) === self::OTP_LENGTH)
        {
            if ($usr = db::read('id,acl,login,otp,otpttl', 'otp', $otp, '', 'one'))
            {
                $id = (int)$usr['id'];
                $acl = (int)$usr['acl'];
                $login = (string)$usr['login'];
                $otpttl = (int)$usr['otpttl'];

                if ($otpttl && (($otpttl + 3600) > time()))
                {
                    if ($acl != 3)
                    { // suspended
                        $_SESSION['resetpw'] = ['usr' => $usr];
                        return [
                            'status' => 'form',
                            'message' => 'Reset password',
                            'data' => ['id' => $id, 'login' => $login]
                        ];
                    }
                    else
                    {
                        util::log($login . ' is not allowed access');
                        return [
                            'status' => 'error',
                            'message' => 'Access denied',
                            'redirect' => $this->controller->config->self
                        ];
                    }
                }
                else
                {
                    util::log('Your one time password key has expired');
                    return [
                        'status' => 'error',
                        'message' => 'Password reset key expired',
                        'redirect' => $this->controller->config->self
                    ];
                }
            }
            else
            {
                util::log('Your one time password key no longer exists');
                return [
                    'status' => 'error',
                    'message' => 'Invalid reset key',
                    'redirect' => $this->controller->config->self
                ];
            }
        }
        else
        {
            util::log('Incorrect one time password key');
            return [
                'status' => 'error',
                'message' => 'Invalid reset key',
                'redirect' => $this->controller->config->self
            ];
        }
    }

    private function mail_forgotpw(string $email, string $newpass, string $headers = ''): bool
    {
        Util::elog(__METHOD__);

        $host = $_SERVER['REQUEST_SCHEME'] . '://'
            . $this->controller->config->host
            . $this->controller->config->self;
        return mail(
            $email,
            'Reset password for ' . $this->controller->config->host,
            'Here is your new OTP (one time password) key that is valid for one hour.

Please click on the link below and continue with reseting your password.

If you did not request this action then please ignore this message.

' . $host . '?plugin=auth&action=resetpw&otp=' . $newpass,
            $headers
        );
    }
}
