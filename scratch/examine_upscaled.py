import asyncio
import winocr
from PIL import Image

async def examine_upscaled():
    img = Image.open("scratch/annex_k_left_column.png")
    for y, label in [(176, "row3"), (234, "row4"), (350, "row6")]:
        c = img.crop((180, y, 400, y+58))
        # Upscale 4x
        c = c.resize((c.width * 4, c.height * 4), Image.Resampling.LANCZOS)
        c.save(f"scratch/{label}.png")
        res = await winocr.recognize_pil(c, lang='en')
        print(f"{label} (y={y}): '{res.text.strip()}'")

asyncio.run(examine_upscaled())
