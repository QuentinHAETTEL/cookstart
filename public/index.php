<?php

require '../vendor/autoload.php';

define('ROOT', dirname(__DIR__));
$app = new App\Core\App();
$app->run();

$router = new App\Core\Router\Router();
$router->get('', 'Home', 'index');

$router->get('/register-login', 'User', 'showIndex');
$router->post('/register-login', 'User', 'getType');
$router->get('/register', 'User', 'showRegister');
$router->get('/login', 'User', 'showLogin');
$router->post('/register', 'User', 'register');
$router->get('/register/confirm', 'User', 'showConfirmAccount');
$router->post('/register/confirm', 'User', 'confirmAccount');
$router->post('/login', 'User', 'login');
$router->get('/logout', 'User', 'logout');

$router->get('/reset-password', 'User', 'resetPassword');

$router->get('/change-password', 'User', 'showChangePassword');
$router->post('/change-password', 'User', 'changePassword');

// TODO: retirer pour commit
$router->get('/ingredients', 'Ingredient', 'index');
$router->get('/ingredients/:id', 'Ingredient', 'show');

$router->run();
