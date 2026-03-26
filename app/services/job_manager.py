from typing import Dict, Any, Optional
import time
from app.models.video import JobStatus

class JobManager:
    """Simple in-memory job manager. Replace with Redis/DB for production."""
    
    def __init__(self):
        self.jobs: Dict[str, Dict[str, Any]] = {}
    
    def create_job(self, job_id: str, request_data: Dict[str, Any]):
        """Create a new job with initial status"""
        self.jobs[job_id] = {
            "status": JobStatus.PENDING,
            "progress": 0.0,
            "message": "Job created",
            "request": request_data,
            "created_at": time.time(),
            "updated_at": time.time()
        }
    
    def update_job(self, job_id: str, status: JobStatus, progress: float = 0.0, 
                   message: str = "", video_url: Optional[str] = None, 
                   error: Optional[str] = None):
        """Update job status and progress"""
        if job_id in self.jobs:
            self.jobs[job_id].update({
                "status": status,
                "progress": progress,
                "message": message,
                "video_url": video_url,
                "error": error,
                "updated_at": time.time()
            })
    
    def get_job(self, job_id: str) -> Optional[Dict[str, Any]]:
        """Get job details"""
        return self.jobs.get(job_id)
    
    def list_jobs(self) -> Dict[str, Dict[str, Any]]:
        """List all jobs"""
        return self.jobs
    
    def delete_job(self, job_id: str):
        """Delete a completed/failed job"""
        self.jobs.pop(job_id, None)

# Global instance
job_manager = JobManager()