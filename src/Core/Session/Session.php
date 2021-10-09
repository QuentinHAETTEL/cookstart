<?php

namespace App\Core\Session;

use App\Core\BaseController;

class Session extends BaseController
{
    private static ?Session $instance = null;


    public function __construct()
    {
        parent::__construct();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }


    public static function getInstance(): Session
    {
        if (!self::$instance) {
            self::$instance = new Session();
        }
        return self::$instance;
    }


    /**
     * @return array|mixed|null
     */
    public function getSession(?string $index = null)
    {
        if ($index === null) {
            return $_SESSION ?? null;
        } else {
            return $_SESSION[$index] ?? null;
        }
    }


    public function setSession(string $name, $value): void
    {
        $_SESSION[$name] = $value;
    }


    public function deleteSession(?string $index = null): void
    {
        if ($index === null) {
            unset($_SESSION);
        } else {
            unset($_SESSION[$index]);
        }
    }
}
