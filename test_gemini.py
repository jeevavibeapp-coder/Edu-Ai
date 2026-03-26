#!/usr/bin/env python3
"""Test script to verify Gemini API key"""

import os
import asyncio
import google.generativeai as genai
from app.config import settings

async def test_gemini():
    print(f"Testing Gemini API with key: {settings.GEMINI_API_KEY[:10]}...")
    
    genai.configure(api_key=settings.GEMINI_API_KEY)
    
    try:
        # List available models
        models = genai.list_models()
        print("Available models:")
        for model in models:
            print(f"  - {model.name}")
        
        # Try with gemini-2.0-flash
        model = genai.GenerativeModel('gemini-2.0-flash')
        
        response = await model.generate_content_async("Hello, test message")
        print("✅ Gemini API call successful!")
        print(f"Response: {response.text}")
        return True
    except Exception as e:
        print(f"❌ Gemini API call failed: {str(e)}")
        return False

if __name__ == "__main__":
    asyncio.run(test_gemini())