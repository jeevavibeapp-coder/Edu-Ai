import pytest
from fastapi.testclient import TestClient
from app.main import app

client = TestClient(app)

def test_health_check():
    """Test health check endpoint"""
    response = client.get("/health")
    assert response.status_code == 200
    assert response.json() == {"status": "healthy", "service": "AI Educational Video Generator"}

def test_generate_video_invalid_data():
    """Test video generation with invalid data"""
    response = client.post("/api/v1/generate-video", json={})
    assert response.status_code == 422  # Validation error

def test_generate_video_valid_data():
    """Test video generation endpoint accepts valid data"""
    data = {
        "class_level": 10,
        "subject": "mathematics",
        "chapter": "Test Chapter",
        "topic": "Test Topic",
        "duration": 60
    }
    response = client.post("/api/v1/generate-video", json=data)
    assert response.status_code == 200
    result = response.json()
    assert "job_id" in result
    assert result["status"] == "pending"

def test_job_status_not_found():
    """Test job status for non-existent job"""
    response = client.get("/api/v1/job/non-existent-job")
    assert response.status_code == 404