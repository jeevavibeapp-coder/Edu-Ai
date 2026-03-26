"""
AI Educational Video Generation Backend — Single File
=====================================================
A complete FastAPI backend for generating AI-powered educational videos.
Uses Gemini AI for scripts, Edge TTS for voiceover, Pillow for slides, MoviePy for rendering.

Usage:
    python video_generator.py

API Docs:
    http://localhost:8000/docs
"""

import asyncio
import os
import platform
import shutil
import textwrap
import time
import uuid
from contextlib import asynccontextmanager
from enum import Enum
from pathlib import Path
from typing import Any, Dict, List, Optional

import edge_tts
import google.generativeai as genai
import httpx
import structlog
import uvicorn
from dotenv import load_dotenv
from fastapi import BackgroundTasks, FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import FileResponse
from fastapi.staticfiles import StaticFiles
from PIL import Image, ImageDraw, ImageFont
from pydantic import BaseModel, ConfigDict, Field

# MoviePy import — handle both v1.x (moviepy.editor) and v2.x (moviepy)
try:
    from moviepy import (
        AudioFileClip,
        CompositeVideoClip,
        ImageClip,
        VideoFileClip,
        concatenate_videoclips,
    )
except ImportError:
    from moviepy.editor import (
        AudioFileClip,
        CompositeVideoClip,
        ImageClip,
        VideoFileClip,
        concatenate_videoclips,
    )

# Load .env file
load_dotenv()

# ═══════════════════════════════════════════════════════════════
# CONFIGURATION
# ═══════════════════════════════════════════════════════════════

# Load from environment (set in .env or system env vars)
GEMINI_API_KEY = os.getenv("GEMINI_API_KEY", "")
HF_API_TOKEN = os.getenv("HF_API_TOKEN", "")
DEBUG = os.getenv("DEBUG", "true").lower() == "true"
HOST = os.getenv("HOST", "0.0.0.0")
PORT = int(os.getenv("PORT", "8000"))
VIDEO_WIDTH = int(os.getenv("VIDEO_WIDTH", "1280"))
VIDEO_HEIGHT = int(os.getenv("VIDEO_HEIGHT", "720"))
VIDEO_FPS = int(os.getenv("VIDEO_FPS", "24"))
TEMP_DIR = os.getenv("TEMP_DIR", "temp")
STORAGE_DIR = os.getenv("STORAGE_DIR", "storage")
MAX_CONCURRENT_JOBS = int(os.getenv("MAX_CONCURRENT_JOBS", "5"))
MAX_VIDEO_DURATION = int(os.getenv("MAX_VIDEO_DURATION", "300"))

# Configure Gemini
if GEMINI_API_KEY:
    genai.configure(api_key=GEMINI_API_KEY)

# Setup logging
structlog.configure(
    processors=[
        structlog.stdlib.add_log_level,
        structlog.processors.TimeStamper(fmt="iso"),
        structlog.processors.StackInfoRenderer(),
        structlog.processors.format_exc_info,
        structlog.dev.ConsoleRenderer(colors=True),
    ],
    wrapper_class=structlog.stdlib.BoundLogger,
    context_class=dict,
    logger_factory=structlog.PrintLoggerFactory(),
    cache_logger_on_first_use=True,
)
logger = structlog.get_logger()

# ═══════════════════════════════════════════════════════════════
# MODELS (Pydantic v2)
# ═══════════════════════════════════════════════════════════════


class Subject(str, Enum):
    MATHEMATICS = "mathematics"
    PHYSICS = "physics"
    CHEMISTRY = "chemistry"
    BIOLOGY = "biology"
    HISTORY = "history"
    GEOGRAPHY = "geography"
    ENGLISH = "english"
    COMPUTER_SCIENCE = "computer_science"


class JobStatus(str, Enum):
    PENDING = "pending"
    PROCESSING = "processing"
    COMPLETED = "completed"
    FAILED = "failed"


class VideoGenerationRequest(BaseModel):
    class_level: int = Field(..., ge=1, le=12, description="Student class level (1-12)")
    subject: Subject
    chapter: str = Field(..., min_length=1, max_length=100, description="Chapter name")
    topic: str = Field(..., min_length=1, max_length=200, description="Specific topic")
    duration: int = Field(default=120, ge=30, le=300, description="Video duration in seconds")

    model_config = ConfigDict(
        json_schema_extra={
            "examples": [
                {
                    "class_level": 10,
                    "subject": "mathematics",
                    "chapter": "Quadratic Equations",
                    "topic": "Solving quadratic equations by factorization",
                    "duration": 120,
                }
            ]
        }
    )


