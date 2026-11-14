<?php
declare(strict_types=1);

require dirname(__DIR__) . '/src/autoload.php';

use App\Core\Router;
use App\Core\View;

session_start();

$router = new Router();
$router
    ->add('home', fn () => View::render('home'))
    // Tambahkan route modul lain di sini (diintegrasikan Ketua):
    // ->add('catalog', fn () => ...)
    // ->add('login', fn () => ...)
    // ->add('transactions', fn () => ...)
;

$router->dispatch($_GET['page'] ?? 'home');
