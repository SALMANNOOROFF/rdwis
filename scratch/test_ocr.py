import asyncio
import io
import winocr
from PIL import Image
import pypdf

async def test():
    reader = pypdf.PdfReader("HR Policy Ammended.pdf")
    page = reader.pages[0]
    for img in page.images:
        image = Image.open(io.BytesIO(img.data))
        text = await winocr.recognize_pil(image, lang='en')
        print("Page 1 OCR text:")
        print(text.text[:500])
        break

asyncio.run(test())
