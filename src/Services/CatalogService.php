<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\JsonStorage;
use App\Models\EBook;
use App\Models\Journal;
use App\Models\Media;
use InvalidArgumentException;
use RuntimeException;

/** Anggota 2: CRUD, pencarian, dan filter katalog (data/books.json). */
class CatalogService
{
    public function __construct(private JsonStorage $books) {}

    /** @return Media[] */
    public function all(): array
    {
        return array_map(fn (array $row) => $this->toMedia($row), $this->books->all());
    }

    public function find(int $id): ?Media
    {
        $row = $this->books->find($id);
        return $row === null ? null : $this->toMedia($row);
    }

    /**
     * @param array<string, mixed> $input type, title, author, pricePerDay, format|volume
     * @throws InvalidArgumentException jika input tidak valid
     */
    public function add(array $input): Media
    {
        $media = $this->build(0, $input);
        $record = $this->books->insert($media->toArray());
        return $this->toMedia($record);
    }

    /** @param array<string, mixed> $input */
    public function update(int $id, array $input): Media
    {
        $current = $this->find($id);
        if ($current === null) {
            throw new InvalidArgumentException('Media tidak ditemukan.');
        }
        $input['type'] = $current->getType();
        $input['available'] = $current->isAvailable();
        $media = $this->build($id, $input);
        $this->books->update($id, $media->toArray());
        return $media;
    }

    public function delete(int $id): bool
    {
        return $this->books->delete($id);
    }

    /** Dipakai TransactionService (Anggota 4) saat pinjam/kembali. */
    public function setAvailability(int $id, bool $available): void
    {
        if (!$this->books->update($id, ['available' => $available])) {
            throw new InvalidArgumentException('Media tidak ditemukan.');
        }
    }

    /**
     * Cari berdasarkan kata kunci (judul/penulis) dan filter jenis/ketersediaan.
     * @return Media[]
     */
    public function search(string $keyword = '', string $type = '', bool $availableOnly = false): array
    {
        $keyword = $this->lower(trim($keyword));

        return array_values(array_filter(
            $this->all(),
            function (Media $m) use ($keyword, $type, $availableOnly): bool {
                if ($type !== '' && $m->getType() !== $type) {
                    return false;
                }
                if ($availableOnly && !$m->isAvailable()) {
                    return false;
                }
                if ($keyword === '') {
                    return true;
                }
                return str_contains($this->lower($m->getTitle()), $keyword)
                    || str_contains($this->lower($m->getAuthor()), $keyword);
            }
        ));
    }

    private function lower(string $text): string
    {
        return function_exists('mb_strtolower') ? mb_strtolower($text) : strtolower($text);
    }

    /** @param array<string, mixed> $input */
    private function build(int $id, array $input): Media
    {
        $title = (string) ($input['title'] ?? '');
        $author = (string) ($input['author'] ?? '');
        $price = $input['pricePerDay'] ?? null;
        if (!is_numeric($price)) {
            throw new InvalidArgumentException('Harga sewa per hari harus berupa angka.');
        }
        $available = (bool) ($input['available'] ?? true);

        return match ((string) ($input['type'] ?? '')) {
            'ebook' => new EBook($id, $title, $author, (float) $price, $available, (string) ($input['format'] ?? 'pdf')),
            'journal' => new Journal($id, $title, $author, (float) $price, $available, (int) ($input['volume'] ?? 1)),
            default => throw new InvalidArgumentException('Jenis media tidak dikenal.'),
        };
    }

    /** @param array<string, mixed> $row */
    private function toMedia(array $row): Media
    {
        try {
            return $this->build((int) $row['id'], $row);
        } catch (InvalidArgumentException $e) {
            throw new RuntimeException('Data di books.json tidak valid: ' . $e->getMessage());
        }
    }
}