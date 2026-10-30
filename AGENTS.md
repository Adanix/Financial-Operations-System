# AI Agent Operating Guidelines: ledger

Panduan kerja resmi untuk pengembangan AI-driven pada proyek **ledger**.

---

## 1. Parameter Runtime & Akses Proyek
- **Nama Proyek**: `ledger`
- **Container App**: `ledger-app-1`
- **Port Host**: `8810`
- **Live Preview URL**: `http://100.108.222.30:8810`
- **Database**: `laravel13_ledger` (User: `usr_ledger`)

---

## 2. Area Kerja & File Editing
- Seluruh kode aplikasi (Controller, Model, Blade view, Route, Migration) berada di dalam folder `src/`.
- Jangan mengedit file di luar `src/`.

---

## 3. Eksekusi Artisan (via Docker)
Host VPS tidak memiliki PHP lokal. Selalu jalankan Artisan via Docker container:
```bash
# Migrasi Database
docker exec ledger-app-1 php artisan migrate

# Generate Controller / Model
docker exec ledger-app-1 php artisan make:controller PostController
docker exec ledger-app-1 php artisan make:model Post -m

# Seeding & Cache Clear
docker exec ledger-app-1 php artisan db:seed
docker exec ledger-app-1 php artisan optimize:clear
```

---

## 4. Styling Tailwind CSS v4
Jika prompt menambahkan class Tailwind baru di file Blade:
```bash
/opt/adanix/bin/adanix build ledger
```

---

## 5. Automated Post-Build Inspection & Multi-Device Responsive Audit (SOP Wajib)

Setiap kali menyelesaikan pembuatan fitur atau perubahan kode, AI Agent **WAJIB** menjalankan audit otomatis 3 lapis sebelum menyatakan pekerjaan selesai:

### Lapis 1: Smart Route & Middleware Crawling

AI Agent wajib melakukan penelusuran (crawling) terhadap seluruh route aplikasi guna memastikan tidak ada broken link, missing controller method, atau exception tersembunyi:

1. **Ekstraksi Daftar Route Aplikasi**:
   Jalankan Artisan route list dari dalam container:
   ```bash
   docker exec ledger-app-1 php artisan route:list --json
   ```
   *(Abaikan route internal vendor seperti `sanctum/csrf-cookie`, `_ignition/*`, dll).*

2. **Klasifikasi Pengujian Route & Penanganan Status Code**:
   - **Route Publik (Method: `GET`, Middleware: `web` tanpa `auth`)**:
     Uji respons HTTP menggunakan `curl`:
     ```bash
     curl -s -o /dev/null -w "%{http_code}\n" http://100.108.222.30:8810/<path>
     ```
     *Standar*: Wajib menghasilkan kode **`200 OK`**. Kode `404` atau `500` adalah BUG KRITIS yang wajib diperbaiki seketika.
   - **Route Privat / Terproteksi (Method: `GET`, Middleware: `auth` / `verified`)**:
     Pengujian tanpa sesi autentikasi harus menghasilkan redirect:
     ```bash
     curl -s -I http://100.108.222.30:8810/<path> | grep -iE "(HTTP/|Location:)"
     ```
     *Standar*: Wajib menghasilkan **`302 Found`** dengan header `Location` mengarah ke login route (misal `/login`). Respons `500` adalah bug.
   - **Endpoint Aksi Form (Method: `POST`, `PUT`, `DELETE`)**:
     - Jangan menembak endpoint ini via GET tanpa payload (akan menghasilkan `405 Method Not Allowed`).
     - Jangan mengirim raw POST tanpa CSRF token (akan menghasilkan `419 Page Expired`).
     - *Audit Kode Blade*: Buka file Blade terkait form tersebut, pastikan memiliki directive `@csrf`, directive method jika bukan POST biasa (misal `@method('PUT')`), serta atribut `action="{{ route('...') }}"` mengarah ke nama route yang valid.

3. **Pengecekan Mutlak Log Laravel (Zero-Error Tolerance)**:
   Periksa log aplikasi Laravel setelah crawling:
   ```bash
   cat /opt/adanix/laravel/ledger/src/storage/logs/laravel.log | grep -iE "(ERROR|CRITICAL|EMERGENCY|Exception)" | tail -n 30
   ```
   *Standar*: Log aplikasi harus **0 Error**. Semua unhandled exception, syntax error Blade, atau query database gagal harus diselesaikan sampai tuntas.

---

### Lapis 2: Multi-Device Responsive Code Audit (Heuristik Statis Blade & Tailwind v4)

Karena VPS tidak memasang headless browser (Puppeteer/Playwright), audit responsivitas dilakukan melalui **Static Heuristic Inspection** pada file Blade (`resources/views/**/*.blade.php`) dan utilitas Tailwind CSS v4:

