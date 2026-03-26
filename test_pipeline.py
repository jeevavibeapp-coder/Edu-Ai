#!/usr/bin/env python3
"""Test the video generation pipeline directly"""

import asyncio
import sys
import os

# Add the app directory to Python path
sys.path.insert(0, os.path.join(os.path.dirname(__file__), 'app'))

from app.models.video import VideoGenerationRequest, Subject
from app.tasks.video_generation import generate_video_task

async def test_video_generation():
    print("Testing video generation pipeline...")

    request = VideoGenerationRequest(
        class_level=10,
        subject=Subject.MATHEMATICS,
        chapter="Quadratic Equations",
        topic="Solving quadratic equations by factorization",
        duration=30
    )

    job_id = "test-job-123"

    try:
        await generate_video_task(job_id, request)
        print("✅ Video generation completed successfully!")
    except Exception as e:
        print(f"❌ Video generation failed: {str(e)}")
        import traceback
        traceback.print_exc()

if __name__ == "__main__":
    asyncio.run(test_video_generation())