class VideoGenerationResponse(BaseModel):
    job_id: str
    status: JobStatus
    message: str
    estimated_time: Optional[int] = None
    video_url: Optional[str] = None


class JobStatusResponse(BaseModel):
    job_id: str
    status: JobStatus
    progress: float = Field(default=0.0, ge=0.0, le=1.0)
    message: str
    video_url: Optional[str] = None
    error: Optional[str] = None


# ═══════════════════════════════════════════════════════════════
# JOB MANAGER (In-Memory)
# ═══════════════════════════════════════════════════════════════

# In-memory job storage — replace with Redis/DB for production
video_jobs: Dict[str, Dict[str, Any]] = {}


def create_job(job_id: str, request_data: Dict[str, Any]):
    """Create a new job with initial status"""
    video_jobs[job_id] = {
        "status": JobStatus.PENDING,
        "progress": 0.0,
        "message": "Job created",
        "request": request_data,
        "created_at": time.time(),
        "updated_at": time.time(),
    }


def update_job(
    job_id: str,
    status: JobStatus,
    progress: float = 0.0,
    message: str = "",
    video_url: Optional[str] = None,
    error: Optional[str] = None,
):
    """Update job status and progress"""
    if job_id in video_jobs:
        video_jobs[job_id].update(
            {
                "status": status,
                "progress": progress,
                "message": message,
                "video_url": video_url,
                "error": error,
                "updated_at": time.time(),
            }
        )


def get_job(job_id: str) -> Optional[Dict[str, Any]]:
    """Get job details"""
    return video_jobs.get(job_id)


def list_jobs() -> Dict[str, Dict[str, Any]]:
    """List all jobs"""
    return video_jobs


# ═══════════════════════════════════════════════════════════════
# AI SCRIPT GENERATION
# ═══════════════════════════════════════════════════════════════

# --- Gemini (Premium) ---

async def generate_script_with_gemini(request: VideoGenerationRequest) -> str:
    """Generate an educational script using Google Gemini API"""
    if not GEMINI_API_KEY:
        raise Exception("GEMINI_API_KEY not configured")

    # Calculate word count (avg speaking rate: ~130 words/minute)
    duration_minutes = request.duration / 60
    target_word_count = int(duration_minutes * 130)
    example_count = max(1, int(duration_minutes) - 1)

    model = genai.GenerativeModel("gemini-2.0-flash")

    prompt = f"""
    Create an exceptional educational script for a {request.duration}-second video lesson.
    
    Subject: {request.subject.value}
    Grade Level: {request.class_level}
    Chapter: {request.chapter}
    Topic: {request.topic}
    
    CORE REQUIREMENTS:
    - Language: Crystal clear, engaging, age-appropriate for grade {request.class_level}
    - Structure: Logical flow from simple to complex concepts
    - The script must be roughly {target_word_count} words long
    - Break the script into SHORT paragraphs (2-3 sentences max per paragraph)
    - Each paragraph becomes one presentation slide
    - Include at least {example_count} real-world example(s)
    - Examples MUST start with "Example: " on a new line
    - Do NOT use markdown formatting like **bold** or *italics*
    - Do NOT use emojis
    - Speak directly to students in a warm, encouraging tone
    - Focus on understanding over memorization
    
    SECTION BREAKDOWN:
    1. Hook (15 seconds): Capture attention with relatable opening
    2. Overview (15 seconds): What students will learn and why it matters
    3. Core Concepts (45-60% of time): Break down main ideas with examples
    4. Applications (20-30% of time): Show real-world use and practice
    5. Summary (10-15 seconds): Reinforce key learning points
    
    SUBJECT GUIDELINES:
    - For Mathematics: Show step-by-step solutions with clear reasoning
    - For Science: Explain processes and include observable examples
    
    Format: Separate paragraphs with double line breaks.
    Focus: Maximum clarity + student engagement.
    """

    try:
        logger.info("Calling Gemini API", subject=request.subject.value, topic=request.topic)
        response = await model.generate_content_async(prompt)
        script = response.text
        logger.info("Gemini script generated", length=len(script))
        return script
    except Exception as e:
        logger.error("Gemini API failed", error=str(e))
        raise


# --- Hugging Face (Free) ---

