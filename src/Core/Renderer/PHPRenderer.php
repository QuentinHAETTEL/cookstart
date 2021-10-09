<?php

namespace App\Core\Renderer;

class PHPRenderer implements RendererInterface
{
    const BASE_TEMPLATE = 'base';


    private string $defaultPath;
    private array $globals = [];


    public function __construct(?string $defaultPath = ROOT.'/templates/')
    {
        if ($defaultPath !== null) {
            $this->defaultPath = $defaultPath;
        }
    }


    public function render(string $template, array $parameters = []): void
    {
        $path = $this->defaultPath.$template.'.php';

        ob_start();
        extract($this->globals);
        extract($parameters);
        require $path;
        $content = ob_get_clean();

        require $this->defaultPath.self::BASE_TEMPLATE.'.php';
    }


    public function addGlobal(string $key, $value): void
    {
        $this->globals[$key] = $value;
    }
}
