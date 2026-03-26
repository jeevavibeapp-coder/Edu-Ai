# 🎓 AI Educational Video Generator

> Generate AI-powered educational lesson videos with a single API call. Combines **Google Gemini AI** for script writing, **Edge TTS** for voiceover, **Pillow** for slide creation, and **MoviePy** for final video rendering.

---

## ✨ Key Features

| Feature | Description |
|---------|-------------|
| 🤖 **AI Script Generation** | Auto-generates lesson scripts using Gemini (paid) or HuggingFace (free) with mock fallback |
| 🎙️ **Text-to-Speech** | Subject-specific voices via Microsoft Edge TTS (no API key needed) |
| 🎨 **Auto Slide Creation** | Beautiful themed slides with example highlighting per subject |
| 🎬 **Video Rendering** | Stitches slides + audio into MP4 using MoviePy + FFmpeg |
| 📐 **Math Animations** | Optional Manim-powered animations for math topics (quadratic, geometry, calculus) |
| ⚡ **Background Processing** | Non-blocking video generation with real-time job status polling |
| 📊 **Job Tracking** | Track progress of multiple video generation jobs simultaneously |

---

## 🏗️ Architecture

```
Single File: video_generator.py (~1000 lines)
├── Configuration        → Loads from .env automatically
├── Models (Pydantic v2) → VideoGenerationRequest, JobStatus, Responses
├── Job Manager          → In-memory job tracking (upgradeable to Redis)
├── AI Services          → Gemini → HuggingFace → Mock (fallback chain)
├── TTS Service          → Edge TTS with 8 subject-specific voices
├── Slide Generator      → Pillow with color-coded themes per subject
├── Manim Service        → Math animation scenes (quadratic, geometry, calculus)
├── Video Renderer       → MoviePy concatenation + FFmpeg encoding
├── FastAPI Routes       → REST API with background task processing
└── Entry Point          → Uvicorn server with hot-reload
```

---

## 📋 Requirements

### System Requirements
- **Python 3.12** (recommended — Python 3.14 has compatibility issues with some packages)
- **FFmpeg** (bundled with MoviePy, but install separately for best results)
- **Manim + LaTeX** (optional — only needed for math animations)

### Python Dependencies
All listed in `requirements.txt`:
```
fastapi, uvicorn, pydantic, pydantic-settings, google-generativeai,
edge-tts, pillow, moviepy, manim, structlog, httpx, python-dotenv,
numpy, opencv-python, scipy
```

---

## 🚀 Installation & Setup

### 1. Clone the Repository
```bash
git clone https://github.com/jeevavibeapp-coder/Edu-Ai.git
cd Edu-Ai
```

### 2. Create Virtual Environment (Python 3.12)
```bash
# Windows
py -3.12 -m venv venv
.\venv\Scripts\activate

# Linux/Mac
python3.12 -m venv venv
source venv/bin/activate
```

### 3. Install Dependencies
```bash
pip install -r requirements.txt
```

### 4. Configure Environment Variables
Create a `.env` file in the project root:
```env
# Required — at least one AI service key
GEMINI_API_KEY=your_gemini_api_key_here
HF_API_TOKEN=your_huggingface_token_here   # Optional (free tier)

# Server Settings
DEBUG=true
HOST=0.0.0.0
PORT=8000

# Video Settings
VIDEO_WIDTH=1280
VIDEO_HEIGHT=720
VIDEO_FPS=24
```

> **Note:** If no API keys are set, the app will use a built-in mock script generator as fallback.

### 5. Run the Server
```bash
python video_generator.py
```

The server starts at: **http://localhost:8000**

API Documentation (Swagger UI): **http://localhost:8000/docs**

---

## 🧪 Testing & Usage

### Quick Health Check
```bash
curl http://localhost:8000/health
```
Expected response:
```json
{"status": "healthy", "service": "AI Educational Video Generator"}
```

### Check Available AI Services
```bash
curl http://localhost:8000/api/v1/status
```
```json
{
  "system": "AI Educational Video Generator",
  "version": "1.0.0",
  "ai_services": {
    "gemini_available": true,
    "huggingface_available": false
  },
  "recommendation": "Gemini"
}
```

