# YIID MoM AI Microservice

Microservice berbasis **FastAPI** untuk merapikan catatan meeting kasar dan mentranskrip rekaman suara rapat (.mp3, .m4a, .wav, .txt) menjadi dokumen Minutes of Meeting (MoM) terstruktur menggunakan **Google Gemini AI**.

Microservice ini terhubung langsung dengan backend **Laravel YIID MoM Assistant** untuk pengisian form live editor dan ekspor ke template resmi `YIID MoM.xlsx`.

---

## 1. Setup & Konfigurasi

### A. Salin File Environment
Masuk ke direktori `services/ai/` atau gunakan direktori root:
```bash
cp services/ai/.env.example services/ai/.env
```

### B. Isi `VLM_API_KEY`
Buka file `services/ai/.env` dan masukkan API Key Gemini Anda:
```env
# Gemini API Key dari Google AI Studio (https://aistudio.google.com/)
VLM_API_KEY="AIzaSy..."
VLM_MODEL=gemini-1.5-flash
VLM_TIMEOUT=60
PORT=8001
HOST=0.0.0.0
```

---

## 2. Cara Menjalankan Microservice

### Dari Root Proyek:
```bash
# Menggunakan uvicorn
uvicorn main:app --host 0.0.0.0 --port 8001 --reload

# Atau menggunakan python main.py
python main.py
```

### Dari Direktori `services/ai/`:
```bash
cd services/ai
uvicorn main:app --host 0.0.0.0 --port 8001 --reload
```

---

## 3. Endpoint API

- `GET /health` : Status kesehatan microservice
- `POST /extract-mom` : Endpoint AI untuk mengekstrak MoM dari form `text` (catatan kasar) atau `file` (audio mp3/m4a/wav atau teks transkrip)
