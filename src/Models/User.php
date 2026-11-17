<?php
declare(strict_types=1);

namespace App\Models;

/** Pengguna dasar; role ditentukan oleh class turunannya. */
abstract class User
{
    public function __construct(
        protected int $id,
        protected string $name,
        protected string $email,
        private string $passwordHash,
    ) {}

    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getEmail(): string { return $this->email; }

    public function verifyPassword(string $plain): bool
    {
        return password_verify($plain, $this->passwordHash);
    }

    abstract public function getRole(): string;
}
