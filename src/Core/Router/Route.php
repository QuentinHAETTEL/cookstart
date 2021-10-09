<?php

namespace App\Core\Router;

class Route
{
    private string $path;
    private string $controller;
    private string $method;
    private array $matches = [];
    private array $parameters = [];


    public function __construct(string $path, string $controller, string $method)
    {
        $this->path = trim($path, '/');
        $this->controller = $controller;
        $this->method = $method;
    }


    public function with($parameter, $regex): Route
    {
        $this->parameters[$parameter] = str_replace('(', '(?:', $regex);
        return $this;
    }


    public function match($url): bool
    {
        $url = trim($url, '/');
        $path = preg_replace_callback('#:([a-zA-Z0-9_-]+)#', [$this, 'matchParameters'], $this->path);

        if (!preg_match("#^$path$#i", $url, $matches)) {
            return false;
        }

        array_shift($matches);
        $this->matches = $matches;

        return true;
    }


    private function matchParameters($match): string
    {
        if (isset($this->parameters[$match[1]])) {
            return '('.$this->parameters[$match[1]].')';
        }
        return '([^/]+)';
    }


    /**
     * @return mixed
     */
    public function call()
    {
        $controller = 'App\\Controllers\\'.ucfirst($this->controller).'Controller';
        $controller = new $controller();

        return call_user_func_array([$controller, $this->method], $this->matches);
    }
}
