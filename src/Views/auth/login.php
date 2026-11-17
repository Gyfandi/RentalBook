<?php
$message = $message ?? null;
$error = $error ?? null;
$email = $email ?? '';
?>
<section aria-labelledby="login-title">
    <h1 id="login-title">Login</h1>

    <?php if ($message !== null): ?>
        <p role="status"><?= \App\Core\View::e((string) $message) ?></p>
    <?php endif; ?>
    <?php if ($error !== null): ?>
        <p role="alert"><?= \App\Core\View::e($error) ?></p>
    <?php endif; ?>

    <form method="post" action="index.php?page=login">
        <div>
            <label for="email">Email</label>
            <input id="email" name="email" type="email" maxlength="254" autocomplete="email"
                   value="<?= \App\Core\View::e($email) ?>" required>
        </div>
        <div>
            <label for="password">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>
        </div>
        <button type="submit">Login</button>
    </form>

    <p>Belum punya akun? <a href="index.php?page=register">Daftar</a></p>
</section>
