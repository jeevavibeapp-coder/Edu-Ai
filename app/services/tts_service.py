import asyncio
import edge_tts
import structlog
from typing import List
from pathlib import Path
from app.config import settings

logger = structlog.get_logger()

class TTSService:
    """Service for generating text-to-speech audio using Edge TTS"""
    
    # Voice mappings for different subjects
    VOICE_MAP = {
        "mathematics": "en-US-AriaNeural",
        "physics": "en-US-ZiraNeural", 
        "chemistry": "en-US-BenjaminNeural",
        "biology": "en-GB-SoniaNeural",
        "history": "en-GB-LibbyNeural",
        "geography": "en-AU-NatashaNeural",
        "english": "en-GB-SoniaNeural",
        "computer_science": "en-US-AriaNeural"
    }
    
    async def generate_audio_batch(self, paragraphs: List[str], output_dir: str, subject: str) -> List[str]:
        """
        Generate audio files for multiple paragraphs in parallel.
        
        Returns list of audio file paths.
        """
        voice = self.VOICE_MAP.get(subject, "en-US-AriaNeural")
        audio_files = []
        
        # Create tasks for parallel processing
        tasks = []
        for i, paragraph in enumerate(paragraphs):
            audio_path = Path(output_dir) / f"audio_{i}.mp3"
            task = self._generate_single_audio(paragraph, str(audio_path), voice)
            tasks.append(task)
        
        # Execute in parallel with semaphore to limit concurrency
        semaphore = asyncio.Semaphore(3)  # Limit to 3 concurrent TTS generations
        
        async def limited_task(task):
            async with semaphore:
                return await task
        
        logger.info("Starting parallel TTS generation", count=len(tasks))
        results = await asyncio.gather(*[limited_task(task) for task in tasks])
        
        # Collect successful audio files
        for result in results:
            if result:
                audio_files.append(result)
        
        logger.info("TTS generation completed", generated=len(audio_files))
        return audio_files
    
    async def _generate_single_audio(self, text: str, output_path: str, voice: str) -> str:
        """Generate audio for a single text segment"""
        try:
            communicate = edge_tts.Communicate(text, voice)
            await communicate.save(output_path)
            
            logger.debug("Audio generated", path=output_path, length=len(text))
            return output_path
            
        except Exception as e:
            logger.error("TTS generation failed", error=str(e), text=text[:50])
            return None