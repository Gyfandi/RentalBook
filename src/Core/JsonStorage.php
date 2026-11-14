<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Penyimpanan berbasis file JSON (dipakai semua Service).
 * Setiap record wajib punya key "id".
 */
class JsonStorage
{
    public function __construct(private readonly string $file)
    {
        if (!is_file($this->file)) {
            $this->save([]);
        }
    }

    /** @return array<int, array<string, mixed>> */
    public function all(): array
    {
        $raw = file_get_contents($this->file);
        $data = json_decode($raw === false ? '[]' : $raw, true);
        return is_array($data) ? $data : [];
    }

    /** @param array<int, array<string, mixed>> $records */
    public function save(array $records): void
    {
        $json = json_encode(array_values($records), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if (file_put_contents($this->file, $json, LOCK_EX) === false) {
            throw new RuntimeException("Gagal menulis {$this->file}");
        }
    }

    public function find(int $id): ?array
    {
        foreach ($this->all() as $row) {
            if ((int) $row['id'] === $id) {
                return $row;
            }
        }
        return null;
    }

    /** @param array<string, mixed> $row */
    public function insert(array $row): array
    {
        $all = $this->all();
        $ids = array_column($all, 'id');
        $row['id'] = $ids ? max($ids) + 1 : 1;
        $all[] = $row;
        $this->save($all);
        return $row;
    }

    /** @param array<string, mixed> $changes */
    public function update(int $id, array $changes): bool
    {
        $all = $this->all();
        foreach ($all as $i => $row) {
            if ((int) $row['id'] === $id) {
                $all[$i] = array_merge($row, $changes, ['id' => $id]);
                $this->save($all);
                return true;
            }
        }
        return false;
    }

    public function delete(int $id): bool
    {
        $all = $this->all();
        $filtered = array_filter($all, fn ($r) => (int) $r['id'] !== $id);
        if (count($filtered) === count($all)) {
            return false;
        }
        $this->save($filtered);
        return true;
    }
}
