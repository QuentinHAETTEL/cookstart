<?php

namespace App\Core\Router;

use App\Core\HTTP\Request;
use App\Core\HTTP\Response;

class Router
{
    private $path;
    private array $routes = [];
    private array $names = [];


    public function __construct()
    {
        if (isset($_GET['path'])) {
            $path = $_GET['path'];
        } else {
            $path = '/';
        }
        $this->path = $path;
    }


    public function get(string $path, string $controller, string $method, ?string $name = null): Route
    {
        return $this->create($path, $controller, $method, $name, 'GET');
    }


    public function post(string $path, string $controller, string $method, ?string $name = null): Route
    {
        return $this->create($path, $controller, $method, $name, 'POST');
    }


    private function create(string $path, string $controller, string $function, ?string $name, string $method): Route
    {
        $route = new Route($path, $controller, $function);
        $this->routes[$method][] = $route;
        if ($name === null) {
            $name = ucfirst($controller).'#'.$function;
        }
        if ($name) {
            $this->names[$name] = $route;
        }

        return $route;
    }


    /**
     * @throws RouterException
     */
    public function run(): Response
    {
        $request = new Request();
        if (!isset($this->routes[$request->getMethod()])) {
            throw new RouterException('REQUEST_METHOD does not exist');
        }

        foreach ($this->routes[$request->getMethod()] as $route) {
            if ($route->match($this->path)) {
                return $route->call();
            }
        }

        $response = new Response();
        return $response->redirect404();
    }


    public function getAll(): array
    {
        return $this->names;
    }
}
