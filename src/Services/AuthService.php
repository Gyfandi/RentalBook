<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\JsonStorage;
use App\Models\Admin;
use App\Models\Member;
use App\Models\User;
use InvalidArgumentException;
use RuntimeException;

/** Autentikasi member/admin menggunakan users.json dan PHP session. */
class AuthService
{
    public function __construct(private JsonStorage $users) {}

    /**
     * Mendaftarkan akun member. Role admin tidak dapat dipilih dari formulir publik.
     * @throws InvalidArgumentException jika input tidak valid atau email telah dipakai
     */
    public function register(string $name, string $email, string $password): Member
    {
        $name = trim($name);
        $email = strtolower(trim($email));

        $nameLength = function_exists('mb_strlen') ? mb_strlen($name) : strlen($name);
        if ($name === '' || $nameLength > 100) {
            throw new InvalidArgumentException('Nama wajib diisi (maksimal 100 karakter).');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Format email tidak valid.');
        }
        if (strlen($password) < 8) {
            throw new InvalidArgumentException('Password minimal 8 karakter.');
        }
        if (strlen($password) > 4096) {
            throw new InvalidArgumentException('Password terlalu panjang.');
        }

        foreach ($this->users->all() as $row) {
            if (isset($row['email']) && strtolower((string) $row['email']) === $email) {
                throw new InvalidArgumentException('Email sudah terdaftar.');
            }
        }

        $record = $this->users->insert([
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'member',
        ]);

        return $this->toUser($record);
    }

    /** Mengembalikan User bila berhasil, atau null untuk kredensial yang salah. */
    public function login(string $email, string $password): ?User
    {
        $this->startSession();
        $email = strtolower(trim($email));

        foreach ($this->users->all() as $row) {
            if (!isset($row['email'], $row['password_hash'])
                || strtolower((string) $row['email']) !== $email
                || !password_verify($password, (string) $row['password_hash'])) {
                continue;
            }

            $user = $this->toUser($row);
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user->getId();
            return $user;
        }

        return null;
    }

    /** Mengakhiri autentikasi dan menghapus cookie session jika digunakan. */
    public function logout(): void
    {
        $this->startSession();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax',
            ]);
        }

        session_destroy();
    }

    /** Mengambil user dari session; data role selalu dibaca ulang dari JSON. */
    public function currentUser(): ?User
    {
        $this->startSession();
        $id = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
        if ($id === false || $id === null) {
            return null;
        }

        $record = $this->users->find((int) $id);
        return $record === null ? null : $this->toUser($record);
    }

    public function isLoggedIn(): bool
    {
        return $this->currentUser() !== null;
    }

    private function toUser(array $record): User
    {
        $id = filter_var($record['id'] ?? null, FILTER_VALIDATE_INT);
        $name = trim((string) ($record['name'] ?? ''));
        $email = strtolower(trim((string) ($record['email'] ?? '')));
        $hash = (string) ($record['password_hash'] ?? '');
        $role = $record['role'] ?? 'member';

        if ($id === false || $id === null || $name === '' || $email === '' || $hash === '') {
            throw new RuntimeException('Data pengguna di users.json tidak valid.');
        }

        return match ($role) {
            'admin' => new Admin((int) $id, $name, $email, $hash),
            'member' => new Member((int) $id, $name, $email, $hash),
            default => throw new RuntimeException('Role pengguna di users.json tidak dikenal.'),
        };
    }

    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            if (headers_sent()) {
                throw new RuntimeException('Session harus dimulai sebelum output dikirim.');
            }
            session_start();
        }
    }
}
