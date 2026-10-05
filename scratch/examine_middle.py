import asyncio
import winocr
from PIL import Image

async def examine_middle():
    img = Image.open("scratch/annex_k_left_column.png")
    # let's slice between y=60 and y=450 in 60-pixel slices
    for y in range(60, 450, 58):
        c = img.crop((180, y, 450, y+58))
        res = await winocr.recognize_pil(c, lang='en')
        print(f"y={y}-{y+58}: {res.text.strip()}")

asyncio.run(examine_middle())
