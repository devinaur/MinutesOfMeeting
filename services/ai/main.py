import base64
import json
import logging
import os
import re
import io
from pathlib import Path
from typing import Any, Dict, Optional

from dotenv import load_dotenv
from fastapi import Body, FastAPI, File, Form, HTTPException, UploadFile, status
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import StreamingResponse
from pydantic import BaseModel
import httpx

_service_dir = Path(__file__).resolve().parent
_root_dir = _service_dir.parent.parent

load_dotenv(_service_dir / ".env", override=True)
load_dotenv(_root_dir / ".env", override=False)
load_dotenv(Path.cwd() / ".env", override=False)
load_dotenv()

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(name)s: %(message)s"
)
logger = logging.getLogger("mom-ai-service")

app = FastAPI(
    title="YIID MoM AI Service",
    description="Microservice for extracting Minutes of Meeting data from text notes and audio recordings using Gemini AI",
    version="1.0.0",
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

DEFAULT_VLM_MODEL = os.getenv("VLM_MODEL", "gemini-1.5-flash")
DEFAULT_VLM_TIMEOUT = int(os.getenv("VLM_TIMEOUT", "60"))

BASE_MOM_PROMPT = """Anda adalah asisten notulen rapat profesional untuk PT YUSEN LOGISTICS INTERLINK INDONESIA.
Tugas Anda adalah menganalisis input (baik berupa catatan kasar dari notes app, transkrip meeting, maupun rekaman suara meeting), lalu merapikan dan mengekstraknya ke dalam format Minutes of Meeting (MoM) resmi yang terstruktur rapi.
PENTING: Jika ada data yang tidak ditemukan dalam catatan/suara, isi dengan string kosong "" (empty string). JANGAN PERNAH menuliskan kata "null", "None", atau tanda strip "-".

ATURAN STRUKTUR & GAYA PENULISAN:
- PISAHKAN PEMBAHASAN KE DALAM SUB-TOPIK: Identifikasi setiap topik/agenda berbeda yang dibahas dalam rapat, lalu pecah menjadi beberapa sub-topik terpisah.
- Tuliskan pembahasan (discussion) secara jelas, padat, to-the-point, dan profesional (hindari kalimat yang bertele-tele atau terlalu panjang).
- Sertakan tindak lanjut (Next Action / PIC) jika ada pada masing-masing sub-topik.
- Sertakan kesimpulan penutup rapat (Conclusion) di akhir.

Aturan Penting & Guardrail Validasi:
1. VALIDASI KONTEKS RAPAT:
   - Evaluasi apakah audio atau teks ini benar-benar merupakan rekaman atau catatan rapat kerja, diskusi bisnis, percakapan operasional, atau presentasi profesional.
   - JIKA INPUT JELAS BUKAN RAPAT KERJA:
     * Set "is_meeting": false
     * Set "error_message": Berikan pesan penolakan standar yang sopan:
       - Jika bahasa Indonesia: "Konten yang dimasukkan tidak terdeteksi sebagai catatan atau rekaman rapat kerja."
       - Jika English: "The provided content was not recognized as a business meeting or notes."
     * Set "meeting_info": {}, "attendees": [], "agenda_items": []
2. JIKA INPUT VALID SEBAGAI RAPAT:
   * Set "is_meeting": true dan "error_message": "".
   * Rapikan kalimat menjadi bahasa bisnis yang profesional, jelas, dan terstruktur per topik.
   * Identifikasi tanggal (DD-MM-YYYY), jam (HH:MM 24 jam), lokasi, pemimpin rapat, dan daftar peserta beserta divisinya jika disebutkan.
3. Kembalikan HANYA format JSON valid (tanpa markdown wrapper ```json)."""

DEPARTMENT_PROMPTS = {
    "it": """STRUKTUR SPESIFIK DEPARTEMEN IT & SYSTEMS:
Rapat divisi IT berfokus pada proyek/inisiatif/pengadaan sistem. Format notulensi IT disusun berurutan per sub-topik:
1. Setiap sub-topik memiliki:
   - "title": Judul sub-topik (misal: "Integrasi WMS", "Rencana POC")
   - "discussion": Ringkasan hasil diskusi atau progres (Discussion / Progress) dalam 1-2 kalimat yang jelas dan padat
   - "next_action": Langkah tindak lanjut atau penugasan (Next Action) secara spesifik
2. Bagian "conclusion": Berada di nomor urut terakhir setelah seluruh sub-topik selesai. Berupa rangkuman paragraf penutup yang lugas, padat, dan jelas mengenai kesimpulan rapat serta status kelanjutan inisiatif/proyek (tuliskan langsung sebagai paragraf narasi penutup, bukan bullet point).

Kembalikan JSON dengan struktur persis seperti ini:
{
  "is_meeting": true,
  "error_message": "",
  "meeting_info": {
    "date": "DD-MM-YYYY",
    "time": "HH:MM",
    "location": "string",
    "chairman": "string"
  },
  "attendees": [
    {
      "name": "string",
      "company": "string ('PT Yusen Logistics Interlink Indonesia' jika internal, atau nama vendor/klien luar)",
      "division": "string"
    }
  ],
  "it_details": {
    "title": "string (Judul besar proyek / inisiatif meeting IT)",
    "sub_topics": [
      {
        "title": "string (Nama sub-topik 1)",
        "discussion": "string (Rangkuman poin diskusi & progres yang ringkas, jelas, dan padat)",
        "next_action": "string (Langkah tindak lanjut & PIC jika ada, atau kosongkan \"\" jika tidak ada)"
      },
      {
        "title": "string (Nama sub-topik 2)",
        "discussion": "string (Rangkuman poin diskusi & progres yang ringkas, jelas, dan padat)",
        "next_action": "string (Langkah tindak lanjut & PIC jika ada, atau kosongkan \"\" jika tidak ada)"
      }
    ],
    "conclusion": "string (Paragraf penutup kesimpulan rapat yang ringkas dan jelas mengenai ringkasan inisiatif dan tindak lanjut utama)"
  },
  "agenda_items": [
    {
      "no": 1,
      "topic": "string (Nama sub-topik 1)",
      "discussion": "string (Rincian Discussion / Progress untuk sub-topik 1)",
      "next_action": "string (Langkah tindak lanjut Next Action & PIC untuk sub-topik 1)"
    },
    {
      "no": 2,
      "topic": "string (Nama sub-topik 2)",
      "discussion": "string (Rincian Discussion / Progress untuk sub-topik 2)",
      "next_action": "string (Langkah tindak lanjut Next Action & PIC untuk sub-topik 2)"
    }
  ]
}""",

    "ssm": """STRUKTUR SPESIFIK STRATEGIC SALES MANAGEMENT (SSM):
Rapat divisi SSM berfokus pada strategi komersial, pipeline klien, tender/bidding logistik, margin, dan target volume.
Format notulensi disusun berurutan per sub-topik / agenda:
1. Setiap sub-topik memiliki:
   - "title": Judul sub-topik (misal: "Pipeline Akun Enterprise", "Tender Logistik Q1", "Review Margin Operasional")
   - "discussion": Ringkasan hasil diskusi komersial atau progres dalam 1-2 kalimat yang jelas dan padat
   - "next_action": Langkah tindak lanjut atau penugasan (Next Action) secara spesifik dengan PIC jika ada
2. Bagian "conclusion": Berada di nomor urut terakhir setelah seluruh sub-topik selesai. Berupa rangkuman paragraf penutup yang lugas, padat, dan jelas mengenai kesepakatan komersial serta langkah strategis berikutnya (paragraf narasi penutup, bukan bullet point).

Kembalikan JSON dengan struktur persis seperti ini:
{
  "is_meeting": true,
  "error_message": "",
  "meeting_info": {
    "date": "DD-MM-YYYY",
    "time": "HH:MM",
    "location": "string",
    "chairman": "string"
  },
  "attendees": [
    {
      "name": "string",
      "company": "string ('PT Yusen Logistics Interlink Indonesia' jika internal, atau nama vendor/klien luar)",
      "division": "string"
    }
  ],
  "it_details": {
    "title": "string (Judul pertemuan / inisiatif meeting SSM)",
    "sub_topics": [
      {
        "title": "string (Nama sub-topik 1)",
        "discussion": "string (Rangkuman poin diskusi komersial & progres yang ringkas, jelas, dan padat)",
        "next_action": "string (Langkah tindak lanjut & PIC jika ada, atau kosongkan \"\" jika tidak ada)"
      }
    ],
    "conclusion": "string (Paragraf penutup kesimpulan rapat yang ringkas dan jelas)"
  },
  "agenda_items": [
    {
      "no": 1,
      "topic": "string (Nama sub-topik 1)",
      "discussion": "string (Rincian Discussion / Progress untuk sub-topik 1)",
      "next_action": "string (Langkah tindak lanjut Next Action & PIC untuk sub-topik 1)"
    }
  ]
}""",

    "business_unit": """STRUKTUR SPESIFIK DEPARTEMEN BUSINESS UNIT / OPERASIONAL:
PENTING: Bagi pembahasan menjadi sub-topik / agenda items yang terpisah dan terstruktur secara rapi.

Kembalikan JSON dengan struktur persis seperti ini:
{
  "is_meeting": true,
  "error_message": "",
  "meeting_info": {
    "date": "DD-MM-YYYY",
    "time": "HH:MM",
    "location": "string",
    "chairman": "string"
  },
  "attendees": [
    {
      "name": "string",
      "company": "string ('PT Yusen Logistics Interlink Indonesia' jika internal, atau nama vendor/klien luar)",
      "division": "string"
    }
  ],
  "agenda_items": [
    {
      "no": 1,
      "topic": "string (Judul topik atau agenda pembahasan 1)",
      "discussion": "string (Rangkuman pembahasan untuk topik 1 yang ringkas, jelas, dan profesional)"
    },
    {
      "no": 2,
      "topic": "string (Judul topik atau agenda pembahasan 2)",
      "discussion": "string (Rangkuman pembahasan untuk topik 2 yang ringkas, jelas, dan profesional)"
    }
  ]
}"""
}


class AttendeeItem(BaseModel):
    name: Optional[str] = ""
    company: Optional[str] = "PT Yusen Logistics Interlink Indonesia"
    division: Optional[str] = ""


class ActionItem(BaseModel):
    task: Optional[str] = ""
    pic: Optional[str] = ""
    deadline: Optional[str] = ""


class AgendaItem(BaseModel):
    no: int
    topic: str
    discussion: str
    action_items: list[ActionItem] = []


class MeetingInfo(BaseModel):
    date: Optional[str] = ""
    time: Optional[str] = ""
    location: Optional[str] = ""
    chairman: Optional[str] = ""


class ITSubTopic(BaseModel):
    title: str = ""
    discussion: str = ""
    next_action: Optional[str] = ""


class ITDetails(BaseModel):
    title: str = ""
    sub_topics: list[ITSubTopic] = []
    conclusion: Optional[str] = ""


class MoMData(BaseModel):
    is_meeting: bool = True
    error_message: Optional[str] = ""
    meeting_info: Optional[MeetingInfo] = None
    attendees: list[AttendeeItem] = []
    agenda_items: list[AgendaItem] = []
    it_details: Optional[ITDetails] = None


class ExtractMoMResponse(BaseModel):
    success: bool
    data: MoMData


@app.get("/health")
async def health_check():
    return {
        "status": "healthy",
        "service": "YIID MoM AI Service",
        "model": os.getenv("VLM_MODEL", DEFAULT_VLM_MODEL),
    }


@app.post("/extract-mom", response_model=ExtractMoMResponse)
async def extract_mom(
    text: Optional[str] = Form(None),
    file: Optional[UploadFile] = File(None),
    language: Optional[str] = Form("id"),
    department: Optional[str] = Form("business_unit"),
    template_variant: Optional[str] = Form("standard")
):
    if not text and not file:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Harus menyertakan salah satu: 'text' (catatan kasar) atau 'file' (audio/dokumen)."
        )

    load_dotenv(_service_dir / ".env", override=True)
    load_dotenv(_root_dir / ".env", override=False)

    api_key = os.getenv("VLM_API_KEY", "").strip()
    if not api_key:
        raise HTTPException(
            status_code=status.HTTP_503_SERVICE_UNAVAILABLE,
            detail="VLM_API_KEY belum dikonfigurasi di services/ai/.env"
        )

    model = os.getenv("VLM_MODEL", DEFAULT_VLM_MODEL)
    timeout = int(os.getenv("VLM_TIMEOUT", str(DEFAULT_VLM_TIMEOUT)))
    endpoint_url = f"https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent?key={api_key}"

    lang_instruction = (
        "IMPORTANT: Output ALL content (topics, discussions, validation messages) in professional business English."
        if language == "en"
        else "PENTING: Tulis seluruh isi notulensi (topik, pembahasan, pesan validasi) dalam bahasa Indonesia bisnis yang formal, jelas, dan profesional."
    )

    dept_key = (department or "business_unit").lower().strip()
    variant_key = (template_variant or "standard").lower().strip()

    if dept_key == "ssm" and variant_key == "project":
        dept_instruction = DEPARTMENT_PROMPTS.get("business_unit")
    elif dept_key in DEPARTMENT_PROMPTS:
        dept_instruction = DEPARTMENT_PROMPTS[dept_key]
    elif "word" in variant_key or "docx" in variant_key:
        dept_instruction = DEPARTMENT_PROMPTS.get("it", DEPARTMENT_PROMPTS["business_unit"])
    else:
        dept_instruction = DEPARTMENT_PROMPTS.get("business_unit")

    combined_system_prompt = f"{BASE_MOM_PROMPT}\n\n{dept_instruction}"

    parts = [
        {"text": combined_system_prompt},
        {"text": lang_instruction}
    ]

    if text and text.strip():
        parts.append({"text": f"Berikut adalah catatan/transkrip meeting:\n\n{text.strip()}"})

    if file:
        content = await file.read()
        filename = file.filename or "recording.mp3"
        ext = Path(filename).suffix.lower()

        audio_mimes = {
            ".mp3": "audio/mp3",
            ".m4a": "audio/mp4",
            ".wav": "audio/wav",
            ".ogg": "audio/ogg",
            ".aac": "audio/aac",
            ".flac": "audio/flac",
            ".webm": "audio/webm",
        }

        if ext == ".txt":
            try:
                txt_content = content.decode("utf-8")
                parts.append({"text": f"Transkrip dari file {filename}:\n\n{txt_content}"})
            except Exception:
                parts.append({"text": f"Transkrip dari file {filename}:\n\n{content.decode('latin-1', errors='ignore')}"})
        elif ext in audio_mimes:
            mime = audio_mimes[ext]
            b64_data = base64.b64encode(content).decode("utf-8")
            parts.append({
                "inline_data": {
                    "mime_type": mime,
                    "data": b64_data
                }
            })
            parts.append({"text": "Tolong dengarkan dan transkrip rekaman suara meeting di atas, lalu ekstrak ke dalam schema MoM yang diminta."})
        else:
            mime = file.content_type or "application/octet-stream"
            b64_data = base64.b64encode(content).decode("utf-8")
            parts.append({
                "inline_data": {
                    "mime_type": mime,
                    "data": b64_data
                }
            })

    payload = {
        "contents": [{"parts": parts}],
        "generationConfig": {
            "temperature": 0.2,
            "response_mime_type": "application/json"
        }
    }

    try:
        async with httpx.AsyncClient(timeout=timeout) as client:
            response = await client.post(endpoint_url, json=payload)
    except httpx.TimeoutException:
        raise HTTPException(
            status_code=status.HTTP_504_GATEWAY_TIMEOUT,
            detail=f"Permintaan ke Gemini timeout setelah {timeout} detik."
        )
    except Exception as e:
        raise HTTPException(status_code=502, detail=f"Gagal memanggil Gemini API: {str(e)}")

    if response.status_code != 200:
        raise HTTPException(
            status_code=status.HTTP_502_BAD_GATEWAY,
            detail=f"Gemini API error ({response.status_code}): {response.text}"
        )

    res_json = response.json()
    try:
        candidates = res_json.get("candidates", [])
        if not candidates:
            raise ValueError("Tidak ada kandidat respon dari Gemini")
        raw_text = candidates[0]["content"]["parts"][0]["text"].strip()

        if raw_text.startswith("```"):
            raw_text = re.sub(r"^```(?:json)?\s*", "", raw_text)
            raw_text = re.sub(r"\s*```$", "", raw_text)

        parsed_data = json.loads(raw_text)
    except Exception as e:
        logger.error(f"Gagal parsing MoM response: {res_json}")
        raise HTTPException(
            status_code=status.HTTP_502_BAD_GATEWAY,
            detail=f"Gagal mem-parsing respon JSON dari Gemini: {str(e)}"
        )

    parsed_data = sanitize_null_to_empty(parsed_data)

    return {
        "success": True,
        "data": parsed_data
    }


