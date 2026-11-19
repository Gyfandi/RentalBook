<?php
use App\Core\View;
?>
<section>
    <h1>Riwayat Transaksi</h1>

    <?php if (empty($transactions)): ?>
        <p>Belum ada transaksi.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Media</th>
                        <th>Tanggal Pinjam</th>
                        <th>Durasi</th>
                        <th>Jatuh Tempo</th>
                        <th>Biaya</th>
                        <th>Denda</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $trx): ?>
                        <tr>
                            <td><?= $trx->getId() ?></td>
                            <td><?= View::e($titles[$trx->getMediaId()] ?? '#' . $trx->getMediaId()) ?></td>
                            <td><?= View::e($trx->getBorrowedAt()) ?></td>
                            <td><?= $trx->getDays() ?> hari</td>
                            <td><?= $trx->getDueDate()->format('Y-m-d H:i') ?></td>
                            <td>Rp<?= number_format($trx->getCost(), 0, ',', '.') ?></td>
                            <td>Rp<?= number_format($trx->getLateFee(), 0, ',', '.') ?></td>
                            <td><?= $trx->isActive() ? 'Dipinjam' : 'Dikembalikan' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>