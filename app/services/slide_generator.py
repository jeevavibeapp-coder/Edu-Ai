from PIL import Image, ImageDraw, ImageFont
import structlog
from typing import List
from pathlib import Path
import textwrap
from app.config import settings

logger = structlog.get_logger()

class SlideGenerator:
    """Service for generating educational slides using Pillow"""
    
    def __init__(self):
        self.width = settings.VIDEO_WIDTH
        self.height = settings.VIDEO_HEIGHT
        
        # Try to load fonts, fallback to default
        try:
            self.title_font = ImageFont.truetype("arial.ttf", 60)
            self.body_font = ImageFont.truetype("arial.ttf", 40)
        except OSError:
            self.title_font = ImageFont.load_default()
            self.body_font = ImageFont.load_default()
    
    def generate_slides(self, paragraphs: List[str], output_dir: str, subject: str) -> List[str]:
        """
        Generate slide images for each paragraph.
        
        Returns list of slide image file paths.
        """
        slide_files = []
        
        for i, paragraph in enumerate(paragraphs):
            slide_path = Path(output_dir) / f"slide_{i}.png"
            self._create_slide(paragraph, str(slide_path), subject, i + 1, len(paragraphs))
            slide_files.append(str(slide_path))
        
        logger.info("Slides generated", count=len(slide_files))
        return slide_files
    
    def _create_slide(self, text: str, output_path: str, subject: str, slide_num: int, total_slides: int):
        """Create a single slide image"""
        # Create image with white background
        img = Image.new('RGB', (self.width, self.height), color='white')
        draw = ImageDraw.Draw(img)
        
        # Colors based on subject
        colors = self._get_subject_colors(subject)
        
        # Draw header
        header_text = f"{subject.title()} - Slide {slide_num}/{total_slides}"
        draw.text((50, 50), header_text, fill=colors['header'], font=self.title_font)
        
        # Wrap and draw main text
        wrapped_text = textwrap.fill(text, width=80)  # Adjust width as needed
        y_position = 150
        
        for line in wrapped_text.split('\n'):
            draw.text((50, y_position), line, fill=colors['text'], font=self.body_font)
            y_position += 50
        
        # Add some visual elements
        self._add_visual_elements(draw, colors, slide_num)
        
        # Save image
        img.save(output_path, 'PNG')
        logger.debug("Slide created", path=output_path)
    
    def _get_subject_colors(self, subject: str) -> dict:
        """Get color scheme for subject"""
        color_schemes = {
            "mathematics": {"header": "blue", "text": "black", "accent": "red"},
            "physics": {"header": "purple", "text": "black", "accent": "green"},
            "chemistry": {"header": "green", "text": "black", "accent": "orange"},
            "biology": {"header": "darkgreen", "text": "black", "accent": "blue"},
            "history": {"header": "brown", "text": "black", "accent": "gold"},
            "geography": {"header": "teal", "text": "black", "accent": "red"},
            "english": {"header": "navy", "text": "black", "accent": "purple"},
            "computer_science": {"header": "darkblue", "text": "black", "accent": "green"}
        }
        return color_schemes.get(subject, {"header": "black", "text": "black", "accent": "gray"})
    
    def _add_visual_elements(self, draw: ImageDraw, colors: dict, slide_num: int):
        """Add visual elements like borders or shapes"""
        # Simple border
        draw.rectangle([20, 20, self.width-20, self.height-20], 
                      outline=colors['accent'], width=3)
        
        # Slide number indicator
        draw.ellipse([self.width-100, self.height-100, self.width-50, self.height-50], 
                    fill=colors['accent'])
        draw.text((self.width-85, self.height-85), str(slide_num), 
                 fill="white", font=self.body_font)