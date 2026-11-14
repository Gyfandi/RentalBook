<?php
declare(strict_types=1);

namespace App\Models;

/** KONTRAK - dikerjakan Anggota 4. Isi sesuai kebutuhan. */
class Transaction
{
    public function __construct(
        private int $id,
        private int $userId,
        private int $mediaId,
        private string $borrowedAt,
        private int $days,
        private float $cost,
        private string $status = 'active', // active | returned
    ) {}

    public function getId(): int { return $this->id; }
    public function getStatus(): string { return $this->status; }
    public function markReturned(): void { $this->status = 'returned'; }
}
