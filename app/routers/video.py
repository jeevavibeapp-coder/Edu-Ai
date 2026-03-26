from fastapi import APIRouter, BackgroundTasks, HTTPException
from typing import Dict
import uuid
import structlog

from app.models.video import (
    VideoGenerationRequest,
    VideoGenerationResponse,
    JobStatusResponse,
    JobStatus,
)
from app.tasks.video_generation import generate_video_task
from app.services.job_manager import job_manager
from app.services.combined_ai_service import combined_ai_service

logger = structlog.get_logger()
router = APIRouter()


@router.post("/generate-video", response_model=VideoGenerationResponse)
async def generate_video(
    request: VideoGenerationRequest,
    background_tasks: BackgroundTasks,
):
    """
    Generate an educational video based on the provided parameters.

    This endpoint accepts video generation requests and processes them asynchronously
    as background tasks to handle the computationally intensive video creation.
    """
    job_id = str(uuid.uuid4())

    # Initialize job status
    job_manager.create_job(job_id, request.model_dump())

    logger.info(
        "Video generation job created",
        job_id=job_id,
        subject=request.subject,
        topic=request.topic,
        duration=request.duration,
    )

    # Run video generation as a true background task
    background_tasks.add_task(generate_video_task, job_id, request)

    return VideoGenerationResponse(
        job_id=job_id,
        status=JobStatus.PENDING,
        message="Video generation started",
        estimated_time=request.duration * 2,  # Rough estimate
    )


@router.get("/job/{job_id}", response_model=JobStatusResponse)
async def get_job_status(job_id: str):
    """
    Get the status of a video generation job.

    Returns current progress, status, and video URL if completed.
    """
    job = job_manager.get_job(job_id)
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


@router.get("/jobs", response_model=Dict[str, JobStatusResponse])
async def list_jobs():
    """
    List all active jobs and their statuses.

    Useful for monitoring and debugging.
    """
    jobs = job_manager.list_jobs()
    return {
        job_id: JobStatusResponse(
            job_id=job_id,
            status=job["status"],
            progress=job.get("progress", 0.0),
            message=job.get("message", ""),
            video_url=job.get("video_url"),
            error=job.get("error"),
        )
        for job_id, job in jobs.items()
    }


@router.get("/status")
async def get_system_status():
    """
    Get system status including available AI services.

    Returns information about which AI services are configured and available.
    """
    ai_status = combined_ai_service.get_service_status()

    return {
        "system": "AI Educational Video Generator",
        "version": "1.0.0",
        "ai_services": ai_status,
        "recommendation": (
            "Gemini"
            if ai_status["gemini_available"]
            else "Hugging Face"
            if ai_status["huggingface_available"]
            else "Mock scripts only"
        ),
    }