### Generate a Video
```bash
curl -X POST http://localhost:8000/api/v1/generate-video \
  -H "Content-Type: application/json" \
  -d '{
    "class_level": 5,
    "subject": "mathematics",
    "chapter": "Basic Addition",
    "topic": "Adding two digit numbers",
    "duration": 60
  }'
```
Response:
```json
{
  "job_id": "abc123...",
  "status": "pending",
  "message": "AI is rendering your video. Please check status.",
  "estimated_time": 120
}
```

### Poll Job Status
```bash
curl http://localhost:8000/api/v1/job/{job_id}
```
```json
{
  "job_id": "abc123...",
  "status": "processing",
  "progress": 0.5,
  "message": "🎨 Creating Slides (8 slides)..."
}
```

### Download Completed Video
```bash
curl http://localhost:8000/api/v1/download/{job_id} --output lesson.mp4
```

### List All Jobs
```bash
curl http://localhost:8000/api/v1/jobs
```

---

## 📚 API Reference

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/health` | Health check |
| `GET` | `/api/v1/status` | Check available AI services |
| `POST` | `/api/v1/generate-video` | Start video generation (background) |
| `GET` | `/api/v1/job/{job_id}` | Get job status & progress |
| `GET` | `/api/v1/jobs` | List all jobs |
| `GET` | `/api/v1/download/{job_id}` | Download completed video |
| `GET` | `/storage/{job_id}.mp4` | Direct video file access |

### Request Body (`POST /api/v1/generate-video`)

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `class_level` | int (1-12) | ✅ | Student grade level |
| `subject` | string | ✅ | One of: `mathematics`, `physics`, `chemistry`, `biology`, `history`, `geography`, `english`, `computer_science` |
| `chapter` | string | ✅ | Chapter name |
| `topic` | string | ✅ | Specific topic to teach |
| `duration` | int (30-300) | ❌ | Video duration in seconds (default: 120) |

---

## 🎨 Subject Themes

Each subject gets a unique visual theme:

| Subject | Background | Text | Example Highlight |
|---------|-----------|------|-------------------|
| Mathematics | 🟣 Purple | White | Yellow |
| Physics | 🔵 Dark Blue | White | Green |
| Chemistry | 🟢 Dark Green | White | Gold |
| Biology | 🌿 Forest Green | White | Light Blue |
| History | 🟤 Brown | White | Gold |
| Geography | 🔵 Teal | White | Pink |
| English | 🔵 Navy | White | Lavender |
| Computer Science | ⚫ Dark | Green | Gold |

---

## 📁 Project Structure

```
Edu-Ai/
├── video_generator.py     # 🎯 Main application (single file)
├── requirements.txt       # Python dependencies
├── .env                   # Environment variables (create this)
├── .gitignore             # Git exclusions
├── Dockerfile             # Docker deployment
├── README.md              # This file
├── storage/               # Generated videos (auto-created)
└── temp/                  # Temporary processing files (auto-created)
```

---

## 🐳 Docker Deployment

```bash
docker build -t edu-ai .
docker run -p 8000:8000 --env-file .env edu-ai
```

---

## ⚙️ Configuration Reference

| Variable | Default | Description |
|----------|---------|-------------|
| `GEMINI_API_KEY` | — | Google Gemini API key |
| `HF_API_TOKEN` | — | HuggingFace API token (free) |
| `DEBUG` | `true` | Enable debug mode & hot-reload |
| `HOST` | `0.0.0.0` | Server host |
| `PORT` | `8000` | Server port |
| `VIDEO_WIDTH` | `1280` | Output video width (px) |
| `VIDEO_HEIGHT` | `720` | Output video height (px) |
| `VIDEO_FPS` | `24` | Output video frame rate |
| `TEMP_DIR` | `temp` | Temporary file directory |
| `STORAGE_DIR` | `storage` | Final video storage directory |

---

## 🔧 Troubleshooting

| Issue | Solution |
|-------|----------|
| `pydantic-core` build fails | Use Python 3.12, not 3.14 |
| `ModuleNotFoundError` | Run `pip install -r requirements.txt` inside venv |
| Videos not generating | Check `.env` has valid `GEMINI_API_KEY` |
| No audio in video | Ensure `edge-tts` is installed and internet is available |
| Manim animations missing | Install Manim + LaTeX separately |

---

## 📄 License

This project is for educational purposes.

---

Built with ❤️ using FastAPI, Gemini AI, Edge TTS, Pillow & MoviePy