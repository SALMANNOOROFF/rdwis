import asyncio
import winocr
from PIL import Image

async def parse_desigs():
    img = Image.open("scratch/page_34_annex_k.png")
    # Designation column is roughly x=280 to x=600
    y_starts = [220, 275, 335, 395, 450, 510, 570, 630, 685, 745, 800, 860, 920, 980, 1035, 1095, 1150]
    for i in range(len(y_starts)-1):
        y1 = y_starts[i]
        y2 = y_starts[i+1]
        desig_img = img.crop((250, y1, 650, y2))
        res = await winocr.recognize_pil(desig_img, lang='en')
        sal_img = img.crop((700, y1, 1150, y2))
        res_sal = await winocr.recognize_pil(sal_img, lang='en')
        print(f"Row {i:2d}: [{res.text.strip():<30}] -> [{res_sal.text.strip()}]")

asyncio.run(parse_desigs())
