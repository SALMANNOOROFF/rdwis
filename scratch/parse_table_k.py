import asyncio
import winocr
from PIL import Image

async def parse_table():
    img = Image.open("scratch/page_34_annex_k.png")
    # Table bounds approximately: y from 200 to 1250, x from 100 to 1150
    # Let's crop into horizontal slices of ~58 pixels
    y_starts = [220, 275, 335, 395, 450, 510, 570, 630, 685, 745, 800, 860, 920, 980, 1035, 1095, 1150]
    for i in range(len(y_starts)-1):
        y1 = y_starts[i]
        y2 = y_starts[i+1]
        row_img = img.crop((100, y1, 1150, y2))
        res = await winocr.recognize_pil(row_img, lang='en')
        print(f"Row {i} (y={y1}-{y2}): {res.text.strip()}")

asyncio.run(parse_table())
