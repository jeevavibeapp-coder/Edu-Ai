import asyncio
import structlog
from typing import Dict, Any
import os
import tempfile

from app.models.video import VideoGenerationRequest, JobStatus
from app.services.job_manager import job_manager
from app.services.combined_ai_service import combined_ai_service as script_service
from app.services.tts_service import TTSService
from app.services.slide_generator import SlideGenerator
from app.services.video_renderer import VideoRenderer
from app.services.manim_service import ManimService
from app.config import settings

logger = structlog.get_logger()


async def generate_video_task(job_id: str, request: VideoGenerationRequest):
    """
    Main video generation task that orchestrates the entire pipeline:
    1. Generate script with AI services
    2. Split into paragraphs
    3. Generate TTS audio
    4. Create slides
    5. Render video with MoviePy
    6. Handle Manim for Maths
    """
    try:
        logger.info("Starting video generation", job_id=job_id, topic=request.topic)

        # Update status to processing
        job_manager.update_job(
            job_id, JobStatus.PROCESSING, 0.1, "Initializing video generation"
        )

        # Create temporary directory for this job
        with tempfile.TemporaryDirectory(dir=settings.TEMP_DIR) as temp_dir:
            try:
                # Step 1: Generate educational script
                logger.info(
                    "Generating script with AI services", job_id=job_id
                )
                script = await script_service.generate_script(request)

                job_manager.update_job(
                    job_id,
                    JobStatus.PROCESSING,
                    0.2,
                    "Script generated successfully",
                )

                # Step 2: Parse script into paragraphs
                paragraphs = parse_script_into_paragraphs(script)

                # Step 3: Generate TTS audio for each paragraph
                logger.info(
                    "Generating TTS audio",
                    job_id=job_id,
                    paragraphs=len(paragraphs),
                )
                tts_service = TTSService()
                audio_files = await tts_service.generate_audio_batch(
                    paragraphs, temp_dir, request.subject
                )

                job_manager.update_job(
                    job_id,
                    JobStatus.PROCESSING,
                    0.4,
                    f"Generated audio for {len(paragraphs)} slides",
                )

                # Step 4: Generate slides
                logger.info("Generating slides", job_id=job_id)
                slide_generator = SlideGenerator()
                slide_files = slide_generator.generate_slides(
                    paragraphs, temp_dir, request.subject
                )

                job_manager.update_job(
                    job_id,
                    JobStatus.PROCESSING,
                    0.6,
                    f"Generated {len(slide_files)} slides",
                )

                # Step 5: Handle Manim animations for Maths
                manim_video = None
                if request.subject == "mathematics":
                    logger.info("Generating Manim animation", job_id=job_id)
                    manim_service = ManimService()
                    manim_video = await manim_service.generate_animation(
                        request.topic, temp_dir, request.duration
                    )

                # Step 6: Render final video
                logger.info("Rendering final video", job_id=job_id)
                video_renderer = VideoRenderer()
                video_path = video_renderer.render_video(
                    slide_files,
                    audio_files,
                    manim_video,
                    temp_dir,
                    request.duration,
                    request.subject,
                )

                job_manager.update_job(
                    job_id,
                    JobStatus.PROCESSING,
                    0.9,
                    "Finalizing video rendering",
                )

                # Step 7: Move to storage and generate URL
                os.makedirs(settings.STORAGE_DIR, exist_ok=True)
                final_path = os.path.join(settings.STORAGE_DIR, f"{job_id}.mp4")
                os.rename(video_path, final_path)

                # In production, upload to cloud storage and get signed URL
                video_url = f"/storage/{job_id}.mp4"  # Local URL for now

                job_manager.update_job(
                    job_id,
                    JobStatus.COMPLETED,
                    1.0,
                    "Video generation completed successfully",
                    video_url=video_url,
                )

                logger.info(
                    "Video generation completed",
                    job_id=job_id,
                    video_path=final_path,
                )

            except Exception as e:
                logger.error(
                    "Video generation failed",
                    job_id=job_id,
                    error=str(e),
                    exc_info=True,
                )
                job_manager.update_job(
                    job_id,
                    JobStatus.FAILED,
                    0.0,
                    f"Video generation failed: {str(e)}",
                    error=str(e),
                )

    except Exception as e:
        logger.error(
            "Video generation failed",
            job_id=job_id,
            error=str(e),
            exc_info=True,
        )
        job_manager.update_job(
            job_id,
            JobStatus.FAILED,
            0.0,
            f"Video generation failed: {str(e)}",
            error=str(e),
        )


def parse_script_into_paragraphs(script: str) -> list[str]:
    """Split script into paragraphs for slide creation"""
    # Simple paragraph splitting - can be enhanced with NLP
    paragraphs = [p.strip() for p in script.split("\n\n") if p.strip()]
    return paragraphs