<?php
use App\Core\View;
/** @var App\Models\Media[] $items */
/** @var string $q */
/** @var string $type */
/** @var bool $availableOnly */
/** @var bool $isAdmin */
?>
<section>
    <h1>Katalog</h1>

    <form method="get" action="index.php" id="catalog-filter">
        <input type="hidden" name="page" value="catalog">
        <input type="search" name="q" value="<?= View::e($q) ?>" placeholder="Cari judul atau penulis">
        <select name="type">
            <option value="">Semua jenis</option>
            <option value="ebook" <?= $type === 'ebook' ? 'selected' : '' ?>>E-Book</option>
            <option value="journal" <?= $type === 'journal' ? 'selected' : '' ?>>Jurnal</option>
        </select>
        <label><input type="checkbox" name="available" value="1" <?= $availableOnly ? 'checked' : '' ?>> Tersedia saja</label>
        <button type="submit">Cari</button>
    </form>

    <?php if ($isAdmin): ?>
        <p><a href="index.php?page=catalog_form">+ Tambah media</a></p>
    <?php endif; ?>

    <?php if (!empty($_SESSION['flash'])): ?>
        <p class="notice"><?= View::e((string) $_SESSION['flash']) ?></p>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <?php if ($items === []): ?>
        <p>Tidak ada media yang cocok.</p>
    <?php else: ?>
        <div class="catalog-grid">
        <?php foreach ($items as $m): ?>
            <article class="media-card" data-type="<?= View::e($m->getType()) ?>">
                <h2><?= View::e($m->getTitle()) ?></h2>
                <p><?= View::e($m->getAuthor()) ?> &middot; <?= $m->getType() === 'ebook' ? 'E-Book' : 'Jurnal' ?></p>
                <p>Rp <?= number_format($m->getPricePerDay(), 0, ',', '.') ?> / hari</p>
                <p><?= $m->isAvailable() ? 'Tersedia' : 'Sedang dipinjam' ?></p>
                <?php if ($isAdmin): ?>
                    <a href="index.php?page=catalog_form&amp;id=<?= $m->getId() ?>">Edit</a>
                    <form method="post" action="index.php?page=catalog_delete" style="display:inline">
                        <input type="hidden" name="id" value="<?= $m->getId() ?>">
                        <button type="submit" class="js-confirm" data-confirm="Hapus media ini?">Hapus</button>
                    </form>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>