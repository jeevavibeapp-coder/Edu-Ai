from pydantic import BaseModel, Field, ConfigDict
from typing import Optional
from enum import Enum


class Subject(str, Enum):
    MATHEMATICS = "mathematics"
    PHYSICS = "physics"
    CHEMISTRY = "chemistry"
    BIOLOGY = "biology"
    HISTORY = "history"
    GEOGRAPHY = "geography"
    ENGLISH = "english"
    COMPUTER_SCIENCE = "computer_science"


class VideoGenerationRequest(BaseModel):
    class_level: int = Field(..., ge=1, le=12, description="Student class level (1-12)")
    subject: Subject
    chapter: str = Field(..., min_length=1, max_length=100, description="Chapter name")
    topic: str = Field(..., min_length=1, max_length=200, description="Specific topic")
    duration: int = Field(..., ge=30, le=300, description="Video duration in seconds")

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


class JobStatus(str, Enum):
    PENDING = "pending"
    PROCESSING = "processing"
    COMPLETED = "completed"
    FAILED = "failed"


class VideoGenerationResponse(BaseModel):
    job_id: str
    status: JobStatus
    message: str
    estimated_time: Optional[int] = None  # seconds
    video_url: Optional[str] = None


class JobStatusResponse(BaseModel):
    job_id: str
    status: JobStatus
    progress: float = Field(..., ge=0.0, le=1.0)  # 0.0 to 1.0
    message: str
    video_url: Optional[str] = None
    error: Optional[str] = None