<?php
declare(strict_types=1);

require dirname(__DIR__) . '/src/autoload.php';

use App\Core\Router;
use App\Core\JsonStorage;
use App\Core\View;
use App\Services\AuthService;
use App\Services\CatalogService;

session_start();

$auth = new AuthService(new JsonStorage(dirname(__DIR__) . '/data/users.json'));
$catalog = new CatalogService(new JsonStorage(dirname(__DIR__) . '/data/books.json'));
$isAdmin = static fn (): bool => $auth->currentUser()?->getRole() === 'admin';
$router = new Router();
$router
    ->add('home', fn () => View::render('home'))
    ->add('catalog', function () use ($catalog, $isAdmin): void {
        $q = (string) ($_GET['q'] ?? '');
        $type = (string) ($_GET['type'] ?? '');
        $availableOnly = !empty($_GET['available']);
        $items = $catalog->search($q, $type, $availableOnly);
        View::render('catalog/index', ['items' => $items, 'q' => $q, 'type' => $type,
            'availableOnly' => $availableOnly, 'isAdmin' => $isAdmin()]);
    })
    ->add('catalog_form', function () use ($catalog, $isAdmin): void {
        if (!$isAdmin()) {
            http_response_code(403);
            View::render('404');
            return;
        }
        $id = (int) ($_GET['id'] ?? 0);
        $editing = $id > 0;
        $media = $editing ? $catalog->find($id) : null;
        if ($editing && $media === null) {
            http_response_code(404);
            View::render('404');
            return;
        }
        $values = $media ? $media->toArray() : ['type' => 'ebook'];
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $values = $_POST + ($media ? ['type' => $media->getType()] : []);
            try {
                $editing ? $catalog->update($id, $_POST) : $catalog->add($_POST);
                $_SESSION['flash'] = 'Katalog berhasil disimpan.';
                View::redirect('catalog');
            } catch (InvalidArgumentException $exception) {
                $error = $exception->getMessage();
            }
            $values['id'] = $id;
        }

        View::render('catalog/form', compact('values', 'error', 'editing'));
    })
    ->add('catalog_delete', function () use ($catalog, $isAdmin): void {
        if (!$isAdmin() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(403);
            View::render('404');
            return;
        }
        $catalog->delete((int) ($_POST['id'] ?? 0));
        $_SESSION['flash'] = 'Media dihapus.';
        View::redirect('catalog');
    })
    ->add('register', function () use ($auth): void {
        $error = null;
        $name = '';
        $email = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = (string) ($_POST['name'] ?? '');
            $email = (string) ($_POST['email'] ?? '');
            try {
                $auth->register($name, $email, (string) ($_POST['password'] ?? ''));
                $_SESSION['flash'] = 'Pendaftaran berhasil. Silakan login.';
                View::redirect('login');
            } catch (InvalidArgumentException $exception) {
                $error = $exception->getMessage();
            }
        }

        View::render('auth/register', compact('error', 'name', 'email'));
    })
    ->add('login', function () use ($auth): void {
        $error = null;
        $email = '';
        $message = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = (string) ($_POST['email'] ?? '');
            $user = $auth->login($email, (string) ($_POST['password'] ?? ''));
            if ($user !== null) {
                View::redirect('home');
            }
            $error = 'Email atau password salah.';
        }

        View::render('auth/login', compact('error', 'email', 'message'));
    })
    ->add('logout', function () use ($auth): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            View::render('404');
            return;
        }

        $auth->logout();
        View::redirect('home');
    })
;

$router->dispatch($_GET['page'] ?? 'home');