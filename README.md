# 🧾 Split Bill Website
**Kelompok 5 SI4803 — Telkom University 2026**

| Nama | NIM | Role |
|---|---|---|
| Raynanda Putra Wijaya | 102022400075 | Project Manager |
| Ayya Fitriana Nafik | 102022400216 | System Analyst |
| Ridanar Permana Putra | 102022400266 | Website Developer |
| Aron Ernesto Salomon Siregar | 102022400357 | Quality Assurance |

---

## ⚡ Quick Setup (Lokal)

### Requirements
- PHP 8.3+
- Composer
- MySQL 8.0+
- Node.js 18+

### 1. Clone & Install

```bash
git clone <repo-url> splitbill
cd splitbill

composer install
npm install
```

### 2. Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`:
```
DB_DATABASE=splitbill
DB_USERNAME=root
DB_PASSWORD=your_password

# Opsional — untuk fitur OCR:
GOOGLE_CLOUD_VISION_API_KEY=your_key
```

### 3. Database

```bash
# Buat database
mysql -u root -p -e "CREATE DATABASE splitbill;"

# Migrate + seed demo data
php artisan migrate --seed
```

### 4. Storage & Run

```bash
php artisan storage:link

# Di terminal terpisah:
npm run dev

# Server:
php artisan serve
```

Buka **http://localhost:8000**

Login demo: `ayya@demo.com` / `password123`

---

## 🏗️ Tech Stack

| Layer | Teknologi |
|---|---|
| Backend | Laravel 11 (PHP 8.3) |
| Auth | Laravel Sanctum |
| Frontend | Livewire 3 + Alpine.js + Tailwind CSS |
| Database | MySQL 8 + Redis (queue) |
| OCR | Google Vision API (via Queue Job) |
| PDF Export | Laravel DomPDF |
| Testing | PestPHP |

---

## 🗂️ Struktur Fitur

| Fitur | PIC | File Utama |
|---|---|---|
| User & Group Management | Raynanda | `GroupController`, `AuthController` |
| Bill & Item CRUD | Ayya | `BillController` |
| OCR Pipeline & Split Calc | Ridanar | `ScanController`, `SplitCalculatorService`, `OcrParserService` |
| Share, Export & History | Aron | `ShareController`, `ExportController` |

---

## 🧪 Testing

```bash
php artisan test
# atau
./vendor/bin/pest
```

Tests ada di:
- `tests/Unit/SplitCalculatorTest.php` — unit test kalkulasi
- `tests/Unit/OcrParserTest.php` — unit test parser OCR
- `tests/Feature/SplitBillTest.php` — feature test end-to-end

---

## 🚀 Deploy ke Laravel Cloud

1. Push ke GitHub
2. Daftar di [cloud.laravel.com](https://cloud.laravel.com)
3. New Project → connect repo
4. Set environment variables (DB, APP_KEY, GOOGLE_VISION_API_KEY)
5. Enable **Worker** untuk Queue (OCR jobs)
6. Deploy → dapat URL publik dengan HTTPS

---

## 📋 Cara Pakai

1. **Register / Login**
2. **Buat Grup** — undang teman via email
3. **Buat Tagihan** di dalam grup
4. **Tambah Item** manual, atau **Upload Foto Struk** (OCR)
5. **Assign item** ke masing-masing peserta
6. Klik **Hitung Split** — pilih Proporsional atau Merata
7. **Generate Link** untuk dibagikan via WhatsApp
8. **Export PDF** untuk dokumentasi

---

## 🔑 Endpoints Utama

```
GET    /dashboard
GET    /groups
POST   /groups
GET    /groups/{id}
POST   /groups/{id}/bills
GET    /bills/{id}
POST   /bills/{id}/items
POST   /bills/{id}/scan           ← Upload struk
GET    /bills/{id}/scan/{id}/status ← Poll OCR status
POST   /bills/{id}/calculate      ← Hitung split
POST   /bills/{id}/share          ← Generate share link
GET    /s/{token}                 ← Public share page
GET    /bills/{id}/export/pdf     ← Download PDF
```
