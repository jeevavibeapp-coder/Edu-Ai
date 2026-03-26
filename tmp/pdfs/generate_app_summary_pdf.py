from pathlib import Path

from PIL import Image, ImageDraw, ImageFont


ROOT = Path(__file__).resolve().parents[2]
TMP_DIR = ROOT / "tmp" / "pdfs"
OUT_DIR = ROOT / "output" / "pdf"
PNG_PATH = TMP_DIR / "app_summary_one_page.png"
PDF_PATH = OUT_DIR / "app_summary_one_page.pdf"
PAGE_W = 1275
PAGE_H = 1650
MARGIN = 72


def font(size, bold=False):
    paths = [
        "C:/Windows/Fonts/arialbd.ttf" if bold else "C:/Windows/Fonts/arial.ttf",
        "C:/Windows/Fonts/calibrib.ttf" if bold else "C:/Windows/Fonts/calibri.ttf",
    ]
    for path in paths:
        p = Path(path)
        if p.exists():
            return ImageFont.truetype(str(p), size)
    return ImageFont.load_default()


TITLE = font(34, True)
SECTION = font(20, True)
BODY = font(16)
SMALL = font(14)


def wrap(draw, text, fnt, width):
    words = text.split()
    lines = []
    cur = ""
    for word in words:
        trial = word if not cur else f"{cur} {word}"
        if draw.textbbox((0, 0), trial, font=fnt)[2] <= width:
            cur = trial
        else:
            if cur:
                lines.append(cur)
            cur = word
    if cur:
        lines.append(cur)
    return lines or [""]


def draw_para(draw, x, y, text, fnt, width, fill):
    for line in wrap(draw, text, fnt, width):
        draw.text((x, y), line, font=fnt, fill=fill)
        y += fnt.size + 6
    return y


def draw_bullets(draw, x, y, items, fnt, width, fill):
    for item in items:
        lines = wrap(draw, item, fnt, width - 20)
        draw.text((x, y), "-", font=fnt, fill=fill)
        for line in lines:
            draw.text((x + 20, y), line, font=fnt, fill=fill)
            y += fnt.size + 4
        y += 6
    return y


def section(draw, x, y, title):
    draw.text((x, y), title, font=SECTION, fill="#16324F")
    y += SECTION.size + 6
    draw.line((x, y, PAGE_W - MARGIN, y), fill="#C7D6E5", width=2)
    return y + 14


def main():
    TMP_DIR.mkdir(parents=True, exist_ok=True)
    OUT_DIR.mkdir(parents=True, exist_ok=True)

    img = Image.new("RGB", (PAGE_W, PAGE_H), "white")
    draw = ImageDraw.Draw(img)
    draw.rounded_rectangle((36, 36, PAGE_W - 36, PAGE_H - 36), 20, fill="white", outline="#D6E0EA", width=3)
    draw.rectangle((36, 36, PAGE_W - 36, 165), fill="#F4F8FB")

    x = MARGIN
    y = 62
    draw.text((x, y), "AI Educational Video Generator", font=TITLE, fill="#0E2233")
    y += TITLE.size + 10
    draw.text((x, y), "One-page repo summary based on current code and docs evidence.", font=SMALL, fill="#4A6072")

    what_it_is = (
        "A FastAPI backend that generates educational videos from structured lesson inputs. "
        "It creates scripts, narration, slides, optional math animations, and an MP4 output "
        "through an asynchronous job pipeline."
    )
    who_its_for = (
        "Primary persona: developers or product teams that want an API to generate lesson videos "
        "for K-12 style subjects and then poll job status or download finished files."
    )
    features = [
        "Accepts lesson requests with class level, subject, chapter, topic, and duration.",
        "Runs video generation asynchronously with job IDs, progress tracking, and status endpoints.",
        "Generates scripts through a fallback chain: Hugging Face -> Gemini -> built-in mock script.",
        "Creates voiceover audio with subject-specific Edge TTS voices.",
        "Builds slide images with Pillow using per-subject color schemes.",
        "Adds optional Manim animations for mathematics topics.",
        "Renders final MP4 videos with MoviePy and serves them from /storage.",
    ]
    architecture = [
        "FastAPI app in app/main.py loads settings, CORS, /health, static /storage, and the /api/v1 video router.",
        "Router in app/routers/video.py creates a job in the in-memory JobManager and starts a BackgroundTasks pipeline.",
        "Task in app/tasks/video_generation.py orchestrates script generation, paragraph splitting, TTS, slide creation, optional Manim, then video rendering.",
        "Services under app/services/ implement CombinedAIService, TTSService, SlideGenerator, ManimService, and VideoRenderer.",
        "Artifacts flow through temp/ during processing, then the final MP4 is moved to storage/{job_id}.mp4 and exposed as /storage/{job_id}.mp4.",
    ]
    how_to_run = [
        "Create a Python 3.11/3.12 virtual environment, then install `pip install -r requirements.txt`.",
        "Copy `.env.example` to `.env` and set at least one of `GEMINI_API_KEY` or `HF_API_TOKEN`; otherwise mock scripts are used.",
        "Start the API with `python -m app.main` from the repo root.",
        "Open `http://localhost:8000/docs` or call `/health` and `/api/v1/generate-video` to test.",
    ]

    y = 195
    y = section(draw, x, y, "What It Is")
    y = draw_para(draw, x, y, what_it_is, BODY, PAGE_W - (2 * MARGIN), "#1E2C38")
    y += 10
    y = section(draw, x, y, "Who It's For")
    y = draw_para(draw, x, y, who_its_for, BODY, PAGE_W - (2 * MARGIN), "#1E2C38")
    y += 10
    y = section(draw, x, y, "What It Does")
    y = draw_bullets(draw, x, y, features, BODY, PAGE_W - (2 * MARGIN), "#1E2C38")
    y += 4
    y = section(draw, x, y, "How It Works")
    y = draw_bullets(draw, x, y, architecture, BODY, PAGE_W - (2 * MARGIN), "#1E2C38")
    y += 4
    y = section(draw, x, y, "How To Run")
    y = draw_bullets(draw, x, y, how_to_run, BODY, PAGE_W - (2 * MARGIN), "#1E2C38")

    footer = "Not found in repo: frontend UI, database persistence, auth, and deployment beyond the provided Dockerfile."
    draw.line((x, PAGE_H - 130, PAGE_W - MARGIN, PAGE_H - 130), fill="#C7D6E5", width=2)
    draw.text((x, PAGE_H - 110), footer, font=SMALL, fill="#5A6B78")

    img.save(PNG_PATH, "PNG")
    img.save(PDF_PATH, "PDF", resolution=150.0)
    print(PDF_PATH)
    print(PNG_PATH)


if __name__ == "__main__":
    main()
