import asyncio
import subprocess
import structlog
from pathlib import Path
import os
from typing import Optional
from app.config import settings

logger = structlog.get_logger()

class ManimService:
    """Service for generating mathematical animations using Manim"""
    
    async def generate_animation(self, topic: str, output_dir: str, duration: int) -> Optional[str]:
        """
        Generate a Manim animation for mathematical topics.
        
        Returns the path to the generated animation video, or None if failed.
        """
        try:
            # Create a simple Manim scene based on the topic
            scene_code = self._generate_scene_code(topic, duration)
            scene_file = Path(output_dir) / "math_scene.py"
            
            # Write scene code to file
            with open(scene_file, 'w') as f:
                f.write(scene_code)
            
            # Output video path
            video_path = Path(output_dir) / "math_animation.mp4"
            
            # Run Manim to generate animation
            cmd = [
                "manim",
                str(scene_file),
                "MathScene",
                "-o", str(video_path.stem),
                "--format", "mp4",
                "--resolution", f"{settings.VIDEO_WIDTH},{settings.VIDEO_HEIGHT}",
                "--fps", str(settings.VIDEO_FPS),
                "--disable_caching"
            ]
            
            logger.info("Running Manim animation", topic=topic, cmd=cmd)
            
            # Run in subprocess
            process = await asyncio.create_subprocess_exec(
                *cmd,
                cwd=output_dir,
                stdout=asyncio.subprocess.PIPE,
                stderr=asyncio.subprocess.PIPE
            )
            
            stdout, stderr = await process.communicate()
            
            if process.returncode == 0:
                # Manim outputs to media/videos/{scene_name}/{resolution}/{filename}.mp4
                # Resolution folder can be like "1080p30" (height-based) or "1920p30"
                media_dir = Path(output_dir) / "media" / "videos" / "math_scene"
                
                # Try common resolution patterns
                candidates = [
                    media_dir / f"{settings.VIDEO_HEIGHT}p{settings.VIDEO_FPS}" / "MathScene.mp4",
                    media_dir / f"{settings.VIDEO_WIDTH}p{settings.VIDEO_FPS}" / "MathScene.mp4",
                ]
                
                found_path = None
                for candidate in candidates:
                    if candidate.exists():
                        found_path = candidate
                        break
                
                # Fallback: search for any MathScene.mp4 under media_dir
                if found_path is None:
                    for mp4 in media_dir.rglob("MathScene.mp4"):
                        found_path = mp4
                        break
                
                if found_path:
                    # Move to desired location
                    os.rename(str(found_path), str(video_path))
                    logger.info("Manim animation generated", path=str(video_path))
                    return str(video_path)
                else:
                    logger.warning("Manim output not found at expected location")
                    return None
            else:
                logger.error("Manim failed", stderr=stderr.decode(), stdout=stdout.decode())
                return None
                
        except Exception as e:
            logger.error("Manim animation generation failed", error=str(e))
            return None
    
    def _generate_scene_code(self, topic: str, duration: int) -> str:
        """Generate Manim scene code based on the mathematical topic"""
        # This is a simplified example - in production, you'd have more sophisticated
        # scene generation based on the specific mathematical concept
        
        if "quadratic" in topic.lower():
            return self._quadratic_equation_scene()
        elif "geometry" in topic.lower() or "triangle" in topic.lower():
            return self._geometry_scene()
        elif "calculus" in topic.lower() or "derivative" in topic.lower():
            return self._calculus_scene()
        else:
            return self._general_math_scene(topic)
    
    def _quadratic_equation_scene(self) -> str:
        """Generate scene for quadratic equations"""
        return '''
from manim import *

class MathScene(Scene):
    def construct(self):
        # Title
        title = Text("Solving Quadratic Equations", font_size=48)
        self.play(Write(title))
        self.wait(1)
        
        # Equation
        equation = MathTex("ax^2 + bx + c = 0", font_size=36)
        self.play(Transform(title, equation))
        self.wait(1)
        
        # Solution formula
        formula = MathTex("x = \\frac{-b \\pm \\sqrt{b^2 - 4ac}}{2a}", font_size=36)
        self.play(Write(formula.next_to(equation, DOWN)))
        self.wait(2)
        
        # Example
        example = MathTex("x^2 + 5x + 6 = 0", font_size=36)
        self.play(Transform(equation, example))
        self.wait(1)
        
        solution = MathTex("x = \\frac{-5 \\pm \\sqrt{25 - 24}}{2} = \\frac{-5 \\pm 1}{2}", font_size=32)
        self.play(Write(solution.next_to(example, DOWN)))
        self.wait(2)
        
        self.play(FadeOut(VGroup(title, equation, formula, example, solution)))
'''
    
    def _geometry_scene(self) -> str:
        """Generate scene for geometry concepts"""
        return '''
from manim import *

class MathScene(Scene):
    def construct(self):
        # Title
        title = Text("Triangle Properties", font_size=48)
        self.play(Write(title))
        self.wait(1)
        
        # Draw triangle
        triangle = Polygon(
            [-2, -1, 0], [2, -1, 0], [0, 1.5, 0],
            color=BLUE, fill_opacity=0.5
        )
        self.play(Create(triangle))
        self.wait(1)
        
        # Label sides
        side_a = MathTex("a").next_to(triangle, LEFT)
        side_b = MathTex("b").next_to(triangle, RIGHT)
        side_c = MathTex("c").next_to(triangle, DOWN)
        
        self.play(Write(side_a), Write(side_b), Write(side_c))
        self.wait(1)
        
        # Pythagorean theorem
        theorem = MathTex("a^2 + b^2 = c^2", font_size=36)
        self.play(Write(theorem.to_edge(UP)))
        self.wait(2)
        
        self.play(FadeOut(VGroup(title, triangle, side_a, side_b, side_c, theorem)))
'''
    
    def _calculus_scene(self) -> str:
        """Generate scene for calculus concepts"""
        return '''
from manim import *

class MathScene(Scene):
    def construct(self):
        # Title
        title = Text("Derivatives", font_size=48)
        self.play(Write(title))
        self.wait(1)
        
        # Function
        func = MathTex("f(x) = x^2", font_size=36)
        self.play(Write(func))
        self.wait(1)
        
        # Derivative
        deriv = MathTex("f'(x) = 2x", font_size=36)
        self.play(Transform(func, deriv))
        self.wait(2)
        
        # Power rule
        rule = MathTex("\\frac{d}{dx}[x^n] = nx^{n-1}", font_size=36)
        self.play(Write(rule.next_to(deriv, DOWN)))
        self.wait(2)
        
        self.play(FadeOut(VGroup(title, func, deriv, rule)))
'''
    
    def _general_math_scene(self, topic: str) -> str:
        """Generate a general mathematical scene"""
        return f'''
from manim import *

class MathScene(Scene):
    def construct(self):
        # Title
        title = Text("{topic}", font_size=48)
        self.play(Write(title))
        self.wait(2)
        
        # Simple animation
        circle = Circle(radius=1, color=BLUE)
        self.play(Create(circle))
        self.wait(1)
        
        square = Square(side_length=2, color=RED)
        self.play(Transform(circle, square))
        self.wait(1)
        
        self.play(FadeOut(VGroup(title, circle, square)))
'''