async def generate_script_with_huggingface(request: VideoGenerationRequest) -> str:
    """Generate educational script using Hugging Face free tier API"""
    if not HF_API_TOKEN:
        raise Exception("HF_API_TOKEN not configured")

    api_url = "https://api-inference.huggingface.co/models/TinyLlama/TinyLlama-1.1B-Chat-v1.0"
    headers = {"Authorization": f"Bearer {HF_API_TOKEN}"}

    prompt = f"""You are an expert educational content creator. Write a clear, engaging script for {request.class_level}th grade students about {request.topic} in {request.subject.value}.

REQUIREMENTS:
- Make explanations crystal clear and easy to understand
- Use simple vocabulary appropriate for grade {request.class_level}
- Structure content logically with clear transitions
- Include real-world examples where possible
- Keep it suitable for a {request.duration}-second video
- Break into short paragraphs (2-3 sentences each)

Return ONLY the script content.

Educational Script:"""

    payload = {
        "inputs": prompt,
        "parameters": {
            "max_new_tokens": 600,
            "temperature": 0.6,
            "do_sample": True,
            "return_full_text": False,
        },
    }

    try:
        logger.info("Calling Hugging Face API", model="TinyLlama-1.1B")
        async with httpx.AsyncClient(timeout=30.0) as client:
            response = await client.post(api_url, headers=headers, json=payload)
            response.raise_for_status()

        result = response.json()
        if isinstance(result, list) and result:
            generated_text = result[0].get("generated_text", "").strip()
        elif isinstance(result, dict):
            generated_text = result.get("generated_text", "").strip()
        else:
            generated_text = ""

        if not generated_text:
            raise Exception("Empty response from Hugging Face API")

        # Clean up prompt repetition
        if generated_text.startswith(prompt[:50]):
            generated_text = generated_text[len(prompt):].strip()

        logger.info("HuggingFace script generated", length=len(generated_text))
        return generated_text
    except Exception as e:
        logger.error("HuggingFace API failed", error=str(e))
        raise


# --- Mock Script (Fallback) ---

def generate_mock_script(request: VideoGenerationRequest) -> str:
    """Enhanced mock script when no AI service is available"""
    subject = request.subject.value.replace("_", " ").title()
    topic = request.topic

    return f"""Welcome to {subject} Learning Studio!

Today's Topic: {topic}

In this session, we'll explore the core concepts of {topic}. This is a fundamental skill that builds the foundation for advanced topics in {subject}.

{topic} is based on important principles that you'll use throughout your studies. Understanding this concept opens doors to solving complex problems.

Let's approach this methodically in three simple steps.

Step One: Understanding the Foundation. Begin with the basic building blocks. These fundamental ideas form the foundation of everything we'll discuss.

Step Two: Seeing It in Action. Now we'll see how this concept works through practical examples. Watch carefully as we apply theory to reality.

Example: Let's consider a real-world situation where {topic} applies. This helps us connect what we learn in class to everyday life.

Step Three: Practicing Together. With clear understanding comes confidence. Let's work through examples together to reinforce your learning.

Remember: mastery comes through understanding, not memorization. Take time to really grasp these concepts and practice regularly.

Your journey in {subject} is built on foundations like this. Keep learning, stay curious!

Thank you for learning with us today!"""


# --- Combined AI Service (Fallback Chain) ---

async def generate_script(request: VideoGenerationRequest) -> str:
    """
    Generate script using best available AI service.
    Priority: HuggingFace (free) → Gemini (paid) → Mock (fallback)
    """
    script = None

    # Try HuggingFace first (free)
    if HF_API_TOKEN:
        try:
            logger.info("Trying HuggingFace (free tier)")
            script = await generate_script_with_huggingface(request)
            if script and len(script.strip()) > 50:
                logger.info("Script generated with HuggingFace", length=len(script))
                return script
            script = None
        except Exception as e:
            logger.warning("HuggingFace failed, trying next", error=str(e))

    # Try Gemini (paid fallback)
    if GEMINI_API_KEY:
        try:
            logger.info("Trying Gemini (paid)")
            script = await generate_script_with_gemini(request)
            if script and len(script.strip()) > 50:
                logger.info("Script generated with Gemini", length=len(script))
                return script
            script = None
        except Exception as e:
            logger.warning("Gemini failed, using mock", error=str(e))

    # Final fallback
    logger.info("Using mock script as fallback")
    return generate_mock_script(request)


# ═══════════════════════════════════════════════════════════════
# TTS (Text-to-Speech) SERVICE — Edge TTS
# ═══════════════════════════════════════════════════════════════

