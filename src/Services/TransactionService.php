<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\JsonStorage;
use App\Models\EBook;
use App\Models\Journal;
use App\Models\Media;
use App\Models\Transaction;
use InvalidArgumentException;
use RuntimeException;

class TransactionService
{
    public const MAX_DAYS = 30;
    public const LATE_FEE_PER_DAY = 1000.0;

    public function __construct(
        private JsonStorage $transactions,
        private JsonStorage $books,
    ) {}

    /** Pinjam/sewa media. */
    public function borrow(int $userId, int $mediaId, int $days): Transaction
    {
        if ($days < 1 || $days > self::MAX_DAYS) {
            throw new InvalidArgumentException('Durasi sewa harus 1-' . self::MAX_DAYS . ' hari.');
        }

        $media = $this->loadMedia($mediaId);
        if (!$media->isAvailable()) {
            throw new RuntimeException('Media sedang tidak tersedia.');
        }

        // Polymorphism: biaya dihitung oleh objek media (tiap jenis bisa beda)
        $cost = $media->rentalCost($days);

        $row = $this->transactions->insert([
            'userId' => $userId,
            'mediaId' => $mediaId,
            'borrowedAt' => date('Y-m-d H:i:s'),
            'days' => $days,
            'cost' => $cost,
            'status' => Transaction::ACTIVE,
            'returnedAt' => null,
            'lateFee' => 0,
        ]);

        $this->books->update($mediaId, ['available' => false]);

        return Transaction::fromArray($row);
    }

    /** Kembalikan media, hitung denda kalau terlambat. */
    public function returnBook(int $transactionId): Transaction
    {
        $row = $this->transactions->find($transactionId);
        if ($row === null) {
            throw new RuntimeException('Transaksi tidak ditemukan.');
        }

        $trx = Transaction::fromArray($row);
        if (!$trx->isActive()) {
            throw new RuntimeException('Transaksi sudah dikembalikan.');
        }

        $lateFee = 0.0;
        if ($trx->isOverdue()) {
            $lateDays = (int) $trx->getDueDate()->diff(new \DateTimeImmutable())->days;
            $lateFee = max(1, $lateDays) * self::LATE_FEE_PER_DAY;
        }

        $trx->markReturned($lateFee);
        $this->transactions->update($trx->getId(), $trx->toArray());
        $this->books->update($trx->getMediaId(), ['available' => true]);

        return $trx;
    }

    /** @return Transaction[] riwayat milik satu user, terbaru di atas */
    public function history(int $userId): array
    {
        $rows = array_filter(
            $this->transactions->all(),
            fn ($r) => (int) $r['userId'] === $userId
        );
        return array_reverse(array_map([Transaction::class, 'fromArray'], array_values($rows)));
    }

    /** @return array<int, string> peta id media => judul */
    public function mediaTitles(): array
    {
        $titles = [];
        foreach ($this->books->all() as $row) {
            $titles[(int) $row['id']] = (string) $row['title'];
        }
        return $titles;
    }

    /** @return Transaction[] semua transaksi (untuk admin) */
    public function all(): array
    {
        return array_reverse(array_map([Transaction::class, 'fromArray'], $this->transactions->all()));
    }

    public function find(int $id): ?Transaction
    {
        $row = $this->transactions->find($id);
        return $row ? Transaction::fromArray($row) : null;
    }

    /** Ubah baris books.json jadi objek Media. */
    private function loadMedia(int $mediaId): Media
    {
        $row = $this->books->find($mediaId);
        if ($row === null) {
            throw new RuntimeException('Media tidak ditemukan.');
        }

        return match ($row['type']) {
            'ebook' => new EBook(
                (int) $row['id'],
                (string) $row['title'],
                (string) $row['author'],
                (float) $row['pricePerDay'],
                (bool) $row['available'],
                (string) ($row['format'] ?? 'pdf'),
            ),
            'journal' => new Journal(
                (int) $row['id'],
                (string) $row['title'],
                (string) $row['author'],
                (float) $row['pricePerDay'],
                (bool) $row['available'],
                (int) ($row['volume'] ?? 1),
            ),
            default => throw new RuntimeException("Tipe media tidak dikenal: {$row['type']}"),
        };
    }
}