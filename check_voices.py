#!/usr/bin/env python3
"""Check available Edge TTS voices"""

import asyncio
import edge_tts

async def check_voices():
    voices = await edge_tts.list_voices()
    print("Available voices:")
    for i, voice in enumerate(voices[:20]):  # Show first 20
        print(f"  {voice['ShortName']} - {voice['Locale']} - {voice['Gender']}")

if __name__ == "__main__":
    asyncio.run(check_voices())