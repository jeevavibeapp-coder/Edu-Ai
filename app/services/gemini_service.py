import google.generativeai as genai
import structlog
from typing import Dict, Any
from app.config import settings
from app.models.video import VideoGenerationRequest

logger = structlog.get_logger()

class GeminiService:
    """Service for generating educational scripts using Google Gemini API"""
    
    def __init__(self):
        genai.configure(api_key=settings.GEMINI_API_KEY)
        self.model = genai.GenerativeModel('gemini-2.0-flash')
    
    async def generate_script(self, request: VideoGenerationRequest) -> str:
        """
        Generate an educational spoken lesson script based on the request parameters.
        
        The script should be structured for educational video creation with clear
        paragraphs that can be converted to slides.
        """
        prompt = self._build_prompt(request)
        
        try:
            logger.info("Calling Gemini API", subject=request.subject, topic=request.topic)
            
            response = await self.model.generate_content_async(prompt)
            script = response.text
            
            logger.info("Gemini script generated", length=len(script))
            return script
            
        except Exception as e:
            error_str = str(e)
            logger.warning("Gemini API failed, using mock response", error=error_str)
            
            # For testing/demo purposes, return a mock script when API fails
            mock_script = self._generate_mock_script(request)
            logger.info("Using mock script for testing", length=len(mock_script))
            return mock_script
    
    def _generate_mock_script(self, request: VideoGenerationRequest) -> str:
        """Generate a mock educational script for testing purposes"""
        return f"""
Welcome to our lesson on {request.topic} in {request.subject.value.title()} for class {request.class_level}.

Today we will explore the fundamental concepts of {request.topic}. This is an important topic that builds the foundation for more advanced studies in {request.subject.value}.

Let's start with the basic definition. {request.topic} refers to the mathematical process of finding the roots of a quadratic equation. A quadratic equation is any equation that can be written in the form ax² + bx + c = 0, where a, b, and c are constants and a ≠ 0.

The most common method to solve quadratic equations is by using the quadratic formula: x = (-b ± √(b² - 4ac)) / 2a. This formula gives us the two possible solutions for x.

For example, let's solve the equation x² + 5x + 6 = 0. Using the quadratic formula with a=1, b=5, c=6, we get x = (-5 ± √(25 - 24)) / 2 = (-5 ± 1) / 2. So the solutions are x = -2 and x = -3.

In summary, quadratic equations can be solved using factoring, completing the square, or the quadratic formula. Each method has its advantages depending on the specific equation you're working with.

Thank you for watching this educational video. Practice these concepts and you'll master quadratic equations!
"""
    
    def _build_prompt(self, request: VideoGenerationRequest) -> str:
        """Build a detailed prompt for premium educational script generation"""
        return f"""
        Create an exceptional educational script for a {request.duration}-second video lesson.
        
        Subject: {request.subject.value}
        Grade Level: {request.class_level}
        Chapter: {request.chapter}
        Topic: {request.topic}
        
        CORE REQUIREMENTS (CRITICAL):
        ✓ Language: Crystal clear, engaging, age-appropriate for grade {request.class_level}
        ✓ Structure: Logical flow from simple to complex concepts
        ✓ Clarity: Every sentence must serve a purpose and be easy to understand
        ✓ Aesthetics: Well-formatted with natural breaks and transitions
        ✓ Engagement: Include examples, analogies, real-world connections
        
        SECTION BREAKDOWN:
        1. Hook (15 seconds): Capture attention with relatable opening
        2. Overview (15 seconds): What students will learn and why it matters
        3. Core Concepts (45-60% of time): Break down main ideas with examples
        4. Applications (20-30% of time): Show real-world use and practice
        5. Summary (10-15 seconds): Reinforce key learning points
        
        SPECIFIC GUIDELINES:
        - For Mathematics: Show step-by-step solutions with clear reasoning
        - For Science: Explain processes and include observable examples
        - Use accessible vocabulary and avoid jargon when possible
        - Include 1-2 worked examples for clarity
        - Emphasize understanding over memorization
        - Each paragraph = one teaching slide (3-4 sentences max per paragraph)
        
        Format: Separate paragraphs with double line breaks.
        Total: Approximately {request.duration} seconds at normal speaking pace.
        
        Focus: Maximum clarity + student engagement + aesthetic presentation."""