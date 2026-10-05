import asyncio
import winocr
from PIL import Image, ImageEnhance

async def examine():
    img = Image.open("scratch/annex_k_left_column.png")
    # Increase contrast
    enhancer = ImageEnhance.Contrast(img)
    img_contrasted = enhancer.enhance(2.0)
    res = await winocr.recognize_pil(img_contrasted, lang='en')
    for l in res.lines:
        print(f"y={l.words[0].bounding_rect.y:.1f}, x={l.words[0].bounding_rect.x:.1f}: {l.text}")

asyncio.run(examine())
