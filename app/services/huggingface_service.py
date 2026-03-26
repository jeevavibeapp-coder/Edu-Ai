import httpx
import structlog
import os
from typing import Optional
from app.models.video import VideoGenerationRequest

logger = structlog.get_logger()


class HuggingFaceService:
    def __init__(self):
        self.api_token = os.getenv("HF_API_TOKEN")

        # Use TinyLlama via HF Inference API (free tier)
        self.api_url = "https://api-inference.huggingface.co/models/TinyLlama/TinyLlama-1.1B-Chat-v1.0"
        self.headers = (
            {"Authorization": f"Bearer {self.api_token}"} if self.api_token else {}
        )

    async def generate_script(self, request: VideoGenerationRequest) -> str:
        """Generate educational script using Hugging Face API"""
        if not self.api_token:
            logger.warning("HF_API_TOKEN not set, using mock script")
            return self._mock_script(request)

        try:
            # Create enhanced educational prompt emphasizing clarity and aesthetics
            prompt = f"""You are an expert educational content creator. Write a clear, engaging script for {request.class_level}th grade students about {request.topic} in {request.subject}.

IMPORTANT REQUIREMENTS:
- Make explanations crystal clear and easy to understand
- Use simple vocabulary appropriate for grade {request.class_level}
- Structure content logically with clear transitions
- Include real-world examples where possible
- Use formatting and visual breaks to make content aesthetic
- Keep it suitable for a {request.duration}-second video with voice narration
- Focus on understanding over memorization

STRUCTURE:
1. Hook: Start with an engaging opening
2. Overview: What students will learn
3. Core concepts: Main ideas explained simply
4. Examples: Practical applications
5. Summary: Key takeaway message

Return ONLY the script content, no additional text.

Educational Script:"""

            # API payload
            payload = {
                "inputs": prompt,
                "parameters": {
                    "max_new_tokens": 600,
                    "temperature": 0.6,
                    "do_sample": True,
                    "return_full_text": False,
                },
            }

            logger.info(
                "Calling Hugging Face API (cost-optimized, free tier)",
                model="TinyLlama-1.1B",
            )

            # Use async httpx instead of blocking requests
            async with httpx.AsyncClient(timeout=30.0) as client:
                response = await client.post(
                    self.api_url, headers=self.headers, json=payload
                )
                response.raise_for_status()

            result = response.json()

            # Extract generated text
            if isinstance(result, list) and result:
                generated_text = result[0].get("generated_text", "").strip()
            elif isinstance(result, dict):
                generated_text = result.get("generated_text", "").strip()
            else:
                generated_text = ""

            if not generated_text:
                raise Exception("Empty response from Hugging Face API")

            # Clean up response (remove any prompt repetition)
            if generated_text.startswith(prompt[:50]):  # Rough check
                generated_text = generated_text[len(prompt) :].strip()

            logger.info("Script generated via Hugging Face", length=len(generated_text))
            return generated_text

        except httpx.HTTPStatusError as e:
            logger.error("Hugging Face API request failed", error=str(e))
            return self._mock_script(request)
        except Exception as e:
            logger.error("Hugging Face generation failed", error=str(e))
            return self._mock_script(request)

    def _mock_script(self, request: VideoGenerationRequest) -> str:
        """Mock script for fallback"""
        return f"""Welcome to {request.subject} class!

Today we're learning about {request.topic}.

This is an important concept that you'll use throughout your studies.

Let's break it down step by step.

First, understand the basic principles.

Then, see how it applies in real life.

Finally, practice with some examples.

Remember to ask questions if anything is unclear!

Keep practicing and you'll master this topic."""


# Global instance - lazy initialization
_huggingface_service = None


def get_huggingface_service():
    global _huggingface_service
    if _huggingface_service is None:
        _huggingface_service = HuggingFaceService()
    return _huggingface_service


# For backward compatibility
huggingface_service = get_huggingface_service()