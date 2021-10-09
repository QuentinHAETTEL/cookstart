<?php

namespace App\Core\Renderer;

interface RendererInterface
{
    public function render(string $template, array $parameters = []);

    public function addGlobal(string $key, $value): void;
}
