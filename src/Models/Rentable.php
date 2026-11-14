<?php
declare(strict_types=1);

namespace App\Models;

/** Kontrak untuk item yang bisa disewa (dipakai Anggota 2 & 4). */
interface Rentable
{
    public function rentalCost(int $days): float;
    public function isAvailable(): bool;
}
