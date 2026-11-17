<?php
$error = $error ?? null;
$name = $name ?? '';
$email = $email ?? '';
?>
<section aria-labelledby="register-title">
    <h1 id="register-title">Buat akun</h1>

    <?php if ($error !== null): ?>
        <p role="alert"><?= \App\Core\View::e($error) ?></p>
    <?php endif; ?>

    <form method="post" action="index.php?page=register">
        <div>
            <label for="name">Nama</label>
            <input id="name" name="name" type="text" maxlength="100" autocomplete="name"
                   value="<?= \App\Core\View::e($name) ?>" required>
        </div>
        <div>
            <label for="email">Email</label>
            <input id="email" name="email" type="email" maxlength="254" autocomplete="email"
                   value="<?= \App\Core\View::e($email) ?>" required>
        </div>
        <div>
            <label for="password">Password (minimal 8 karakter)</label>
            <input id="password" name="password" type="password" minlength="8"
                   autocomplete="new-password" required>
        </div>
        <button type="submit">Daftar</button>
    </form>

    <p>Sudah punya akun? <a href="index.php?page=login">Login</a></p>
</section>
