# AI Educational Video Generation Backend

A production-grade FastAPI backend for generating AI-powered educational videos using multiple AI services, Edge TTS, Pillow, MoviePy, and Manim.

## Features

- **Multi-AI Script Generation**: Uses Gemini (premium) + Hugging Face (free) with intelligent fallback
- **Neural Voice Audio**: Generates high-quality TTS audio using Microsoft Edge TTS
- **Dynamic Slide Creation**: Creates visually appealing slides with Pillow
- **Video Rendering**: Combines slides and audio with MoviePy for smooth playback
- **Mathematical Animations**: Integrates Manim for interactive Maths content
- **Background Processing**: Asynchronous video generation with job status tracking
- **Scalable Architecture**: Designed for concurrent video generation requests
- **Cost Optimization**: Free tier support with premium upgrade path

## Architecture

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Mobile App    │────│   FastAPI       │────│   Background    │
│   (Flutter)     │    │   Backend       │    │   Tasks         │
└─────────────────┘    └─────────────────┘    └─────────────────┘
                              │
                              ▼
                       ┌─────────────────┐
                       │ Combined AI     │
                       │ Service         │
                       │ • Gemini API    │
                       │ • Hugging Face  │
                       │ • Mock Fallback │
                       └─────────────────┘
                              │
                              ▼
                       ┌─────────────────┐
                       │   Services      │
                       │ • Edge TTS      │
                       │ • Pillow        │
                       │ • MoviePy       │
                       │ • Manim         │
                       └─────────────────┘
                              │
                              ▼
                       ┌─────────────────┐
                       │   Storage       │
                       │ • Temp files    │
                       │ • Final videos  │
                       └─────────────────┘
```

## Setup

1. **Clone and Install Dependencies**
   ```bash
   pip install -r requirements.txt
   ```

2. **Environment Configuration**
   ```bash
   cp .env.example .env
   # Edit .env with your API keys (see AI Services section below)
   ```

## AI Services Configuration

### Option 1: Premium Quality (Gemini Only)
```bash
# Get API key from: https://makersuite.google.com/app/apikey
GEMINI_API_KEY=your_gemini_api_key_here
```

### Option 2: Free Tier (Hugging Face Only)
```bash
# Get token from: https://huggingface.co/settings/tokens
HF_API_TOKEN=your_huggingface_token_here
```

### Option 3: Combined (Recommended - Best Quality)
Set both tokens for optimal results with automatic fallback:
```bash
GEMINI_API_KEY=your_gemini_key
HF_API_TOKEN=your_hf_token
```

**AI Service Priority:**
1. **Gemini** (if available): Highest quality, paid service
2. **Hugging Face** (if available): Good quality, free tier
3. **Mock Scripts**: Reliable fallback, no API required

3. **Install System Dependencies**
   ```bash
   # For Manim (Linux/Mac)
   sudo apt-get install ffmpeg
   pip install manim
   
   # For Edge TTS
   pip install edge-tts
   ```

4. **Run the Application**
   ```bash
   python -m app.main
   ```

## API Endpoints

### Generate Video
```http
POST /api/v1/generate-video
Content-Type: application/json

{
  "class_level": 10,
  "subject": "mathematics",
  "chapter": "Quadratic Equations",
  "topic": "Solving quadratic equations by factorization",
  "duration": 120
}
```

### Check Job Status
```http
GET /api/v1/job/{job_id}
```

### List All Jobs
```http
GET /api/v1/jobs
```

### System Status
```http
GET /api/v1/status
```
Returns AI service availability and system health.

## Testing

### Test AI Services
```bash
# Test combined AI service
python test_combined_ai.py

# Test full video pipeline
python test_pipeline.py
```

### API Testing
```bash
# Generate video
curl -X POST "http://localhost:8000/api/v1/generate-video" \
  -H "Content-Type: application/json" \
  -d '{
    "class_level": 10,
    "subject": "mathematics",
    "chapter": "Quadratic Equations",
    "topic": "Solving quadratic equations",
    "duration": 60
  }'

# Check status
curl "http://localhost:8000/api/v1/status"
```

## Production Deployment

### Docker
```bash
docker build -t video-generator .
docker run -p 8000:8000 video-generator
```

### Scaling with Celery
```bash
# Start Redis
redis-server

# Start Celery worker
celery -A app.tasks worker --loglevel=info

# Start FastAPI
uvicorn app.main:app --host 0.0.0.0 --port 8000
```

## Configuration

Key settings in `.env`:
- `GEMINI_API_KEY`: Your Google Gemini API key
- `REDIS_URL`: Redis connection for job queuing
- `MAX_CONCURRENT_JOBS`: Limit concurrent video generations
- `VIDEO_WIDTH/HEIGHT`: Output video resolution

## Development

### Running Tests
```bash
pytest
```

### Code Quality
```bash
black .
flake8
mypy
```

## Mobile App Integration

The backend provides REST APIs suitable for Flutter mobile applications:

- **Job Polling**: Periodic status checks for video generation progress
- **WebSocket Support**: Real-time updates (can be added)
- **Cloud Storage**: Videos served via signed URLs
- **Authentication**: JWT-based user sessions

## Performance Optimizations

- Parallel TTS generation with concurrency limits
- Video caching for repeated topics
- Memory-efficient processing with streaming
- Background task queuing with Redis/Celery
- Optimized video compression

## Monitoring

- Structured JSON logging with correlation IDs
- Health check endpoints
- Job status tracking
- Error reporting and alerting

## Contributing

1. Follow the existing code structure
2. Add tests for new features
3. Update documentation
4. Use type hints and docstrings