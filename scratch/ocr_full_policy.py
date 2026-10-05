import asyncio
import io
import winocr
from PIL import Image
import pypdf
import time

async def ocr_all():
    start_t = time.time()
    reader = pypdf.PdfReader("HR Policy Ammended.pdf")
    total = len(reader.pages)
    print(f"Starting OCR on {total} pages...")
    
    with open("scratch/full_policy_ocr.txt", "w", encoding="utf-8") as f_out:
        for idx, page in enumerate(reader.pages):
            f_out.write(f"\n\n==================== PAGE {idx+1} ====================\n\n")
            if len(page.images) == 0:
                f_out.write(page.extract_text() or "[NO IMAGES OR TEXT]")
                continue
            for img_idx, img in enumerate(page.images):
                image = Image.open(io.BytesIO(img.data))
                res = await winocr.recognize_pil(image, lang='en')
                f_out.write(res.text + "\n")
            if (idx + 1) % 10 == 0 or idx + 1 == total:
                print(f"Processed {idx+1}/{total} pages ({time.time() - start_t:.1f}s)")

    print(f"Finished OCR in {time.time() - start_t:.1f}s. Saved to scratch/full_policy_ocr.txt")

asyncio.run(ocr_all())
