<?php

namespace App\Core\Mail;

use App\Core\App;
use App\Core\Config;
use App\Entities\User;

class MailService
{
    public const SEPARATOR = "\r\n"; // Double quote is mandatory !
    public const HELLO = 'Bonjour, '.self::SEPARATOR;
    public const PREVENTION_MESSAGE = self::SEPARATOR.self::SEPARATOR.'Si vous n\'êtes pas à l\'origine de cette action, veuillez ne pas tenir compte de cet email.';
    public const SIGNING = self::SEPARATOR.self::SEPARATOR.'L\'équipe du site';


    private Config $config;
    private ?string $appName;
    private ?string $baseUrl;


    public function __construct()
    {
        $this->config = App::getInstance()->getConfig();
        $this->appName = $this->config->get('app_name');
        $this->baseUrl = $this->config->get('base_url');
    }


    public function sendRegisterMail(array $recipients, User $user, string $token): bool
    {
        $mail = new Mail();
        $mail->setRecipients($recipients);
        $mail->setSubject(strtoupper($this->appName).' - Validation de votre inscription');
        $mail->setUser($user);

        $content = self::HELLO.
            'Suite à la création de votre compte sur '.$this->appName.
            ', veuillez cliquer sur le lien ci-dessous pour activer votre compte :'.
            self::SEPARATOR.
            $this->baseUrl.'/register/confirm?id='.$user->getId().'&token='.$token.
            self::PREVENTION_MESSAGE.self::SIGNING;
        $mail->setContent($content);

        $mailController = new MailController();
        return $mailController->sendMail($mail);
    }


    public function sendResetMail(array $recipients, User $user, string $token): bool
    {
        $mail = new Mail();
        $mail->setRecipients($recipients);
        $mail->setSubject(strtoupper($this->appName).' - Réinitialisation de votre mot de passe');
        $mail->setUser($user);

        $content = self::HELLO.
            'Suite à votre demande de réinitialisation de mot de passe, veuillez cliquer sur le lien ci-dessous pour le modifier :'.
            self::SEPARATOR.
            $this->baseUrl.'/change-password?id='.$user->getId().'&token='.$token.
            self::PREVENTION_MESSAGE.self::SIGNING;
        $mail->setContent($content);

        $mailController = new MailController();
        return $mailController->sendMail($mail);
    }
}
