<?php

namespace App\Core;

use App\Core\Renderer\TwigRenderer;

class BaseController
{
    protected Config $config;
    protected TwigRenderer $renderer;


    public function __construct()
    {
        $this->config = App::getInstance()->getConfig();
        $this->renderer = new TwigRenderer(App::getInstance());
    }
}