VOICE_MAP = {
    "mathematics": "en-US-AriaNeural",
    "physics": "en-US-ZiraNeural",
    "chemistry": "en-US-BenjaminNeural",
    "biology": "en-GB-SoniaNeural",
    "history": "en-GB-LibbyNeural",
    "geography": "en-AU-NatashaNeural",
    "english": "en-GB-SoniaNeural",
    "computer_science": "en-US-AriaNeural",
}


async def create_voiceover(text: str, output_audio_path: str, subject: str = "mathematics"):
    """Generate audio for a single paragraph using Edge TTS"""
    voice = VOICE_MAP.get(subject, "en-US-AriaNeural")
    try:
        communicate = edge_tts.Communicate(text, voice)
        await communicate.save(output_audio_path)
        logger.debug("Audio generated", path=output_audio_path)
        return output_audio_path
    except Exception as e:
        logger.error("TTS generation failed", error=str(e))
        return None


async def generate_audio_batch(
    paragraphs: List[str], output_dir: str, subject: str
) -> List[str]:
    """Generate audio files for all paragraphs with limited concurrency"""
    semaphore = asyncio.Semaphore(3)
    audio_files = []

    async def generate_one(i: int, para: str):
        async with semaphore:
            audio_path = os.path.join(output_dir, f"audio_{i}.mp3")
            result = await create_voiceover(para, audio_path, subject)
            return result

    tasks = [generate_one(i, para) for i, para in enumerate(paragraphs)]
    results = await asyncio.gather(*tasks)

    for result in results:
        if result:
            audio_files.append(result)

    logger.info("TTS batch completed", generated=len(audio_files), total=len(paragraphs))
    return audio_files


# ═══════════════════════════════════════════════════════════════
# SLIDE GENERATOR — Pillow
# ═══════════════════════════════════════════════════════════════

# Color themes per subject (Purple blackboard style from reference)
SUBJECT_COLORS = {
    "mathematics": {"bg": (94, 23, 131), "text": (255, 255, 255), "example": (255, 235, 59), "accent": (200, 200, 200)},
    "physics": {"bg": (20, 50, 100), "text": (255, 255, 255), "example": (100, 255, 100), "accent": (200, 200, 200)},
    "chemistry": {"bg": (0, 80, 60), "text": (255, 255, 255), "example": (255, 200, 50), "accent": (200, 200, 200)},
    "biology": {"bg": (20, 80, 20), "text": (255, 255, 255), "example": (100, 200, 255), "accent": (200, 200, 200)},
    "history": {"bg": (80, 50, 20), "text": (255, 255, 255), "example": (255, 215, 0), "accent": (200, 200, 200)},
    "geography": {"bg": (0, 80, 80), "text": (255, 255, 255), "example": (255, 150, 150), "accent": (200, 200, 200)},
    "english": {"bg": (30, 30, 80), "text": (255, 255, 255), "example": (200, 150, 255), "accent": (200, 200, 200)},
    "computer_science": {"bg": (20, 20, 50), "text": (0, 255, 0), "example": (255, 200, 50), "accent": (200, 200, 200)},
}


def _load_fonts():
    """Load fonts with cross-platform fallback"""
    try:
        if platform.system() == "Windows":
            title_font = ImageFont.truetype("arialbd.ttf", 60)
            body_font = ImageFont.truetype("arial.ttf", 40)
        else:
            title_font = ImageFont.truetype("/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf", 60)
            body_font = ImageFont.truetype("/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf", 40)
    except OSError:
        title_font = ImageFont.load_default()
        body_font = ImageFont.load_default()
    return title_font, body_font


