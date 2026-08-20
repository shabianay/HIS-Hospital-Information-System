# HealthPro — Hospital Information System (HIS)

Sistem Informasi Rumah Sakit berbasis web yang mencakup pendaftaran & antrian, rekam medis elektronik (EMR), laboratorium, radiologi, farmasi & stok, rawat inap, IGD & triase, operasi, keuangan (billing, pengeluaran, refund, BPJS), purchasing, imunisasi, stock opname, surat kematian, hingga laporan dan audit trail.

Dibangun dengan **Laravel 12**, **Tailwind CSS**, **Alpine.js**, dan **Vite**.

## Daftar Isi

- [Fitur](#fitur)
- [Arsitektur & Teknologi](#arsitektur--teknologi)
- [Persyaratan Sistem](#persyaratan-sistem)
- [Instalasi](#instalasi)
- [Akun Demo](#akun-demo)
- [Peran & Izin (Role & Permission)](#peran--izin-role--permission)
- [Modul & Rute Utama](#modul--rute-utama)
- [Pengujian](#pengujian)
- [Struktur Proyek](#struktur-proyek)
- [Dokumentasi](#dokumentasi)
- [Lisensi](#lisensi)

## Fitur

| Area | Fitur |
|---|---|
| **Pendaftaran & Antrian** | Registrasi pasien (No. RM), janji temu per poli dengan nomor antrian otomatis, TV display antrian (`/queue-display`), antrian online via portal publik tanpa login (`/portal`), cek antrian (`/cek-antrian`) |
| **Rekam Medis (EMR)** | SOAP notes, tanda vital, diagnosis ICD-10, resep, rujukan & surat sakit, cetak PDF (rekam medis, riwayat, resep, rujukan, surat sakit), riwayat medis pasien |
| **Laboratorium** | Master pemeriksaan lab, permintaan (pending/urgent → in_progress → completed), input hasil per item (normal/abnormal), notifikasi, cetak PDF hasil |
| **Radiologi** | Master pemeriksaan radiologi, permintaan + item per tes, input hasil (findings/impression), notifikasi, cetak PDF hasil |
| **Farmasi & Stok** | Master obat, antrian resep & dispense, stok per batch & expiry, mutasi stok (in/out/adjust/retur), kartu stok, alert low stock / reorder / expiring |
| **Rawat Inap** | Master kamar & tempat tidur, admission (pilih kamar/bed), discharge (bed otomatis kosong) |
| **IGD & Triase** | Registrasi IGD, triase (red/yellow/green/black), alur status waiting → in_triage → treatment → observation → discharged / referred / admitted / deceased |
| **Operasi & Bedah** | Jadwal operasi dengan prosedur ICD-9-CM, alur scheduled → in_progress → completed / cancelled |
| **Keuangan** | Billing per kunjungan, multi-payment (cash/card/QRIS/BPJS/insurance), pembayaran parsial, struk (receipt) + PDF, laporan harian, rekonsiliasi kas per shift, pengeluaran, refund kelebihan bayar |
| **BPJS** | SEP (aktif → dibatalkan), klaim (diajukan → disetujui/ditolak/menunggu) |
| **Purchasing** | Master supplier, purchase order (draft → ordered → received / cancelled), penerimaan barang otomatis menambah stok batch `PO-{id}` |
| **Imunisasi** | Pencatatan imunisasi (13 jenis vaksin) dengan validasi tanggal berikutnya |
| **Stock Opname** | Opname draft → approved; saat approve stok batch disesuaikan + mutasi tercatat |
| **Surat Kematian** | Pembuatan & cetak/download surat kematian |
| **Laporan & Audit** | Laporan PDF/CSV, laporan harian, audit trail semua aksi, dashboard per peran |
| **Manajemen** | Manajemen user & peran, master data (poli, jadwal, tarif, ICD-9-CM, dokter), notifikasi real-time |

## Arsitektur & Teknologi

- **Backend:** Laravel 12 (PHP `^8.2`)
- **Frontend:** Blade + Tailwind CSS + Alpine.js, dibundel dengan Vite
- **Database:** MySQL (default `his_db`), mendukung Redis untuk skala (session/queue/cache)
- **Otentikasi:** Laravel Breeze (session-based, email verification)
- **Otorisasi:** `spatie/laravel-permission` (roles & permissions)
- **PDF:** `barryvdh/laravel-dompdf`
- **Testing:** PHPUnit (suite fitur lengkap untuk semua modul)

## Persyaratan Sistem

- PHP `>= 8.2` (disarankan 8.2+)
- Composer
- Node.js `>= 20` & npm
- MySQL (atau SQLite untuk pengembangan ringan)
- Redis (opsional, untuk skala; lihat `.env.example`)

## Instalasi

```bash
# 1. Clone & masuk direktori
git clone https://github.com/shabianay/HIS-Hospital-Information-System.git his
cd his

# 2. Install dependensi backend
composer install

# 3. Buat environment & generate key
copy .env.example .env        # Windows
cp .env.example .env          # Linux/macOS
php artisan key:generate

# 4. Konfigurasi .env (sesuaikan database)
#    APP_NAME=HealthPro
#    DB_CONNECTION=mysql
#    DB_DATABASE=his_db
#    DB_USERNAME=root
#    DB_PASSWORD=

# 5. Migrasi + seed data demo (termasuk akun demo & data seluruh modul)
php artisan migrate --seed

# 6. Install dependensi frontend & build aset
npm install
npm run build

# 7. Jalankan server
php artisan serve
```

Akses aplikasi di `http://127.0.0.1:8000`. Untuk pengembangan dengan hot reload:

```bash
npm run dev
```

> **Catatan:** Jangan tinggalkan file `public/hot` saat produksi — file tersebut memaksa Vite memuat dari dev server. Gunakan `npm run build` (manifest aset sudah dikonfigurasi).

## Akun Demo

Semua password: `password`

| Peran | Email | Akses Utama |
|---|---|---|
| Admin | `admin@his.local` | Semua modul |
| Pendaftaran | `pendaftaran@his.local` | Pasien, antrian, rawat inap, surat kematian, antrian online |
| Dokter | `dokter@his.local` | Rekam medis, pasien saya, operasi, surat kematian |
| Perawat | `perawat@his.local` | Tanda vital, IGD, rawat inap, imunisasi, operasi |
| Kasir | `kasir@his.local` | Billing, pengeluaran, BPJS, refund |
| Apoteker | `apoteker@his.local` | Farmasi, purchasing, stock opname |
| Staf Lab | `lab@his.local` | Laboratorium & radiologi |

Data demo yang di-seed mencakup pasien, poli, dokter, jadwal, obat + stok batch, tarif, ICD-10, ICD-9-CM, pemeriksaan lab/radiologi, supplier, kamar & tempat tidur, serta alur klinis lengkap (janji temu → rekam medis → lab/radiologi → billing/pembayaran/refund → rawat inap/IGD/operasi → BPJS → imunisasi → stock opname → purchasing → surat kematian).

## Peran & Izin (Role & Permission)

Didefinisikan di `database/seeders/DatabaseSeeder.php`:

- **admin** — semua izin.
- **registration** — `manage-patients`, `manage-appointments`, `manage-inpatient`, `manage-death-certificate`, `manage-online-registration`, `view-dashboard`.
- **doctor** — `manage-emr`, `manage-surgery`, `manage-death-certificate`, `view-dashboard`.
- **nurse** — `manage-emr`, `input-vital-signs`, `manage-inpatient`, `manage-igd`, `manage-surgery`, `manage-immunization`, `view-dashboard`.
- **pharmacist** — `manage-pharmacy`, `manage-purchasing`, `manage-stock-opname`, `view-dashboard`.
- **cashier** — `manage-billing`, `manage-finance`, `manage-bpjs`, `view-dashboard`.
- **lab_tech** — `manage-lab`, `manage-radiology`, `view-dashboard`.

Menu sidebar disaring otomatis berdasarkan role & permission pengguna.

## Modul & Rute Utama

Beberapa rute utama (daftar lengkap: `php artisan route:list`):

| Modul | Rute |
|---|---|
| Dashboard | `GET /dashboard` |
| Antrian hari ini | `GET /appointments/queue` |
| Pasien | `GET /patients` |
| Display antrian (poli/lab/farmasi) | `GET /queue-display` · `/queue-display/lab` · `/queue-display/pharmacy` |
| Portal antrian online (publik) | `GET /portal` |
| Rekam medis | `GET /medical-records` |
| Laboratorium | `GET /lab/requests` · `GET /lab/tests` |
| Radiologi | `GET /radiology/requests` · `GET /radiology/tests` |
| Farmasi | `GET /pharmacy/pending` · `GET /medicines` · `GET /medicines-stock` |
| Rawat inap | `GET /inpatient-admissions` · `GET /rooms` · `GET /beds` |
| IGD | `GET /emergency` |
| Operasi | `GET /surgeries` |
| Billing & keuangan | `GET /billings` · `GET /billing/daily-report` · `GET /billing/cash-reconciliation` · `GET /expenses` · `GET /refunds` |
| BPJS | `GET /bpjs` |
| Purchasing | `GET /purchasing/orders` · `GET /purchasing/suppliers` |
| Imunisasi | `GET /immunizations` |
| Stock opname | `GET /stock-opname` |
| Surat kematian | `GET /death-certificates` |
| Laporan | `GET /reports` (PDF/CSV) |
| Admin | `GET /users` · `GET /audit` · `GET /notifications` |

## Pengujian

```bash
php artisan test
```

Suite berisi 245 test (1.021 assertions) mencakup akses per role, sidebar menu, CRUD, alur status tiap modul, permission, dan ekspor CSV.

## Struktur Proyek

```
app/
├── Http/Controllers/     # Controller per modul (REST + fitur khusus)
├── Models/               # 44 model Eloquent
└── View/Components/      # Komponen Blade (guest-layout, sidebar, dll.)
database/
├── migrations/           # 47 migrasi (skema lengkap)
└── seeders/              # Seeder roles, akun demo, dan data seluruh modul
resources/
└── views/                # Blade: layouts, components, & halaman per modul
routes/
└── web.php               # Semua rute aplikasi (auth + verified)
tests/                    # Feature tests per modul
```

## Dokumentasi

- [Alur Bisnis (Business Flow)](docs/business-flow.md)

## Lisensi

Proyek ini berlisensi [MIT](https://opensource.org/licenses/MIT).