import asyncio
import io
import winocr
from PIL import Image
import pypdf

async def inspect_page34():
    reader = pypdf.PdfReader("HR Policy Ammended.pdf")
    page = reader.pages[33] # 0-indexed page 34
    for img in page.images:
        image = Image.open(io.BytesIO(img.data))
        res = await winocr.recognize_pil(image, lang='en')
        for line in res.lines:
            words = " ".join([w.text for w in line.words])
            print(f"y={line.words[0].bounding_rect.y:.1f}, x={line.words[0].bounding_rect.x:.1f}: {words}")

asyncio.run(inspect_page34())
