<?php

namespace App\Core\Security;

use App\Core\BaseController;
use App\Core\HTTP\Request;
use App\Core\HTTP\Response;
use App\Core\Mail\MailService;
use App\Core\Renderer\RendererInterface;
use App\Core\Validator\Validator;
use App\Core\Database\QueryBuilder;
use App\Entities\User;
use Exception;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class AuthController extends BaseController
{
    private TokenController $tokenController;
    private SecurityController $securityController;
    private MailService $mailService;
    private array $messages = [
        'errorServer' => 'Une erreur est survenue, veuillez réessayer',
        'alreadyExist' => 'Cette adresse email est déjà utilisée',
        'passwordsNotCorrespond' => 'Les mots de passe saisis ne correspondent pas',
        'registerSuccess' => 'Un mail a été envoyé à l\'adresse enregistrée pour terminer l\'inscription',
        'notConfirmed' => 'Le compte n\'a pas été activé, veuillez l\'activer avant toute connexion',
        'confirmationSuccess' => 'Votre compte a bien été confirmé',
        'resetSuccess' => 'Le mot de passe a été réinitialisé, un email a été envoyé',
        'changeSuccess' => 'Le mot de passe a été modifié',
        'loginSuccess' => 'Connexion réussie',
        'logoutSuccess' => 'Déconnexion réussie',
        'wrongPassword' => 'L\'adresse email ou le mot de passe est incorrect',
        'wrongEmail' => 'L\'adresse email ou le mot de passe est incorrect',
        'wrongToken' => 'Une erreur est survenue, veuillez réessayer',
        'unknownUser' => 'Aucun compte ne correspond à cette adresse email',
        'mailSend' => 'Un email a été envoyé'
    ];


    public function __construct()
    {
        parent::__construct();
        $this->tokenController = new TokenController();
        $this->securityController = new SecurityController();
        $this->mailService = new MailService();
        $this->user = new User();
    }


    private function getEmailValidator($values): Validator
    {
        $validator = new Validator($values);
        return $validator->isRequired('email')
            ->isEmail('email');
    }


    private function getRegisterValidator($values): Validator
    {
        $validator = new Validator($values);
        return $validator->isRequired('firstname', 'lastname', 'email', 'password', 'password_confirmation')
            ->length('firstname', 1, 255)
            ->length('lastname', 1, 255)
            ->length('email', 5, 255)
            ->length('password', 8, 255)
            ->length('password_confirmation', 8, 255)
            ->isEmail('email');
    }


    private function getLoginValidator($values): Validator
    {
        $validator = new Validator($values);
        return $validator->isRequired('email', 'password')
            ->length('email', 5, 255)
            ->length('password', 8, 255)
            ->isEmail('email');
    }


    private function getChangePasswordValidator($values): Validator
    {
        $validator = new Validator($values);
        return $validator->isRequired('email', 'token', 'password', 'password_confirmation')
            ->length('email', 5, 255)
            ->length('password', 8, 255)
            ->length('password_confirmation', 8, 255)
            ->isEmail('email');
    }


    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     * @return RendererInterface|Response
     */
    public function showIndex()
    {
        if (!$this->isAuthenticated()) {
            return $this->renderer->render('security/index', ['email' => $this->session->getSession('email')]);
        } else {
            $response = new Response();
            return $response->redirectToHomepage();
        }
    }


    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     * @return RendererInterface|Response
     */
    public function showLogin()
    {
        if (!$this->isAuthenticated() && $this->session->getSession('email')) {
            $user = new User();
            $user = $user->findByEmail($this->session->getSession('email'));
            return $this->renderer->render(
                'security/login',
                ['email' => $user->getEmail(), 'firstname' => $user->getFirstname()]
            );
        } else {
            $response = new Response();
            return $response->redirect(BASE_URL.'/register-login/');
        }
    }


    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     * @return Response|RendererInterface
     */
    public function showRegister()
    {
        if (!$this->isAuthenticated()) {
            if ($this->session->getSession('email') === null) {
                $response = new Response();
                return $response->redirect(BASE_URL.'/register-login');
            } else {
                return $this->renderer->render('security/register', ['email' => $this->session->getSession('email')]);
            }
        } else {
            $response = new Response();
            return $response->redirectToHomepage();
        }
    }


    /**
     * @return RendererInterface|Response
     * @throws Exception
     */
    public function showConfirmAccount()
    {
        $request = new Request();
        $user = new User();
        $user = $user->find($request->getGetData('id'));

        if ($this->tokenController->checkToken('register')) {
            return $this->renderer->render(
                'security/confirm-account',
                ['email' => $user->getEmail(), 'firstname' => $user->getFirstname()]
            );
        } else {
            $response = new Response();
            return $response->redirect404();
        }
    }


    /**
     * @return RendererInterface|Response
     * @throws Exception
     */
    public function showChangePassword()
    {
        $request = new Request();
        $user = new User();
        $user = $user->find($request->getGetData('id'));

        if ($this->tokenController->checkToken('reset-password')) {
            return $this->renderer->render(
                'security/change-password',
                ['email' => $user->getEmail(), 'token' => trim($request->getGetData('token'), '/')]
            );
        } else {
            $response = new Response();
            return $response->redirect404();
        }
    }


    public function getType(): Response
    {
        $request = new Request();
        $validator = $this->getEmailValidator($request->getPostData());

        $response = new Response();
        if (!$validator->isValid()) {
            return $response->jsonResponse($validator->getErrors(), Response::ERROR_STATUS);
        }

        $this->session->setSession('email', $request->getPostData('email'));
        if ($this->user->exists($request->getPostData('email'))) {
            if ($this->user->isConfirmed()) {
                return $response->jsonResponse('/login', Response::SUCCESS_STATUS);
            } else {
                return $response->jsonResponse($this->messages['notConfirmed'], Response::ERROR_STATUS);
            }
        } else {
            return $response->jsonResponse('/register', Response::SUCCESS_STATUS);
        }
    }


    public function register(): Response
    {
        $request = new Request();
        $validator = $this->getRegisterValidator($request->getPostData());

        $response = new Response();
        if (!$validator->isValid()) {
            return $response->jsonResponse($validator->getErrors(), Response::ERROR_STATUS);
        }

        $this->user->hydrate($request->getPostData());
        if ($this->user->exists($this->user->getEmail())) {
            return $response->jsonResponse($this->messages['alreadyExist'], Response::ERROR_STATUS);
        }

        if ($this->user->getPassword() !== $request->getPostData('password_confirmation')) {
            return $response->jsonResponse($this->messages['passwordsNotCorrespond'], Response::ERROR_STATUS);
        }

        $q = new QueryBuilder();
        $values = [
            'lastname' => $this->user->getLastname(),
            'firstname' => $this->user->getFirstname(),
            'email' => $this->user->getEmail(),
            'password' => $this->securityController->hashPassword($this->user),
            'roles' => $this->user->getRoles()
        ];
        $result = $q->insert('users')->setValues($values)->execute(get_class($this->user));
        if ($result) {
            $this->user->setId($result);
            $token = $this->tokenController->generate($this->user, 'register');

            $response = new Response();
            if ($this->mailService->sendRegisterMail([$this->user->getEmail()], $this->user, $token)) {
                return $response->jsonResponse($this->messages['registerSuccess'], Response::SUCCESS_STATUS);
            } else {
                return $response->jsonResponse($this->messages['errorServer'], Response::ERROR_STATUS);
            }
        }

        return $response->jsonResponse($this->messages['errorServer'], Response::ERROR_STATUS);
    }


    public function confirmAccount(): Response
    {
        $request = new Request();
        $validator = $this->getLoginValidator($request->getPostData());

        $response = new Response();
        if (!$validator->isValid()) {
            return $response->jsonResponse($validator->getErrors(), Response::ERROR_STATUS);
        }

        $user = $this->user->findByEmail($request->getPostData('email'));
        if ($user) {
            if ($this->securityController->checkPassword($request->getPostData('password'), $user)) {
                $user->setConfirmedAt(date('Y-m-d H:i:s'));
                $user->addRoles(['ROLE_USER']);
                $user->setLastLogin(date('Y-m-d H:i:s'));
                $user->setNumberLogin();
                $user->save();

                $this->session->deleteSession('email');
                $user->setPassword('');
                $this->session->setSession('auth', $user);
                return $response->jsonResponse($this->messages['confirmationSuccess'], Response::SUCCESS_STATUS);
            } else {
                return $response->jsonResponse($this->messages['wrongPassword'], Response::ERROR_STATUS);
            }
        } else {
            return $response->jsonResponse($this->messages['wrongEmail'], Response::ERROR_STATUS);
        }
    }


    /**
     * @throws Exception
     */
    public function login(): Response
    {
        $request = new Request();
        $validator = $this->getLoginValidator($request->getPostData());

        $response = new Response();
        if (!$validator->isValid()) {
            return $response->jsonResponse($validator->getErrors(), Response::ERROR_STATUS);
        }

        $user = $this->user->findByEmail($request->getPostData('email'));
        if ($user) {
            if ($user->isConfirmed()) {
                if ($this->securityController->checkPassword($request->getPostData('password'), $user)) {
                    $user->setLastLogin(date('Y-m-d H:i:s'));
                    $user->setNumberLogin();
                    $user->save();

                    $this->session->deleteSession('email');
                    $user->setPassword('');
                    $this->session->setSession('auth', $user);
                    return $response->jsonResponse($this->messages['loginSuccess'], Response::SUCCESS_STATUS);
                } else {
                    return $response->jsonResponse($this->messages['wrongPassword'], Response::ERROR_STATUS);
                }
            } else {
                return $response->jsonResponse($this->messages['notConfirmed'], Response::ERROR_STATUS);
            }
        } else {
            return $response->jsonResponse($this->messages['wrongEmail'], Response::ERROR_STATUS);
        }
    }


    public function logout(): Response
    {
        $this->session->deleteSession('auth');

        $response = new Response();
        return $response->redirect(BASE_URL.'/register-login');
    }


    public function resetPassword(): Response
    {
        $user = $this->user->findActiveByEmail($this->session->getSession('email'));

        $response = new Response();
        if ($user) {
            $token = $this->tokenController->generate($user, 'reset-password');
            if ($this->mailService->sendResetMail([$user->getEmail()], $user, $token)) {
                return $response->jsonResponse($this->messages['resetSuccess'], Response::SUCCESS_STATUS);
            } else {
                return $response->jsonResponse($this->messages['errorServer'], Response::ERROR_STATUS);
            }
        } else {
            return $response->jsonResponse($this->messages['unknownUser'], Response::ERROR_STATUS);
        }
    }


    public function changePassword(): Response
    {
        $request = new Request();
        $validator = $this->getChangePasswordValidator($request->getPostData());

        $response = new Response();
        if (!$validator->isValid()) {
            return $response->jsonResponse($validator->getErrors(), Response::ERROR_STATUS);
        }

        if ($request->isPostExists()) {
            if (!$request->getPostData('token')) {
                return $response->jsonResponse($this->messages['errorServer'], Response::ERROR_STATUS);
            }

            if ($request->getPostData('password') !== $request->getPostData('password_confirmation')) {
                return $response->jsonResponse($this->messages['passwordsNotCorrespond'], Response::ERROR_STATUS);
            }

            $user = $this->user->findActiveByEmail($request->getPostData('email'));
            $user->hydrate($request->getPostData());
            $token = $this->tokenController->findActiveByType('reset-password', $user);

            if ($token) {
                if ($token->getHash() !== trim($request->getPostData('token'), '/')) {
                    return $response->jsonResponse($this->messages['errorServer'], Response::ERROR_STATUS);
                }

                $user->setPassword($this->securityController->hashPassword($user));
                $user->save();
                return $response->jsonResponse($this->messages['changeSuccess'], Response::SUCCESS_STATUS);
            } else {
                return $response->jsonResponse($this->messages['errorServer'], Response::ERROR_STATUS);
            }
        } else {
            return $response->jsonResponse($this->messages['errorServer'], Response::ERROR_STATUS);
        }
    }


    public function isAuthenticated(): bool
    {
        return $this->session->getSession('auth') !== null;
    }
}
