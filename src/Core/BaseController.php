<?php

namespace App\Core;

use App\Core\Renderer\TwigRenderer;
use App\Core\Security\AuthController;
use App\Core\Session\Session;
use App\Entities\User;

class BaseController
{
    const NOT_GRANTED_MESSAGE = 'Vous n\'êtes pas autorisé à accéder à cette page';


    protected Config $config;
    protected TwigRenderer $renderer;
    protected Session $session;
    protected ?User $user;


    public function __construct()
    {
        $this->config = App::getInstance()->getConfig();
        $this->renderer = new TwigRenderer(App::getInstance());
        $this->session = Session::getInstance();

        $this->user = $this->getCurrentUser();
    }


    public function getCurrentUser(): ?User
    {
        return $this->session->getSession('auth');
    }


    public function isGranted(string $role): bool
    {
        $authController = new AuthController();
        if ($authController->isAuthenticated()) {
            if ($this->user->isGranted($role)) {
                return true;
            }
        }

        return false;
    }
}
