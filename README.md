# 📝 YIID Minutes of Meeting (MoM) Assistant
### PT Yusen Logistics Interlink Indonesia

**YIID Minutes of Meeting (MoM) Assistant** adalah sistem otomasi cerdas untuk merapikan dan mentransformasikan catatan rapat berantakan (*raw/messy notes*) maupun rekaman suara meeting (*audio recording*) menjadi dokumen notulensi resmi terstruktur secara instan menggunakan kecerdasan buatan (**Google Gemini AI**), serta mengekspornya langsung ke master template spreadsheet resmi **`YIID MoM.xlsx`**.

---

## ⚡ Fitur Utama

1. **Dual Input Mode:**
   - **Catatan Teks / Transkrip:** Mendukung pengetikan atau tempel (*paste*) catatan meeting kasar, singkatan lapangan, campuran bahasa, hingga transkrip Microsoft Teams / Zoom.
   - **Rekaman Suara Rapat (Audio):** Unggah langsung berkas rekaman suara (.mp3, .m4a, .wav, .ogg, .aac, .txt) hingga ukuran 30 MB untuk didengarkan, ditranskrip, dan diekstrak oleh Gemini AI.
2. **AI Intelligent Extraction (Gemini AI Engine):**
   - Otomatis mengekstrak **Metadata Meeting** (Tanggal, Jam, Lokasi/Platform, Pemimpin Rapat/Chairman).
   - Identifikasi daftar **Peserta (Attendees)** beserta Perusahaan dan Divisi terkait.
   - Penyusunan **Agenda & Ringkasan Diskusi** secara runtut dan profesional.
   - Pemisahan tegas **Action Items (To-Do List)** lengkap dengan **PIC** (*Person in Charge*) dan target **Deadline**.
3. **Live Interactive Editor:**
   - Seluruh hasil ekstraksi AI dapat dikoreksi, ditambah, atau dihapus langsung di tampilan web sebelum diekspor.
4. **Official Excel Generator (`.xlsx`):**
   - Mengisi otomatis data hasil rapat ke template master `YIID MoM.xlsx` dengan format, font, garis batas (*borders*), dan penataan layout yang presisi.

---

## 🛠️ Tech Stack

- **Backend Web:** [Laravel 13](https://laravel.com/) (PHP ^8.3)
- **Frontend Reactive:** [Livewire 3](https://livewire.laravel.com/) & [Volt](https://livewire.laravel.com/docs/volt)
- **Styling UI:** [Tailwind CSS](https://tailwindcss.com/)
- **AI Microservice:** [FastAPI](https://fastapi.tiangolo.com/) (Python 3.10+) & [Google Gemini 1.5/2.0/3.0 Flash/Flash-Lite](https://aistudio.google.com/)
- **Spreadsheet Engine:** [PhpSpreadsheet](https://phpspreadsheet.readthedocs.io/)

---

## 💻 Prasyarat Sistem (Prerequisites)

- **PHP:** `^8.3` (dengan ekstensi `pdo`, `mbstring`, `zip`, `xml`)
- **Composer:** `^2.x`
- **Node.js & npm:** Node.js `^18.x` atau `^20.x`
- **Python:** `^3.10` atau `^3.11` (tersedia modul `pip` & `venv`)
- **Gemini API Key:** Diperoleh gratis melalui [Google AI Studio](https://aistudio.google.com/)

---

## 🚀 Panduan Instalasi (Installation)

### 1. Clone Repositori & Pasang Dependensi

```bash
git clone https://github.com/rhnzaldi/yiid-mom-assistant.git yiid-mom-assistant
cd yiid-mom-assistant

# Install dependensi PHP (Laravel)
composer install

# Install dependensi Frontend & Build aset
npm install
npm run build
```

### 2. Konfigurasi Environment Laravel

```bash
# Salin file konfigurasi environment
cp .env.example .env

# Generate Application Key
php artisan key:generate
```

### 3. Setup Python Microservice & Gemini API Key

Siapkan virtual environment Python dan pasang dependensi AI:

```bash
# Buat virtual environment (jika belum ada)
python3 -m venv .venv
source .venv/bin/activate

# Install dependensi microservice
pip install -r services/ai/requirements.txt
```

Konfigurasikan API Key Gemini pada file `services/ai/.env`:

```bash
cp services/ai/.env.example services/ai/.env
```

Buka `services/ai/.env` dan masukkan API Key Gemini Anda:
```env
VLM_API_KEY=AIzaSy...
VLM_MODEL=gemini-1.5-flash
VLM_TIMEOUT=120
PORT=8001
HOST=0.0.0.0
```

---

## 🏃 2 Langkah Mudah Menjalankan Service

Untuk menjalankan sistem secara penuh, cukup jalankan **2 terminal** berikut:

### Langkah 1: Jalankan Python MoM AI Microservice (Port 8001)

Buka terminal pertama dan jalankan service AI:
```bash
python main.py
```
*Service akan aktif dan mendengarkan request di: `http://127.0.0.1:8001`.*

### Langkah 2: Jalankan Laravel Web Server (Port 8000)

Buka terminal kedua dan jalankan aplikasi web:
```bash
php artisan serve
```
*Aplikasi MoM Assistant dapat langsung diakses melalui browser di: 👉 **`http://127.0.0.1:8000`**.*

---

## 📂 Struktur Direktori Utama

```
├── YIID MoM.xlsx                    # Master template spreadsheet resmi PT Yusen Logistics
├── app/
│   ├── Http/Controllers/
│   │   └── MoMExportController.php  # Stream download export file Excel
│   ├── Livewire/MoM/
│   │   └── MoMGenerator.php         # Logika reaktif form & integrasi API Gemini
│   ├── Models/
│   │   └── User.php                 # Model otentikasi dasar
│   └── Services/
│       └── MoMExcelExportService.php# Mesin pengisi spreadsheet PhpSpreadsheet
├── database/
│   ├── factories/UserFactory.php
│   ├── migrations/                  # Tabel dasar (users, cache, jobs, sessions)
│   └── seeders/DatabaseSeeder.php
├── resources/
│   ├── css/ & js/                   # Asset bundle Tailwind & Vite
│   └── views/
│       ├── layouts/app.blade.php    # Layout utama YIID MoM
│       └── livewire/mom/            # Template tampilan UI generator
├── services/
│   └── ai/
│       ├── main.py                  # Endpoint FastAPI AI (/extract-mom)
│       └── requirements.txt         # Library FastAPI, httpx, uvicorn
├── main.py                          # Runner root Python service
└── routes/
    ├── web.php                      # Routing utama MoM generator & export
    └── console.php
```

---

## 🧪 Menjalankan Automated Tests

Aplikasi dilengkapi pengujian unit dan integrasi otomatis (PHPUnit):

```bash
php artisan test
```

---

## 🏢 Kepemilikan & Lisensi

Dikembangkan secara khusus untuk operasional internal **PT Yusen Logistics Interlink Indonesia**. Seluruh hak cipta dilindungi undang-undang.
