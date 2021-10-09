<?php

namespace App\Core;

use App\Core\Database\Database;
use App\Core\HTTP\Request;
use App\Core\HTTP\Response;
use App\Core\Session\Session;

class App
{
    private static ?App $instance = null;
    private Config $config;
    private ?Database $db = null;


    public function __construct()
    {
        $this->config = Config::getInstance(ROOT.'/config/config.php');
    }


    public static function getInstance(): App
    {
        if (self::$instance === null) {
            self::$instance = new App();
        }
        return self::$instance;
    }


    public function run(): void
    {
        define('BASE_URL', $this->config->get('base_url'));
        date_default_timezone_set('Europe/Paris');

        Session::getInstance();

        $request = new Request();
        $url = $request->getRequestUri();
        if (!empty($url) && $url[-1] !== '/') {
            $response = new Response();
            $response->addHeader('HTTP/1.1 301 Moved Permanently');
            $response->redirect($url.'/');
        }
    }


    public function getConfig(): Config
    {
        return $this->config;
    }


    public function getDb(): Database
    {
        if ($this->db === null) {
            $this->db = new Database(
                $this->config->get('db_name'),
                $this->config->get('db_host'),
                $this->config->get('db_user'),
                $this->config->get('db_pass')
            );
        }
        return $this->db;
    }


    public function getEnv(): string
    {
        return $this->config->get('env');
    }
}
