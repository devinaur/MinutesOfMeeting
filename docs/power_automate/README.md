# Panduan Setup 2 Flow Power Automate (Text vs Audio)

Panduan ini digunakan oleh developer dan manager untuk mengonfigurasi **Microsoft Power Automate** agar terhubung dengan aplikasi **YIID Minutes of Meeting Assistant**.

---

## 1. Flow 1: Khusus Teks (`YIID-MoM-Text-Processor`)

### Langkah Pembuatan:
1. Masuk ke [make.powerautomate.com](https://make.powerautomate.com).
2. Buat **Automated Cloud Flow** baru dengan trigger: **"When an HTTP request is received"**.
   - **Method:** `POST`
   - **Who can trigger:** `Any user in my tenant` (atau `Anyone` jika diamankan dengan API key).
   - **Request Body JSON Schema:** Buka dan paste isi file `flow1_text_trigger_schema.json`.
3. Tambahkan Action: **"Create prompt"** atau **"Run a prompt with Copilot / Azure OpenAI"**.
   - Masukkan instruksi: Gunakan dynamic token `system_prompt`.
   - Masukkan input teks: Gunakan dynamic token `text_content`.
4. Tambahkan Action: **"Response"**.
   - **Status Code:** `200`
   - **Headers:**
     - Key: `Content-Type`
     - Value: `application/json`
   - **Body:** Hasil keluaran JSON dari Copilot (lihat contoh di `response_schema_success.json`).
5. **Salin HTTP POST URL** yang dihasilkan oleh trigger, lalu tempel di file `.env` Laravel:
   ```env
   POWER_AUTOMATE_TEXT_URL=https://prod-xx.southeastasia.logic.azure.com/...
   ```

---

## 2. Flow 2: Khusus Audio (`YIID-MoM-Audio-Processor`)

### Langkah Pembuatan:
1. Buat **Automated Cloud Flow** baru dengan trigger: **"When an HTTP request is received"**.
   - **Method:** `POST`
   - **Request Body JSON Schema:** Buka dan paste isi file `flow2_audio_trigger_schema.json`.
2. **Tahap 1 (Speech-to-Text):**
   - Tambahkan action konversi Base64 ke binary audio: `base64ToBinary(triggerBody()?['audio_base64'])`.
   - Kirim ke **Azure Cognitive Services Speech-to-Text** atau **Azure OpenAI Whisper**.
   - Simpan hasil transkrip ke variabel `raw_transcript`.
   - *Jika gagal (Audio korup/hening):* Kirim action **Response (Status 422)** dengan body:
     ```json
     {
       "success": false,
       "error_stage": "VOICE_TO_TEXT",
       "message": "Suara rekaman tidak dapat dikenali oleh sistem Speech-to-Text."
     }
     ```
3. **Tahap 2 (Text-to-JSON):**
   - Kirim `raw_transcript` + `system_prompt` ke **Copilot / Azure OpenAI**.
   - *Jika gagal (Token limit exceeded):* Kirim action **Response (Status 422)** dengan menyertakan `raw_transcript` (lihat `response_schema_error.json`).
4. **Tahap 3 (Response Sukses):**
   - Action **Response (Status 200)** berisi hasil MoM JSON lengkap.
5. **Salin HTTP POST URL** audio, lalu tempel di file `.env` Laravel:
   ```env
   POWER_AUTOMATE_AUDIO_URL=https://prod-yy.southeastasia.logic.azure.com/...
   ```

---

## 3. Fitur Penyelamatan Transkrip Otomatis (Zero Data Loss)
Jika rekaman audio sangat panjang sehingga Copilot gagal merangkumnya karena limit token:
1. Flow Audio akan mengembalikan `error_stage: "TEXT_TO_JSON"` beserta `raw_transcript`.
2. Aplikasi web Laravel **secara otomatis memindahkan user ke tab "Catatan Teks"** dan mengisi kotaknya dengan teks transkrip rekaman yang sudah berhasil didapat!
3. User tidak perlu merekam ulang, cukup mereview atau mengedit sedikit teksnya, lalu klik proses ulang.
