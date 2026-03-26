from pydantic_settings import BaseSettings
from typing import Optional

class Settings(BaseSettings):
    # API Keys
    GEMINI_API_KEY: Optional[str] = None
    HF_API_TOKEN: Optional[str] = None
    REDIS_URL: str = "redis://localhost:6379/0"
    
    # App Settings
    APP_NAME: str = "AI Educational Video Generator"
    DEBUG: bool = False
    HOST: str = "0.0.0.0"
    PORT: int = 8000
    
    # Video Settings
    VIDEO_WIDTH: int = 1920
    VIDEO_HEIGHT: int = 1080
    VIDEO_FPS: int = 30
    AUDIO_SAMPLE_RATE: int = 44100
    
    # Paths
    TEMP_DIR: str = "temp"
    STORAGE_DIR: str = "storage"
    
    # Task Queue
    CELERY_BROKER_URL: str = "redis://localhost:6379/0"
    CELERY_RESULT_BACKEND: str = "redis://localhost:6379/0"
    
    # Limits
    MAX_CONCURRENT_JOBS: int = 5
    MAX_VIDEO_DURATION: int = 300  # seconds
    GEMINI_RATE_LIMIT: int = 60  # requests per minute
    
    class Config:
        env_file = ".env"
        case_sensitive = True

settings = Settings()