def sanitize_null_to_empty(obj):
    if isinstance(obj, dict):
        return {k: sanitize_null_to_empty(v) for k, v in obj.items()}
    elif isinstance(obj, list):
        return [sanitize_null_to_empty(item) for item in obj]
    elif obj is None:
        return ""
    elif isinstance(obj, str):
        cleaned = obj.strip()
        if cleaned.lower() in ("null", "none", "-"):
            return ""
        return cleaned
    return obj


def generate_it_word_mom(data: dict, template_path: Optional[str] = None) -> io.BytesIO:
    import docx
    from docx.shared import Pt

    template_file = data.get("template_file") or data.get("template_path")
    target_template = template_path

    if not target_template and template_file:
        if os.path.isabs(template_file) and os.path.exists(template_file):
            target_template = template_file
        elif (_root_dir / template_file).exists():
            target_template = str(_root_dir / template_file)
        elif (Path.cwd() / template_file).exists():
            target_template = str(Path.cwd() / template_file)

    if not target_template:
        target_template = str(_root_dir / "IT MoM.docx")

    if not os.path.exists(target_template):
        alt_paths = [
            _root_dir / "templates" / "IT MoM.docx",
            Path.cwd() / "IT MoM.docx"
        ]
        for ap in alt_paths:
            if ap.exists():
                target_template = str(ap)
                break

    if not os.path.exists(target_template):
        raise FileNotFoundError(f"Template Word MoM tidak ditemukan di {target_template}")

    doc = docx.Document(target_template)

    info = data.get("meeting_info") or {}
    t0 = doc.tables[0]

    date_val = info.get("date") or ""
    time_val = info.get("time") or ""
    date_time_str = f"{date_val}  {time_val}".strip()
    if date_time_str and len(t0.rows[0].cells) > 1:
        t0.rows[0].cells[1].text = date_time_str

    if len(t0.rows[1].cells) > 1:
        t0.rows[1].cells[1].text = info.get("location") or ""

    attendees = data.get("attendees") or []
    r2_cells = t0.rows[2].cells
    for i in range(1, len(r2_cells)):
        att_idx = i - 1
        if att_idx < len(attendees):
            att = attendees[att_idx]
            name = att.get("name") or ""
            div = att.get("division") or ""
            r2_cells[i].text = f"{name} ({div})" if div else name
        else:
            r2_cells[i].text = ""

    if len(t0.rows[3].cells) > 1:
        t0.rows[3].cells[1].text = info.get("chairman") or ""

    t1 = doc.tables[1]
    cell = t1.rows[1].cells[0]

    for p in list(cell.paragraphs):
        p._element.getparent().remove(p._element)

    it_details = data.get("it_details") or {}
    title_text = it_details.get("title") or "Agenda & Pembahasan Rapat"

    p_title = cell.add_paragraph()
    r_title = p_title.add_run(title_text)
    r_title.bold = True
    r_title.font.name = "Calibri"
    r_title.font.size = Pt(12)

    sub_topics = it_details.get("sub_topics") or []
    agenda_items = data.get("agenda_items") or []

    if agenda_items:
        synced_topics = []
        for idx, item in enumerate(agenda_items):
            act = item.get("next_action") or ""
            if not act and idx < len(sub_topics):
                act = sub_topics[idx].get("next_action") or ""
            synced_topics.append({
                "title": item.get("topic") or f"Topik {idx + 1}",
                "discussion": item.get("discussion") or "",
                "next_action": act
            })
        sub_topics = synced_topics

    for idx, st in enumerate(sub_topics, 1):
        st_title = st.get("title") or f"Topik {idx}"
        p_st = cell.add_paragraph()
        p_st.paragraph_format.space_before = Pt(8)
        r_st = p_st.add_run(f"{idx}. {st_title}")
        r_st.bold = True
        r_st.font.name = "Calibri"
        r_st.font.size = Pt(12)

        p_disc_lbl = cell.add_paragraph()
        r_disc_lbl = p_disc_lbl.add_run("Discussion / Progress")
        r_disc_lbl.bold = True
        r_disc_lbl.font.name = "Calibri"
        r_disc_lbl.font.size = Pt(11)

        p_disc = cell.add_paragraph()
        r_disc = p_disc.add_run(st.get("discussion") or "")
        r_disc.font.name = "Calibri"
        r_disc.font.size = Pt(11)

        next_act = st.get("next_action")
        if next_act and next_act.strip() not in ("-", ""):
            p_act_lbl = cell.add_paragraph()
            r_act_lbl = p_act_lbl.add_run("Next Action")
            r_act_lbl.bold = True
            r_act_lbl.font.name = "Calibri"
            r_act_lbl.font.size = Pt(11)

            p_act = cell.add_paragraph()
            r_act = p_act.add_run(next_act)
            r_act.font.name = "Calibri"
            r_act.font.size = Pt(11)

    conclusion = it_details.get("conclusion")
    if conclusion and conclusion.strip():
        p_concl_lbl = cell.add_paragraph()
        p_concl_lbl.paragraph_format.space_before = Pt(8)
        r_concl_lbl = p_concl_lbl.add_run(f"{len(sub_topics) + 1}. Conclusion")
        r_concl_lbl.bold = True
        r_concl_lbl.font.name = "Calibri"
        r_concl_lbl.font.size = Pt(12)

        raw_lines = [l.strip() for l in conclusion.split("\n") if l.strip()]
        for line in raw_lines:
            clean_line = re.sub(r"^[\s•\-\*]+\s*", "", line).strip()
            if not clean_line:
                clean_line = line

            p_concl = cell.add_paragraph()
            p_concl.paragraph_format.space_before = Pt(2)
            p_concl.paragraph_format.space_after = Pt(2)
            r_concl = p_concl.add_run(clean_line)
            r_concl.font.name = "Calibri"
            r_concl.font.size = Pt(11)

    output = io.BytesIO()
    doc.save(output)
    output.seek(0)
    return output


@app.post("/export-word")
async def export_word(payload: dict = Body(...)):
    try:
        buffer = generate_it_word_mom(payload)
        return StreamingResponse(
            buffer,
            media_type="application/vnd.openxmlformats-officedocument.wordprocessingml.document",
            headers={"Content-Disposition": 'attachment; filename="MoM.docx"'}
        )
    except Exception as e:
        logger.error(f"Error exporting Word: {e}")
        raise HTTPException(status_code=500, detail=str(e))


if __name__ == "__main__":
    import uvicorn
    port = int(os.getenv("PORT", "8001"))
    host = os.getenv("HOST", "0.0.0.0")
    logger.info(f"Starting YIID MoM AI service on {host}:{port}")
    uvicorn.run(app, host=host, port=port)
