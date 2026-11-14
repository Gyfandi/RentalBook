# E-Book Rental - UTS Pemrograman Web

PHP 8.x OOP tanpa framework. Penyimpanan: JSON (`data/`).

## Menjalankan
    php -S localhost:8000 -t public

Buka http://localhost:8000

## Pemetaan Rubrik OOP
| Konsep | Lokasi |
|---|---|
| Namespace | `App\Core`, `App\Models`, `App\Services` |
| Encapsulation | `src/Models/Media.php`, `User.php` |
| Inheritance | `EBook extends Media`, `Member/Admin extends User` |
| Polymorphism | `rentalCost()` per tipe |
| Interface | `src/Models/Rentable.php` |

## Pembagian
1 Ketua: arsitektur | 2 Katalog | 3 User | 4 Transaksi | 5 Front-end
