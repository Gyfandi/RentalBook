<?php
declare(strict_types=1);

namespace App\Models;

use InvalidArgumentException;

/**
 * Inheritance: Journal extends Media.
 * Polymorphism: rentalCost() di-override, ada diskon 20% untuk sewa >= 7 hari.
 */
class Journal extends Media
{
    public const WEEKLY_DISCOUNT = 0.20;

    public function __construct(
        int $id,
        string $title,
        string $author,
        float $pricePerDay,
        bool $available = true,
        private int $volume = 1,
    ) {
        parent::__construct($id, $title, $author, $pricePerDay, $available);
        $this->setVolume($volume);
    }

    public function getType(): string { return 'journal'; }
    public function getVolume(): int { return $this->volume; }

    public function setVolume(int $volume): void
    {
        if ($volume < 1) {
            throw new InvalidArgumentException('Volume jurnal minimal 1.');
        }
        $this->volume = $volume;
    }

    public function rentalCost(int $days): float
    {
        $cost = parent::rentalCost($days);
        return $days >= 7 ? $cost * (1 - self::WEEKLY_DISCOUNT) : $cost;
    }

    protected function extraData(): array
    {
        return ['volume' => $this->volume];
    }
}