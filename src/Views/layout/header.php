<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>E-Book Rental</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header>
    <nav>
        <a href="index.php?page=home">Beranda</a>
        <a href="index.php?page=catalog">Katalog</a>
        <a href="index.php?page=transactions">Transaksi</a>
        <?php if (!empty($_SESSION['user_id'])): ?>
            <form method="post" action="index.php?page=logout" style="display:inline">
                <button type="submit">Logout</button>
            </form>
        <?php else: ?>
            <a href="index.php?page=login">Login</a>
            <a href="index.php?page=register">Daftar</a>
        <?php endif; ?>
    </nav>
</header>
<main>
