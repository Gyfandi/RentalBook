<?php
declare(strict_types=1);

namespace App\Models;

use InvalidArgumentException;

/**
 * Anggota 2 - Katalog media.
 *
 * Abstract class: tidak bisa di-instantiate langsung, hanya lewat turunan
 * (EBook, Journal).
 * Encapsulation: semua properti protected, akses lewat getter/setter
 * yang memvalidasi nilai.
 */
abstract class Media implements Rentable
{
    public function __construct(
        protected int $id,
        protected string $title,
        protected string $author,
        protected float $pricePerDay,
        protected bool $available = true,
    ) {
        $this->setTitle($title);
        $this->setAuthor($author);
        $this->setPricePerDay($pricePerDay);
    }

    public function getId(): int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getAuthor(): string { return $this->author; }
    public function getPricePerDay(): float { return $this->pricePerDay; }
    public function isAvailable(): bool { return $this->available; }

    public function setTitle(string $title): void
    {
        $title = trim($title);
        if ($title === '' || strlen($title) > 150) {
            throw new InvalidArgumentException('Judul wajib diisi (maksimal 150 karakter).');
        }
        $this->title = $title;
    }

    public function setAuthor(string $author): void
    {
        $author = trim($author);
        if ($author === '' || strlen($author) > 100) {
            throw new InvalidArgumentException('Penulis wajib diisi (maksimal 100 karakter).');
        }
        $this->author = $author;
    }

    public function setPricePerDay(float $price): void
    {
        if ($price < 0) {
            throw new InvalidArgumentException('Harga sewa per hari tidak boleh negatif.');
        }
        $this->pricePerDay = $price;
    }

    public function setAvailable(bool $v): void { $this->available = $v; }

    /** Jenis media: 'ebook' | 'journal'. Dipakai sebagai kolom "type" di books.json. */
    abstract public function getType(): string;

    /** Data tambahan khusus tiap jenis, ikut disimpan ke JSON. */
    abstract protected function extraData(): array;

    /** Polymorphism: tiap jenis media boleh menghitung biaya berbeda. */
    public function rentalCost(int $days): float
    {
        if ($days < 1) {
            throw new InvalidArgumentException('Durasi sewa minimal 1 hari.');
        }
        return $this->pricePerDay * $days;
    }

    /** @return array<string, mixed> format record books.json */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->getType(),
            'title' => $this->title,
            'author' => $this->author,
            'pricePerDay' => $this->pricePerDay,
            'available' => $this->available,
        ] + $this->extraData();
    }
}