<?php
use App\Core\View;
/** @var array<string, mixed> $values */
/** @var string|null $error */
/** @var bool $editing */
?>
<section>
    <h1><?= $editing ? 'Edit media' : 'Tambah media' ?></h1>
    <?php if ($error): ?><p class="error"><?= View::e($error) ?></p><?php endif; ?>

    <form method="post" action="index.php?page=catalog_form<?= $editing ? '&amp;id=' . (int) $values['id'] : '' ?>">
        <label>Jenis
            <select name="type" <?= $editing ? 'disabled' : '' ?>>
                <option value="ebook" <?= ($values['type'] ?? '') === 'ebook' ? 'selected' : '' ?>>E-Book</option>
                <option value="journal" <?= ($values['type'] ?? '') === 'journal' ? 'selected' : '' ?>>Jurnal</option>
            </select>
        </label>
        <label>Judul <input name="title" value="<?= View::e((string) ($values['title'] ?? '')) ?>" required maxlength="150"></label>
        <label>Penulis <input name="author" value="<?= View::e((string) ($values['author'] ?? '')) ?>" required maxlength="100"></label>
        <label>Harga per hari <input type="number" name="pricePerDay" min="0" step="100" value="<?= View::e((string) ($values['pricePerDay'] ?? '')) ?>" required></label>
        <label>Format (E-Book)
            <select name="format">
                <?php foreach (App\Models\EBook::FORMATS as $f): ?>
                    <option value="<?= $f ?>" <?= ($values['format'] ?? 'pdf') === $f ? 'selected' : '' ?>><?= strtoupper($f) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Volume (Jurnal) <input type="number" name="volume" min="1" value="<?= (int) ($values['volume'] ?? 1) ?>"></label>
        <button type="submit">Simpan</button>
        <a href="index.php?page=catalog">Batal</a>
    </form>
</section>