1. **Verifikasi Wajib Viewport Meta Tag**:
   Pastikan layout induk (`resources/views/layouts/*.blade.php` atau `app.blade.php`) memiliki:
   ```html
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   ```

2. **Matriks Standar Audit per Ukuran Layar**:
   - **Mobile Screen (Small & Large: 320px – 425px)**:
     - *Anti-Horizontal Overflow*: Dilarang menggunakan lebar fixed melebihi 320px tanpa modifier responsif (hindari `w-[500px]`, `min-w-[400px]`). Gunakan `w-full`, `max-w-xs`, `max-w-sm`. Hindari `flex` satu baris kaku; gunakan `flex-col sm:flex-row` atau `flex-wrap`.
     - *Tabel Responsif*: Seluruh elemen `<table>` **WAJIB** dibungkus kontainer scroll horizontal:
       ```html
       <div class="overflow-x-auto w-full">
           <table class="min-w-full ...">...</table>
       </div>
       ```
     - *Stacked Grid*: Grid tidak boleh langsung multi-kolom di mobile tanpa breakpoint prefix (contoh salah: `grid grid-cols-3`). Wajib mobile-first: `grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3`.
     - *Touch Targets & Form Input*: Tombol dan tautan aksi wajib memiliki area sentuh yang nyaman (minimal `py-2.5 px-4` atau `min-h-[44px]`). Input formulir pada mobile wajib selebar layar penuh (`w-full`).
   - **Tablet Screen (768px – 1024px: `md:` sampai `lg:`)**:
     - *Adaptasi Grid*: Kartu atau panel bertransisi mulus dari 1 kolom di mobile menjadi `md:grid-cols-2` atau `md:grid-cols-3`.
     - *Navigasi Responsif*: Menu navigasi horizontal desktop disembunyikan (`hidden md:flex`), dan menu burger/drawer aktif untuk ukuran layar di bawah `md:`.
   - **Desktop / Wide Screen (1280px+ : `xl:`, `2xl:`)**:
     - *Batas Kontainer Maksimal*: Konten utama wajib dibungkus kontainer pembatas agar tidak melar tak beraturan di layar ultra-wide:
       ```html
       <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
       ```
     - *Tipografi*: Paragraf panjang atau blok teks dibatasi lebarnya (`max-w-prose` atau batas kolom) agar mudah dibaca.

---

### Lapis 3: Self-Healing Workflow & Git Micro-Checkpoint

Jika selama audit ditemukan kelemahan kode atau bug:
1. **Perbaikan Mandiri Seketika (Self-Healing)**:
   AI Agent langsung mengedit file yang bermasalah di dalam `src/` tanpa menunda.
2. **Kompilasi Ulang Aset**:
   Jika perbaikan menyangkut class Tailwind CSS v4 atau aset Blade:
   ```bash
   /opt/adanix/bin/adanix build ledger
   ```
3. **Re-Verifikasi**:
   Jalankan ulang crawling HTTP dan cek log Laravel untuk memastikan 0 error.
4. **Git Checkpoint Commit**:
   Setelah seluruh checklist Lapis 1 dan Lapis 2 terpenuhi:
   ```bash
   cd /opt/adanix/laravel/ledger/src
   git add -A
   git commit -m "audit(responsive): <rincian perbaikan layout & verifikasi route>"
   ```
5. **Format Ringkasan Laporan Audit ke Pengguna**:
   Sertakan ringkasan audit terstruktur pada pesan balasan ke pengguna:
   ```markdown
   ### 📋 Laporan Post-Build Inspection & Responsive Audit
   - **Route Crawl**: [X] route diuji (Publik: 200 OK, Auth: 302 Redirect, Form: @csrf & action valid)
   - **Laravel Log**: Clean (0 error pada storage/logs/laravel.log)
   - **Responsive Check**: Mobile 320px (stacked grid, overflow-x-auto table, touch-friendly), Tablet 768px (grid adaptif), Desktop 1280px (max-w container)
   - **Self-Healing & Build**: [Perbaikan dilakukan / Clean] & adanix build executed
   - **Git Checkpoint**: <commit-hash>
   ```

---

## 6. Git Checkpoint per Fitur
Setelah seluruh checklist audit terverifikasi, lakukan commit di folder `src/`:
```bash
cd /opt/adanix/laravel/ledger/src
git add -A
git commit -m "feat(<fitur>): <penjelasan ringkas>"
```

---

## 7. Proteksi Mutlak
Dilarang keras menyentuh:
- `/opt/adanix/laravel/_golden-laravel13`
- Proyek legacy (port 8801): `ceksa`
