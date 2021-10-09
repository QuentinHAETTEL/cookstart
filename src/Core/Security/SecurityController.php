<?php

namespace App\Core\Security;

use App\Core\BaseController;
use App\Core\App;
use App\Entities\User;

class SecurityController extends BaseController
{
    private ?string $salt = null;


    public function __construct()
    {
        parent::__construct();
        if ($this->salt === null) {
            $this->salt = App::getInstance()->getConfig()->get('salt');
        }
    }


    public function hashPassword(User $user): string
    {
        $password = $this->preparePassword($user, $user->getPassword());
        $parameters = ['cost' => 12];

        return password_hash($password, PASSWORD_BCRYPT, $parameters);
    }


    public function checkPassword(string $password, User $user): bool
    {
        $password = $this->preparePassword($user, $password);

        return password_verify($password, $user->getPassword());
    }


    public function createHash(?int $length): string
    {
        return md5(rand(0, $length));
    }


    private function preparePassword(User $user, string $password): string
    {
        return md5($user->getLastname()).$password.$this->salt;
    }
}
