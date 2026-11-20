<?php
declare(strict_types=1);

namespace App\Models;

use DateTimeImmutable;

/**
 * Model transaksi peminjaman (Anggota 4).
 * Encapsulation: semua properti private, akses lewat getter.
 */
class Transaction
{
    public const ACTIVE = 'active';
    public const RETURNED = 'returned';

    public function __construct(
        private int $id,
        private int $userId,
        private int $mediaId,
        private string $borrowedAt,   // format: Y-m-d H:i:s
        private int $days,
        private float $cost,
        private string $status = self::ACTIVE,
        private ?string $returnedAt = null,
        private float $lateFee = 0.0,
    ) {}

    public function getId(): int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getMediaId(): int { return $this->mediaId; }
    public function getBorrowedAt(): string { return $this->borrowedAt; }
    public function getDays(): int { return $this->days; }
    public function getCost(): float { return $this->cost; }
    public function getStatus(): string { return $this->status; }
    public function getReturnedAt(): ?string { return $this->returnedAt; }
    public function getLateFee(): float { return $this->lateFee; }

    public function getTotal(): float
    {
        return $this->cost + $this->lateFee;
    }

    public function getDueDate(): DateTimeImmutable
    {
        return (new DateTimeImmutable($this->borrowedAt))->modify("+{$this->days} days");
    }

    public function isActive(): bool
    {
        return $this->status === self::ACTIVE;
    }

    public function isOverdue(?DateTimeImmutable $now = null): bool
    {
        $now ??= new DateTimeImmutable();
        return $this->isActive() && $now > $this->getDueDate();
    }

    public function markReturned(float $lateFee = 0.0): void
    {
        $this->status = self::RETURNED;
        $this->returnedAt = date('Y-m-d H:i:s');
        $this->lateFee = $lateFee;
    }

    /** @return array<string, mixed> untuk disimpan ke transactions.json */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'mediaId' => $this->mediaId,
            'borrowedAt' => $this->borrowedAt,
            'days' => $this->days,
            'cost' => $this->cost,
            'status' => $this->status,
            'returnedAt' => $this->returnedAt,
            'lateFee' => $this->lateFee,
        ];
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            (int) $row['id'],
            (int) $row['userId'],
            (int) $row['mediaId'],
            (string) $row['borrowedAt'],
            (int) $row['days'],
            (float) $row['cost'],
            (string) ($row['status'] ?? self::ACTIVE),
            $row['returnedAt'] ?? null,
            (float) ($row['lateFee'] ?? 0),
        );
    }
}