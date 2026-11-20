<?php
$navCurrent = is_string($_GET['page'] ?? null) ? $_GET['page'] : 'home';
$navLinks = [
    'home' => 'Beranda',
    'catalog' => 'Katalog',
    'transactions' => 'Transaksi',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sistem peminjaman e-book dan jurnal berbasis PHP OOP.">
    <title>E-Book Rental</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<a class="skip-link" href="#main">Lewati ke konten utama</a>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="index.php?page=home">E-Book Rental</a>
        <button type="button" class="nav-toggle" aria-controls="site-nav" aria-expanded="false">Menu</button> <?php /* <-- BARU */ ?>
        <nav class="site-nav" id="site-nav" aria-label="Navigasi utama"> <?php /* <-- BARU: tambah id */ ?>
            <ul>
                <?php foreach ($navLinks as $navPage => $navLabel): ?>
                    <?php $navActive = $navCurrent === $navPage
                        || ($navPage === 'catalog' && str_starts_with($navCurrent, 'catalog')); ?>
                    <li>
                        <a href="index.php?page=<?= $navPage ?>"<?= $navActive ? ' aria-current="page"' : '' ?>><?= $navLabel ?></a>
                    </li>
                <?php endforeach; ?>
                <?php if (!empty($_SESSION['user_id'])): ?>
                    <li>
                        <form class="inline-form" method="post" action="index.php?page=logout">
                            <button type="submit" data-confirm="Yakin ingin keluar dari akun?">Logout</button> <?php /* <-- BARU */ ?>
                        </form>
                    </li>
                <?php else: ?>
                    <li><a href="index.php?page=login"<?= $navCurrent === 'login' ? ' aria-current="page"' : '' ?>>Login</a></li>
                    <li><a href="index.php?page=register"<?= $navCurrent === 'register' ? ' aria-current="page"' : '' ?>>Daftar</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>
<main id="main" class="container">