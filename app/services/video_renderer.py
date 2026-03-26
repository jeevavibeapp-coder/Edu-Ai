from moviepy import VideoFileClip, AudioFileClip, ImageClip, concatenate_videoclips, CompositeVideoClip
import structlog
from typing import List, Optional
from pathlib import Path
import math
from app.config import settings

logger = structlog.get_logger()

class VideoRenderer:
    """Service for rendering final video using MoviePy"""
    
    def render_video(self, slide_files: List[str], audio_files: List[str], 
                    manim_video: Optional[str], output_dir: str, 
                    target_duration: int, subject: str) -> str:
        """
        Render the final educational video by combining slides, audio, and animations.
        
        Returns the path to the final video file.
        """
        try:
            clips = []
            audio_clips = []  # Track audio clips for cleanup
            total_audio_duration = 0
            
            # Calculate timing for each slide
            num_slides = len(slide_files)
            if not audio_files:
                # No audio files, create video with fixed duration per slide
                target_slide_duration = target_duration / num_slides if num_slides > 0 else target_duration
            else:
                target_slide_duration = target_duration / num_slides
            
            for i, slide_path in enumerate(slide_files):
                if audio_files and i < len(audio_files):
                    # Load audio to get duration
                    audio_clip = AudioFileClip(audio_files[i])
                    audio_duration = audio_clip.duration
                    total_audio_duration += audio_duration
                    
                    # Create image clip with audio duration
                    slide_clip = ImageClip(slide_path, duration=audio_duration)
                    
                    # Set audio
                    slide_clip = slide_clip.with_audio(audio_clip)
                    
                    # Store audio clip for later cleanup
                    audio_clips.append(audio_clip)
                else:
                    # No audio for this slide, use target duration
                    slide_clip = ImageClip(slide_path, duration=target_slide_duration)
                
                # Add fade transitions (disabled for now)
                # if i > 0:
                #     slide_clip = slide_clip.fadein(0.5)
                
                clips.append(slide_clip)
            
            # Handle Manim animation for Maths
            if manim_video and subject == "mathematics":
                manim_clip = VideoFileClip(manim_video)
                # Insert Manim animation in the middle
                mid_point = len(clips) // 2
                clips.insert(mid_point, manim_clip)
                total_audio_duration += manim_clip.duration
            
            # Concatenate all clips
            final_clip = concatenate_videoclips(clips, method="compose", padding=-0.5)  # Negative padding for crossfade
            
            # Ensure total duration matches target
            if final_clip.duration < target_duration:
                # Add padding if needed
                final_clip = final_clip.resized(width=settings.VIDEO_WIDTH, height=settings.VIDEO_HEIGHT)
            elif final_clip.duration > target_duration:
                # Trim if too long
                final_clip = final_clip.subclipped(0, target_duration)
            
            # Set final video properties
            # final_clip = final_clip.set_fps(settings.VIDEO_FPS)  # Removed - not available on CompositeVideoClip
            
            # Output path
            output_path = Path(output_dir) / "final_video.mp4"
            
            # Render video
            logger.info("Rendering video", output_path=str(output_path), duration=final_clip.duration)
            final_clip.write_videofile(
                str(output_path),
                fps=settings.VIDEO_FPS,
                codec="libx264",
                audio_codec="aac",
                temp_audiofile=str(Path(output_dir) / "temp_audio.m4a"),
                remove_temp=True
            )
            
            # Close clips to free memory and file handles
            final_clip.close()
            for clip in clips:
                clip.close()
                if hasattr(clip, 'audio') and clip.audio:
                    clip.audio.close()
            
            # Close standalone audio clips
            for audio_clip in audio_clips:
                audio_clip.close()
            
            # Small delay to ensure file handles are released
            import time
            time.sleep(0.5)
            
            logger.info("Video rendering completed", path=str(output_path))
            return str(output_path)
            
        except Exception as e:
            logger.error("Video rendering failed", error=str(e))
            raise Exception(f"Failed to render video: {str(e)}")