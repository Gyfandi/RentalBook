<?php
declare(strict_types=1);

namespace App\Models;

/**
 * KONTRAK - dikerjakan Anggota 2.
 * Encapsulation: properti protected + getter/setter.
 */
abstract class Media implements Rentable
{
    public function __construct(
        protected int $id,
        protected string $title,
        protected string $author,
        protected float $pricePerDay,
        protected bool $available = true,
    ) {}

    public function getId(): int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getAuthor(): string { return $this->author; }
    public function isAvailable(): bool { return $this->available; }
    public function setAvailable(bool $v): void { $this->available = $v; }

    abstract public function getType(): string;

    /** Polymorphism: tiap jenis media boleh menghitung biaya berbeda. */
    public function rentalCost(int $days): float
    {
        return $this->pricePerDay * $days;
    }
}
