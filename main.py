"""
Root entrypoint for YIID MoM AI Microservice.
Allows running the service directly from repository root:
    uvicorn main:app --port 8001 --reload
or:
    python main.py
"""
import importlib.util
import os
import sys
from pathlib import Path
from dotenv import load_dotenv

service_dir = Path(__file__).parent / "services" / "ai"
sys.path.insert(0, str(service_dir))

# Pre-load services/ai/.env
load_dotenv(service_dir / ".env", override=True)
load_dotenv(Path(__file__).parent / ".env", override=False)

service_main_path = service_dir / "main.py"
spec = importlib.util.spec_from_file_location("service_main", str(service_main_path))
service_main = importlib.util.module_from_spec(spec)
sys.modules["service_main"] = service_main
spec.loader.exec_module(service_main)

app = service_main.app

if __name__ == "__main__":
    import uvicorn
    port = int(os.getenv("PORT", "8001"))
    host = os.getenv("HOST", "0.0.0.0")
    print(f"Starting YIID MoM AI service on {host}:{port}")
    uvicorn.run("main:app", host=host, port=port, reload=True)