def create_slide_image(
    paragraph_text: str,
    output_image_path: str,
    slide_number: int,
    total_slides: int,
    subject: str = "mathematics",
):
    """Generate a slide image with subject-themed colors and example highlighting"""
    colors = SUBJECT_COLORS.get(subject, SUBJECT_COLORS["mathematics"])
    font, small_font = _load_fonts()

    img = Image.new("RGB", (VIDEO_WIDTH, VIDEO_HEIGHT), color=colors["bg"])
    draw = ImageDraw.Draw(img)

    # Split main text and example text
    if "Example:" in paragraph_text:
        parts = paragraph_text.split("Example:")
        main_text = parts[0].strip()
        example_text = "Example: " + parts[1].strip()
    else:
        main_text = paragraph_text.strip()
        example_text = ""

    # Draw main text (centered)
    wrapped_main = "\n".join(textwrap.wrap(main_text, width=40))
    draw.multiline_text(
        (VIDEO_WIDTH // 2, 60),
        wrapped_main,
        fill=colors["text"],
        font=font,
        spacing=20,
        align="center",
        anchor="ma",
    )

    # Draw example text in highlight color below main text
    if example_text:
        wrapped_example = "\n".join(textwrap.wrap(example_text, width=40))
        bbox = draw.multiline_textbbox(
            (VIDEO_WIDTH // 2, 60), wrapped_main, font=font, spacing=20, align="center", anchor="ma"
        )
        y_start = bbox[3] + 40  # 40px gap after main text
        draw.multiline_text(
            (VIDEO_WIDTH // 2, y_start),
            wrapped_example,
            fill=colors["example"],
            font=font,
            spacing=20,
            align="center",
            anchor="ma",
        )

    # Slide number indicator (bottom right)
    slide_label = f"Slide {slide_number} of {total_slides}"
    draw.text((VIDEO_WIDTH - 300, VIDEO_HEIGHT - 50), slide_label, fill=colors["accent"], font=small_font)

    # Simple border
    draw.rectangle([10, 10, VIDEO_WIDTH - 10, VIDEO_HEIGHT - 10], outline=colors["accent"], width=2)

    img.save(output_image_path, "PNG")
    logger.debug("Slide created", path=output_image_path, slide=slide_number)


def generate_slides(paragraphs: List[str], output_dir: str, subject: str) -> List[str]:
    """Generate slide images for all paragraphs"""
    slide_files = []
    for i, paragraph in enumerate(paragraphs):
        slide_path = os.path.join(output_dir, f"slide_{i}.png")
        create_slide_image(paragraph, slide_path, i + 1, len(paragraphs), subject)
        slide_files.append(slide_path)

    logger.info("Slides generated", count=len(slide_files))
    return slide_files


# ═══════════════════════════════════════════════════════════════
# MANIM SERVICE — Mathematical Animations
# ═══════════════════════════════════════════════════════════════

MANIM_SCENES = {
    "quadratic": '''from manim import *

class MathScene(Scene):
    def construct(self):
        title = Text("Solving Quadratic Equations", font_size=48)
        self.play(Write(title))
        self.wait(1)
        equation = MathTex("ax^2 + bx + c = 0", font_size=36)
        self.play(Transform(title, equation))
        self.wait(1)
        formula = MathTex(r"x = \\frac{-b \\pm \\sqrt{b^2 - 4ac}}{2a}", font_size=36)
        self.play(Write(formula.next_to(equation, DOWN)))
        self.wait(2)
        example = MathTex("x^2 + 5x + 6 = 0", font_size=36)
        self.play(Transform(equation, example))
        self.wait(1)
        solution = MathTex(r"x = \\frac{-5 \\pm \\sqrt{25 - 24}}{2} = \\frac{-5 \\pm 1}{2}", font_size=32)
        self.play(Write(solution.next_to(example, DOWN)))
        self.wait(2)
        self.play(FadeOut(VGroup(title, equation, formula, example, solution)))
''',
    "geometry": '''from manim import *

class MathScene(Scene):
    def construct(self):
        title = Text("Triangle Properties", font_size=48)
        self.play(Write(title))
        self.wait(1)
        triangle = Polygon([-2, -1, 0], [2, -1, 0], [0, 1.5, 0], color=BLUE, fill_opacity=0.5)
        self.play(Create(triangle))
        self.wait(1)
        side_a = MathTex("a").next_to(triangle, LEFT)
        side_b = MathTex("b").next_to(triangle, RIGHT)
        side_c = MathTex("c").next_to(triangle, DOWN)
        self.play(Write(side_a), Write(side_b), Write(side_c))
        self.wait(1)
        theorem = MathTex("a^2 + b^2 = c^2", font_size=36)
        self.play(Write(theorem.to_edge(UP)))
        self.wait(2)
        self.play(FadeOut(VGroup(title, triangle, side_a, side_b, side_c, theorem)))
''',
    "calculus": '''from manim import *

class MathScene(Scene):
    def construct(self):
        title = Text("Derivatives", font_size=48)
        self.play(Write(title))
        self.wait(1)
        func = MathTex("f(x) = x^2", font_size=36)
        self.play(Write(func))
        self.wait(1)
        deriv = MathTex("f'(x) = 2x", font_size=36)
        self.play(Transform(func, deriv))
        self.wait(2)
        rule = MathTex(r"\\frac{d}{dx}[x^n] = nx^{n-1}", font_size=36)
        self.play(Write(rule.next_to(deriv, DOWN)))
        self.wait(2)
        self.play(FadeOut(VGroup(title, func, deriv, rule)))
''',
}


async def generate_manim_animation(topic: str, output_dir: str) -> Optional[str]:
    """Generate Manim animation for math topics. Returns video path or None."""
    try:
        # Pick scene based on topic keywords
        topic_lower = topic.lower()
        if "quadratic" in topic_lower:
            scene_code = MANIM_SCENES["quadratic"]
        elif "geometry" in topic_lower or "triangle" in topic_lower:
            scene_code = MANIM_SCENES["geometry"]
        elif "calculus" in topic_lower or "derivative" in topic_lower:
            scene_code = MANIM_SCENES["calculus"]
        else:
            # General scene
            scene_code = f'''
from manim import *

class MathScene(Scene):
    def construct(self):
        title = Text("{topic}", font_size=48)
        self.play(Write(title))
        self.wait(2)
        circle = Circle(radius=1, color=BLUE)
        self.play(Create(circle))
        self.wait(1)
        square = Square(side_length=2, color=RED)
        self.play(Transform(circle, square))
        self.wait(1)
        self.play(FadeOut(VGroup(title, circle, square)))
'''

        scene_file = os.path.join(output_dir, "math_scene.py")
        video_path = os.path.join(output_dir, "math_animation.mp4")

        with open(scene_file, "w") as f:
            f.write(scene_code)

        cmd = [
            "manim", scene_file, "MathScene",
            "-o", "MathScene",
            "--format", "mp4",
            "--resolution", f"{VIDEO_WIDTH},{VIDEO_HEIGHT}",
            "--fps", str(VIDEO_FPS),
            "--disable_caching",
        ]

        logger.info("Running Manim", topic=topic)
        process = await asyncio.create_subprocess_exec(
            *cmd, cwd=output_dir,
            stdout=asyncio.subprocess.PIPE, stderr=asyncio.subprocess.PIPE,
        )
        stdout, stderr = await process.communicate()

        if process.returncode == 0:
            # Search for output file
            media_dir = Path(output_dir) / "media" / "videos" / "math_scene"
            found = None
            for pattern in [f"{VIDEO_HEIGHT}p{VIDEO_FPS}", f"{VIDEO_WIDTH}p{VIDEO_FPS}"]:
                candidate = media_dir / pattern / "MathScene.mp4"
                if candidate.exists():
                    found = candidate
                    break
            if found is None:
                for mp4 in media_dir.rglob("MathScene.mp4"):
                    found = mp4
                    break

            if found:
                os.rename(str(found), video_path)
                logger.info("Manim animation generated", path=video_path)
                return video_path

        logger.warning("Manim generation failed or output not found")
        return None
    except Exception as e:
        logger.error("Manim failed", error=str(e))
        return None


# ═══════════════════════════════════════════════════════════════
# VIDEO RENDERER — MoviePy
# ═══════════════════════════════════════════════════════════════


def stitch_video(
    slide_files: List[str],
    audio_files: List[str],
    manim_video: Optional[str],
    output_path: str,
    target_duration: int,
    subject: str,
):
    """Combine slides + audio + optional Manim animation into final MP4"""
    clips = []
    audio_clips = []
    num_slides = len(slide_files)

    for i, slide_path in enumerate(slide_files):
        if audio_files and i < len(audio_files):
            audio_clip = AudioFileClip(audio_files[i])
            slide_clip = ImageClip(slide_path, duration=audio_clip.duration)
            slide_clip = slide_clip.with_audio(audio_clip)
            audio_clips.append(audio_clip)
        else:
            duration_per = target_duration / num_slides if num_slides > 0 else 5
            slide_clip = ImageClip(slide_path, duration=duration_per)

        clips.append(slide_clip)

    # Insert Manim animation in the middle for math subjects
    if manim_video and subject == "mathematics":
        try:
            manim_clip = VideoFileClip(manim_video)
            mid = len(clips) // 2
            clips.insert(mid, manim_clip)
        except Exception as e:
            logger.warning("Could not include Manim clip", error=str(e))

    # Concatenate
    final_clip = concatenate_videoclips(clips, method="compose")

    # Trim if needed
    if final_clip.duration > target_duration:
        final_clip = final_clip.subclipped(0, target_duration)

    # Render
    logger.info("Rendering video", output=output_path, duration=final_clip.duration)
    final_clip.write_videofile(
        output_path,
        fps=VIDEO_FPS,
        codec="libx264",
        audio_codec="aac",
        ffmpeg_params=["-pix_fmt", "yuv420p"],
        remove_temp=True,
    )

    # Cleanup
    final_clip.close()
    for clip in clips:
        clip.close()
    for ac in audio_clips:
        ac.close()

    time.sleep(0.5)  # Ensure file handles are released
    logger.info("Video rendering completed", path=output_path)
    return output_path


# ═══════════════════════════════════════════════════════════════
# PIPELINE — Video Generation Task
# ═══════════════════════════════════════════════════════════════


def parse_script_into_paragraphs(script: str) -> List[str]:
    """Split script into paragraphs for slide creation"""
    return [p.strip() for p in script.split("\n\n") if p.strip()]


async def process_video_generation(job_id: str, request: VideoGenerationRequest):
    """
    Main pipeline orchestrator:
    1. Generate script (AI)
    2. Split into paragraphs
    3. Generate TTS audio
    4. Create slides
    5. Optional: Manim animation (math)
    6. Render final video
    """
    try:
        logger.info("Starting video generation", job_id=job_id, topic=request.topic)
        update_job(job_id, JobStatus.PROCESSING, 0.1, "📝 Writing Lesson Script...")

        # Create temp directory
        temp_dir = os.path.join(TEMP_DIR, f"job_{job_id}")
        os.makedirs(temp_dir, exist_ok=True)

        try:
            # Step 1: Generate script
            script = await generate_script(request)
            update_job(job_id, JobStatus.PROCESSING, 0.2, "Script generated successfully")

            # Step 2: Parse into paragraphs
            paragraphs = parse_script_into_paragraphs(script)
            if not paragraphs:
                raise Exception("Script produced no paragraphs")

            # Step 3: Generate TTS audio
            update_job(
                job_id, JobStatus.PROCESSING, 0.3,
                f"🎙️ Generating Voiceover (0/{len(paragraphs)} slides)...",
            )
            audio_files = await generate_audio_batch(paragraphs, temp_dir, request.subject.value)
            update_job(
                job_id, JobStatus.PROCESSING, 0.5,
                f"🎨 Creating Slides ({len(paragraphs)} slides)...",
            )

            # Step 4: Generate slides
            slide_files = generate_slides(paragraphs, temp_dir, request.subject.value)
            update_job(job_id, JobStatus.PROCESSING, 0.6, f"Generated {len(slide_files)} slides")

            # Step 5: Manim animation (math only)
            manim_video = None
            if request.subject == Subject.MATHEMATICS:
                update_job(job_id, JobStatus.PROCESSING, 0.65, "🎬 Generating Math Animation...")
                manim_video = await generate_manim_animation(request.topic, temp_dir)

            # Step 6: Render final video
            update_job(job_id, JobStatus.PROCESSING, 0.7, "🎬 Stitching Final Video...")
            output_path = os.path.join(temp_dir, "final_video.mp4")
            await asyncio.to_thread(
                stitch_video,
                slide_files, audio_files, manim_video,
                output_path, request.duration, request.subject.value,
            )

            # Move to storage
            os.makedirs(STORAGE_DIR, exist_ok=True)
            final_path = os.path.join(STORAGE_DIR, f"{job_id}.mp4")
            shutil.move(output_path, final_path)

            video_url = f"/storage/{job_id}.mp4"
            update_job(
                job_id, JobStatus.COMPLETED, 1.0,
                "✅ Video generation completed!", video_url=video_url,
            )
            logger.info("Video generation completed", job_id=job_id, path=final_path)

        except Exception as e:
            logger.error("Video generation failed", job_id=job_id, error=str(e), exc_info=True)
            update_job(
                job_id, JobStatus.FAILED, 0.0,
                f"❌ Video generation failed: {str(e)}", error=str(e),
            )
        finally:
            # Cleanup temp files
            try:
                shutil.rmtree(temp_dir, ignore_errors=True)
            except Exception:
                pass

    except Exception as e:
        logger.error("Pipeline error", job_id=job_id, error=str(e))
        update_job(
            job_id, JobStatus.FAILED, 0.0,
            f"❌ Pipeline error: {str(e)}", error=str(e),
        )


# ═══════════════════════════════════════════════════════════════
# FASTAPI APPLICATION
# ═══════════════════════════════════════════════════════════════


@asynccontextmanager
async def lifespan(app: FastAPI):
    """Application startup/shutdown"""
    logger.info("Starting AI Educational Video Generator")
    os.makedirs(STORAGE_DIR, exist_ok=True)
    os.makedirs(TEMP_DIR, exist_ok=True)
    yield
    logger.info("Shutting down AI Educational Video Generator")


app = FastAPI(
    title="AI Educational Video Generator",
    description="Generate AI-powered educational videos with Gemini, Edge TTS, Pillow & MoviePy",
    version="1.0.0",
    debug=DEBUG,
    lifespan=lifespan,
)

# CORS
allowed_origins = ["http://localhost:3000", "http://localhost:8080"]
if DEBUG:
    allowed_origins.append("*")

app.add_middleware(
    CORSMiddleware,
    allow_origins=allowed_origins,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Ensure storage dir exists before mounting StaticFiles (it requires the dir to exist)
os.makedirs(STORAGE_DIR, exist_ok=True)
app.mount("/storage", StaticFiles(directory=STORAGE_DIR), name="storage")


# ── Routes ─────────────────────────────────────────────────────


@app.get("/health")
async def health_check():
    """Health check endpoint"""
    return {"status": "healthy", "service": "AI Educational Video Generator"}


@app.post("/api/v1/generate-video", response_model=VideoGenerationResponse)
async def generate_video_endpoint(
    request: VideoGenerationRequest,
    background_tasks: BackgroundTasks,
):
    """Generate an educational video (runs in background)"""
    job_id = uuid.uuid4().hex

    create_job(job_id, request.model_dump())
    logger.info(
        "Video generation job created",
        job_id=job_id, subject=request.subject.value,
        topic=request.topic, duration=request.duration,
    )

    # Run in background — returns immediately
    background_tasks.add_task(process_video_generation, job_id, request)

    return VideoGenerationResponse(
        job_id=job_id,
        status=JobStatus.PENDING,
        message="AI is rendering your video. Please check status.",
        estimated_time=request.duration * 2,
    )


@app.get("/api/v1/job/{job_id}", response_model=JobStatusResponse)
async def get_job_status(job_id: str):
    """Check status of a video generation job"""
    job = get_job(job_id)
    if not job:
        raise HTTPException(status_code=404, detail="Job not found")

    return JobStatusResponse(
        job_id=job_id,
        status=job["status"],
        progress=job.get("progress", 0.0),
        message=job.get("message", ""),
        video_url=job.get("video_url"),
        error=job.get("error"),
    )


@app.get("/api/v1/jobs")
async def list_all_jobs():
    """List all jobs and their statuses"""
    jobs = list_jobs()
    return {
        jid: JobStatusResponse(
            job_id=jid,
            status=j["status"],
            progress=j.get("progress", 0.0),
            message=j.get("message", ""),
            video_url=j.get("video_url"),
            error=j.get("error"),
        )
        for jid, j in jobs.items()
    }


@app.get("/api/v1/status")
async def system_status():
    """Get system status — which AI services are available"""
    return {
        "system": "AI Educational Video Generator",
        "version": "1.0.0",
        "ai_services": {
            "gemini_available": bool(GEMINI_API_KEY),
            "huggingface_available": bool(HF_API_TOKEN),
        },
        "recommendation": (
            "Gemini" if GEMINI_API_KEY
            else "Hugging Face" if HF_API_TOKEN
            else "Mock scripts only"
        ),
    }


@app.get("/api/v1/download/{job_id}")
async def download_video(job_id: str):
    """Download a completed video"""
    job = get_job(job_id)
    if not job:
        raise HTTPException(status_code=404, detail="Job not found")
    if job["status"] != JobStatus.COMPLETED:
        raise HTTPException(status_code=400, detail="Video not ready yet")

    file_path = os.path.join(STORAGE_DIR, f"{job_id}.mp4")
    if not os.path.exists(file_path):
        raise HTTPException(status_code=404, detail="Video file not found")

    return FileResponse(file_path, media_type="video/mp4", filename=f"lesson_{job_id}.mp4")


# ═══════════════════════════════════════════════════════════════
# ENTRY POINT
# ═══════════════════════════════════════════════════════════════

if __name__ == "__main__":
    uvicorn.run(
        "video_generator:app",
        host=HOST,
        port=PORT,
        reload=DEBUG,
        log_config=None,
    )
