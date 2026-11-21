<?php
use App\Core\View;
use App\Models\EBook;
use App\Models\Media;

/** @var Media[] $featured dikirim oleh route home (3 media pertama dari katalog) */
$featured = $featured ?? [];
$loggedIn = !empty($_SESSION['user_id']);
?>
<section class="hero" aria-labelledby="hero-title">
    <div class="hero-grid">
        <div class="hero-copy">
            <span class="eyebrow">Perpustakaan Digital</span>
            <h1 id="hero-title">E-Book Rental</h1>
            <p class="hero-lead">Sewa e-book dan jurnal per hari. Cari judul, pilih durasi, dan pantau riwayat transaksimu di satu tempat.</p>
            <div class="hero-actions">
                <a class="btn" href="index.php?page=catalog">Lihat Katalog</a>
                <?php if ($loggedIn): ?>
                    <a class="btn btn-outline" href="index.php?page=transactions">Riwayat Transaksi</a>
                <?php else: ?>
                    <a class="btn btn-outline" href="index.php?page=register">Daftar Sekarang</a>
                <?php endif; ?>
            </div>

            <dl class="hero-stats">
                <div class="hero-stat">
                    <dt>Durasi sewa</dt>
                    <dd>1&ndash;30 hari</dd>
                </div>
                <div class="hero-stat">
                    <dt>Denda terlambat</dt>
                    <dd>Rp1.000 / hari</dd>
                </div>
                <div class="hero-stat">
                    <dt>Diskon jurnal</dt>
                    <dd>20% (&ge; 7 hari)</dd>
                </div>
            </dl>
        </div>

        <div class="hero-spines" aria-hidden="true">
            <div class="spine spine-1">
                <span class="spine-cat">Arsip Ilmiah</span>
                <span class="spine-title">Jurnal Komputasi &amp; Web</span>
                <span class="spine-meta">2026</span>
            </div>
            <div class="spine spine-2">
                <span class="spine-dot"></span>
                <span class="spine-title">Struktur Data Modern</span>
                <div class="spine-foot">
                    <span class="spine-rule"></span>
                    <span class="spine-cat">Edisi IV</span>
                </div>
            </div>
            <div class="spine spine-3">
                <span class="spine-cat">Kurasi</span>
                <span class="spine-title">Desain Antarmuka</span>
                <span class="spine-meta">Vol. 02</span>
            </div>
            <div class="spine spine-4">
                <span class="spine-cat">Sains Sosial</span>
                <span class="spine-title">Kritik Literasi Digital</span>
                <span class="spine-meta">P. Hasan</span>
            </div>
            <div class="spine spine-5">
                <span class="spine-cat">Monograf</span>
                <span class="spine-title">Arsitektur Informasi</span>
                <span class="spine-meta">N. 12</span>
            </div>
        </div>
    </div>
</section>

<section aria-labelledby="fitur-title">
    <span class="eyebrow">Prosedur Peminjaman</span>
    <h2 id="fitur-title">Cara kerjanya</h2>
    <div class="feature-grid">
        <article class="feature">
            <h3>1. Pilih media</h3>
            <p>Telusuri katalog e-book dan jurnal, lalu cari berdasarkan judul atau penulis.</p>
        </article>
        <article class="feature">
            <h3>2. Sewa per hari</h3>
            <p>Tentukan durasi sewa 1-30 hari. Biaya dihitung otomatis sesuai jenis media.</p>
        </article>
        <article class="feature">
            <h3>3. Kembalikan tepat waktu</h3>
            <p>Keterlambatan dikenai denda Rp1.000 per hari. Riwayat bisa dilihat kapan saja.</p>
        </article>
    </div>
</section>

<?php if ($featured !== []): ?>
<section class="ledger" aria-labelledby="ledger-title">
    <div class="ledger-head">
        <div>
            <span class="eyebrow eyebrow-accent">Koleksi Katalog</span>
            <h2 id="ledger-title" class="ledger-title">Media yang Bisa Disewa</h2>
        </div>
        <a class="ledger-link" href="index.php?page=catalog">Lihat seluruh katalog &rarr;</a>
    </div>
    <ol class="ledger-list">
        <?php foreach ($featured as $i => $m): ?>
            <?php
            $kind = $m instanceof EBook
                ? 'E-Book &bull; Format ' . strtoupper($m->getFormat())
                : 'Jurnal &bull; Vol. ' . $m->getVolume();
            ?>
            <li class="ledger-row">
                <div class="ledger-main">
                    <span class="ledger-no"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
                    <div>
                        <a class="ledger-name" href="index.php?page=catalog"><?= View::e($m->getTitle()) ?></a>
                        <span class="ledger-meta"><?= View::e($m->getAuthor()) ?> &bull; <?= $kind ?></span>
                    </div>
                </div>
                <div class="ledger-side">
                    <span class="tag <?= $m->isAvailable() ? '' : 'tag-busy' ?>"><?= $m->isAvailable() ? 'Tersedia' : 'Sedang dipinjam' ?></span>
                    <span class="ledger-price">Rp<?= number_format($m->getPricePerDay(), 0, ',', '.') ?> / hari</span>
                </div>
            </li>
        <?php endforeach; ?>
    </ol>
</section>
<?php endif; ?>