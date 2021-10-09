<?php

namespace App\Core;

class Config
{
    private array $settings;
    private static ?Config $instance = null;


    public function __construct($path)
    {
        $this->settings = require $path;
    }


    public static function getInstance($path): Config
    {
        if (self::$instance === null) {
            self::$instance = new Config($path);
        }
        return self::$instance;
    }


    public function get($key)
    {
        if (!isset($this->settings[$key])) {
            return null;
        }
        return $this->settings[$key];
    }
}
