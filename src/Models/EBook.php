<?php
declare(strict_types=1);

namespace App\Models;

use InvalidArgumentException;

/** Inheritance: EBook extends Media. Biaya sewa memakai tarif dasar Media. */
class EBook extends Media
{
    public const FORMATS = ['pdf', 'epub', 'mobi'];

    public function __construct(
        int $id,
        string $title,
        string $author,
        float $pricePerDay,
        bool $available = true,
        private string $format = 'pdf',
    ) {
        parent::__construct($id, $title, $author, $pricePerDay, $available);
        $this->setFormat($format);
    }

    public function getType(): string { return 'ebook'; }
    public function getFormat(): string { return $this->format; }

    public function setFormat(string $format): void
    {
        $format = strtolower(trim($format));
        if (!in_array($format, self::FORMATS, true)) {
            throw new InvalidArgumentException('Format e-book harus: ' . implode(', ', self::FORMATS) . '.');
        }
        $this->format = $format;
    }

    protected function extraData(): array
    {
        return ['format' => $this->format];
    }
}