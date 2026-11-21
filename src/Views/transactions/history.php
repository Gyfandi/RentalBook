<?php
use App\Core\View;
/** @var App\Models\Transaction[] $transactions */
/** @var array<int, string> $titles */

$months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
$fmtDate = static fn (\DateTimeInterface $d): string => $d->format('d') . ' ' . $months[(int) $d->format('n') - 1] . ' ' . $d->format('Y');
$rupiah = static fn (float $n): string => 'Rp' . number_format($n, 0, ',', '.');

// Ringkasan dihitung dari data transaksi yang sudah ada (tanpa field baru)
$total = count($transactions);
$activeCount = 0;
$sumCost = 0.0;
$sumFee = 0.0;
foreach ($transactions as $t) {
    $activeCount += $t->isActive() ? 1 : 0;
    $sumCost += $t->getCost();
    $sumFee += $t->getLateFee();
}
?>
<section aria-labelledby="tx-title">
    <div class="tx-head">
        <h1 id="tx-title">Riwayat Transaksi</h1>
        <?php if ($total > 0): ?>
            <div class="tx-tools">
                <div class="chip-group" role="group" aria-label="Filter status">
                    <button type="button" class="chip is-active" data-filter="all" aria-pressed="true">Semua</button>
                    <button type="button" class="chip" data-filter="dipinjam" aria-pressed="false">Dipinjam</button>
                    <button type="button" class="chip" data-filter="dikembalikan" aria-pressed="false">Dikembalikan</button>
                </div>
                <input type="search" id="tx-search" placeholder="Cari judul media" aria-label="Cari judul media">
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($_SESSION['flash'])): ?>
        <p class="notice"><?= View::e((string) $_SESSION['flash']) ?></p>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <?php if ($total === 0): ?>
        <div class="empty-state">
            <p>Belum ada transaksi.</p>
            <a class="btn" href="index.php?page=catalog">Cari media</a>
        </div>
    <?php else: ?>
        <div class="summary-grid">
            <div class="summary-card">
                <span class="summary-label">Total Transaksi</span>
                <span class="summary-value"><?= $total ?></span>
            </div>
            <div class="summary-card">
                <span class="summary-label">Sedang Dipinjam</span>
                <span class="summary-value is-accent"><?= $activeCount ?></span>
            </div>
            <div class="summary-card">
                <span class="summary-label">Total Biaya Sewa</span>
                <span class="summary-value"><?= $rupiah($sumCost) ?></span>
            </div>
            <div class="summary-card">
                <span class="summary-label">Akumulasi Denda</span>
                <span class="summary-value <?= $sumFee > 0 ? 'is-late' : '' ?>"><?= $rupiah($sumFee) ?></span>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Media</th>
                        <th scope="col">Tanggal Pinjam</th>
                        <th scope="col">Durasi</th>
                        <th scope="col">Jatuh Tempo</th>
                        <th scope="col">Biaya</th>
                        <th scope="col">Denda</th>
                        <th scope="col">Status</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tx-rows">
                    <?php foreach ($transactions as $trx): ?>
                        <?php
                        $title = $titles[$trx->getMediaId()] ?? 'Media #' . $trx->getMediaId();
                        $overdue = $trx->isOverdue();
                        ?>
                        <tr data-status="<?= $trx->isActive() ? 'dipinjam' : 'dikembalikan' ?>" data-title="<?= View::e($title) ?>">
                            <td>TR-<?= str_pad((string) $trx->getId(), 4, '0', STR_PAD_LEFT) ?></td>
                            <td><?= View::e($title) ?></td>
                            <td><?= $fmtDate(new \DateTimeImmutable($trx->getBorrowedAt())) ?></td>
                            <td><?= $trx->getDays() ?> hari</td>
                            <td class="<?= $overdue ? 'is-late' : '' ?>"><?= $fmtDate($trx->getDueDate()) ?></td>
                            <td><?= $rupiah($trx->getCost()) ?></td>
                            <td class="<?= $trx->getLateFee() > 0 ? 'is-late' : 'is-zero' ?>"><?= $rupiah($trx->getLateFee()) ?></td>
                            <td>
                                <?php if (!$trx->isActive()): ?>
                                    <span class="badge badge-ok">Dikembalikan</span>
                                <?php elseif ($overdue): ?>
                                    <span class="badge badge-busy">Terlambat</span>
                                <?php else: ?>
                                    <span class="badge badge-busy">Dipinjam</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($trx->isActive()): ?>
                                    <form method="post" action="index.php?page=return" class="inline-form">
                                        <input type="hidden" name="id" value="<?= $trx->getId() ?>">
                                        <button type="submit" class="btn-sm" data-confirm="Kembalikan &quot;<?= View::e($title) ?>&quot; sekarang?">Kembalikan</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="tx-count" id="tx-count" aria-live="polite">Menampilkan <?= $total ?> dari <?= $total ?> transaksi</p>
        <p class="empty-state" id="tx-empty" hidden>Tidak ada transaksi yang cocok.</p>

        <aside class="info-box" aria-label="Ketentuan denda">
            <div>
                <strong>Jatuh tempo &amp; denda</strong>
                <p>Durasi sewa 1&ndash;30 hari. Keterlambatan dikenai denda Rp1.000 per hari, dihitung saat media dikembalikan.</p>
            </div>
            <a class="btn" href="index.php?page=catalog">Cari media lain</a>
        </aside>
    <?php endif; ?>
</section>