# E-Book Rental - UTS Pemrograman Web

PHP 8.x OOP tanpa framework. Penyimpanan: JSON (`data/`).

## Menjalankan
    php -S localhost:8000 -t public

Buka http://localhost:8000

## Pemetaan Rubrik OOP
| Konsep | Lokasi |
|---|---|
| Namespace | `App\Core`, `App\Models`, `App\Services` |
| Encapsulation | `src/Models/Media.php`, `src/Models/User.php` (password hash private) |
| Inheritance | `EBook extends Media`, `src/Models/Member.php` dan `Admin.php` extends `User` |
| Polymorphism | `rentalCost()` per tipe |
| Interface | `src/Models/Rentable.php` |
| Autentikasi | `src/Services/AuthService.php` (register, login, logout, session, validasi, `password_hash`) |

Data akun disimpan di `data/users.json` dengan kolom `id`, `name`, `email`, `password_hash`, dan `role`. Pendaftaran publik selalu membuat role `member`.

## Pembagian
1 Ketua: arsitektur | 2 Katalog | 3 User | 4 Transaksi | 5 Front-end
