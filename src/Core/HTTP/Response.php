<?php

namespace App\Core\HTTP;

use App\Core\App;
use App\Core\Renderer\TwigRenderer;

class Response
{
    const SUCCESS_STATUS = 'SUCCESS';
    const WARNING_STATUS = 'WARNING';
    const ERROR_STATUS = 'ERROR';


    public function addHeader(string $header): void
    {
        header($header);
    }


    public function redirect(string $location): self
    {
        header('Location: '.$location);
        return $this;
    }


    public function redirectToHomepage(): self
    {
        $this->redirect(BASE_URL);
        return $this;
    }


    public function redirect404(): self
    {
        $renderer = new TwigRenderer(App::getInstance());
        $this->addHeader('HTTP/1.1 404 Not Found');
        $renderer->render('error');

        return $this;
    }


    public function jsonResponse($content, string $status): self
    {
        print json_encode([
            'status' => $status,
            'content' => $content
        ]);

        return $this;
    }
}
