from contextlib import asynccontextmanager
from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from fastapi.staticfiles import StaticFiles
import structlog
import uvicorn
import os

from app.config import settings
from app.routers import video
from app.utils.logging import setup_logging

# Setup structured logging
setup_logging()

logger = structlog.get_logger()


@asynccontextmanager
async def lifespan(app: FastAPI):
    """Application lifespan manager"""
    logger.info("Starting AI Educational Video Generator", app_name=settings.APP_NAME)
    
    # Ensure storage directory exists
    os.makedirs(settings.STORAGE_DIR, exist_ok=True)
    os.makedirs(settings.TEMP_DIR, exist_ok=True)
    
    yield
    
    logger.info("Shutting down AI Educational Video Generator")


app = FastAPI(
    title=settings.APP_NAME,
    description="AI-powered educational video generation backend",
    version="1.0.0",
    debug=settings.DEBUG,
    lifespan=lifespan,
)

# CORS middleware — restrict origins for production
allowed_origins = [
    "http://localhost:3000",
    "http://localhost:8080",
]
if settings.DEBUG:
    allowed_origins.append("*")

app.add_middleware(
    CORSMiddleware,
    allow_origins=allowed_origins,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Serve generated videos from storage/
app.mount("/storage", StaticFiles(directory=settings.STORAGE_DIR), name="storage")

# Include routers
app.include_router(video.router, prefix="/api/v1", tags=["video"])


@app.get("/health")
async def health_check():
    """Health check endpoint"""
    return {"status": "healthy", "service": settings.APP_NAME}


if __name__ == "__main__":
    uvicorn.run(
        "app.main:app",
        host=settings.HOST,
        port=settings.PORT,
        reload=settings.DEBUG,
        log_config=None,  # Use our custom logging
    )
