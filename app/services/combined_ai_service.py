import structlog
import os
from typing import Optional, Tuple
from app.models.video import VideoGenerationRequest

logger = structlog.get_logger()

class CombinedAIService:
    """
    Combined AI service that uses both Gemini (paid) and Hugging Face (free)
    for optimal quality and reliability.
    """

    def __init__(self):
        self.gemini_available = bool(os.getenv("GEMINI_API_KEY"))
        self.hf_available = bool(os.getenv("HF_API_TOKEN"))

        # Lazy import to avoid loading if not needed
        self._gemini_service = None
        self._hf_service = None

    def _get_gemini_service(self):
        if self._gemini_service is None and self.gemini_available:
            try:
                from app.services.gemini_service import GeminiService
                self._gemini_service = GeminiService()
            except Exception as e:
                logger.warning("Failed to initialize Gemini service", error=str(e))
                self.gemini_available = False
        return self._gemini_service

    def _get_hf_service(self):
        if self._hf_service is None and self.hf_available:
            try:
                from app.services.huggingface_service import HuggingFaceService
                self._hf_service = HuggingFaceService()
            except Exception as e:
                logger.warning("Failed to initialize Hugging Face service", error=str(e))
                self.hf_available = False
        return self._hf_service

    async def generate_script(self, request: VideoGenerationRequest) -> str:
        """
        Generate educational script using the best available AI service.
        Priority: Hugging Face (free) -> Gemini (paid) -> Mock (fallback)
        Prioritizes free services to minimize costs while maintaining quality.
        """
        script = None
        source = None

        # Try Hugging Face first (free, good quality)
        if self.hf_available:
            try:
                logger.info("Attempting script generation with Hugging Face (free tier, preferred)")
                hf_service = self._get_hf_service()
                if hf_service:
                    script = await hf_service.generate_script(request)
                    if script and len(script.strip()) > 50:  # Basic quality check
                        source = "Hugging Face"
                        logger.info("Successfully generated script with Hugging Face", length=len(script))
                    else:
                        logger.warning("Hugging Face returned poor quality script, trying Gemini")
                        script = None
            except Exception as e:
                logger.error("Hugging Face generation failed", error=str(e))

        # Fall back to Gemini only if HF unavailable (paid service)
        if script is None and self.gemini_available:
            try:
                logger.info("Attempting script generation with Gemini (paid, fallback only)")
                gemini_service = self._get_gemini_service()
                if gemini_service:
                    script = await gemini_service.generate_script(request)
                    if script and len(script.strip()) > 50:  # Basic quality check
                        source = "Gemini"
                        logger.info("Successfully generated script with Gemini (paid)", length=len(script))
                    else:
                        logger.warning("Gemini returned poor quality script, using mock")
                        script = None
            except Exception as e:
                logger.error("Gemini generation failed", error=str(e))

        # Final fallback to enhanced mock
        if script is None:
            logger.info("Using enhanced mock script as final fallback")
            script = self._enhanced_mock_script(request)
            source = "Mock"

        logger.info("Script generation completed", source=source, length=len(script))
        return script

    def _enhanced_mock_script(self, request: VideoGenerationRequest) -> str:
        """Enhanced mock script with clear structure and aesthetic presentation"""
        subject = request.subject.replace("_", " ").title()
        topic = request.topic
        
        return f"""Welcome to {subject} Learning Studio

Today's Topic: {topic}

═══════════════════════════════════════════════════

📚 What You'll Learn

In this session, we'll explore the core concepts of {topic}. This is a fundamental skill that builds the foundation for advanced topics in {subject}.

───────────────────────────────────────────────────

🎯 Core Concept

{topic} is based on important principles that you'll use throughout your {subject} studies. Understanding this concept opens doors to solving complex problems.

Key Ideas:
• Foundation principles that underpin this topic
• Real-world applications and examples
• Connection to what you've already learned

───────────────────────────────────────────────────

📖 Breaking It Down

Let's approach this methodically in three simple steps:

Step One: Understanding the Foundation
Begin with the basic building blocks. These fundamental ideas form the foundation of everything we'll discuss.

Step Two: Seeing It in Action
Now we'll see how this concept works through practical examples. Watch carefully as we apply theory to reality.

Step Three: Practicing Together
With clear understanding comes confidence. We'll work through examples together to reinforce your learning.

───────────────────────────────────────────────────

💡 Real-World Application

This concept isn't just academic. You'll encounter these ideas in various fields and real-life situations. Understanding it deeply gives you powerful problem-solving tools.

───────────────────────────────────────────────────

✨ Key Takeaway

Remember: mastery comes through understanding, not memorization. Take time to really grasp these concepts, ask questions when something's unclear, and practice regularly.

───────────────────────────────────────────────────

Your journey in {subject} is built on foundations like this. Keep learning, stay curious, and don't hesitate to revisit challenging concepts.

Thank you for learning with us!"""

    def _mock_script(self, request: VideoGenerationRequest) -> str:
        """Simple fallback mock script"""
        return self._enhanced_mock_script(request)

    def get_service_status(self) -> dict:
        """Get status of available AI services"""
        return {
            "gemini_available": self.gemini_available,
            "huggingface_available": self.hf_available,
            "services": {
                "gemini": "available" if self.gemini_available else "unavailable",
                "huggingface": "available" if self.hf_available else "unavailable"
            }
        }

# Global instance
combined_ai_service = CombinedAIService()