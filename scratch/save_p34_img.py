import io
import pypdf
from PIL import Image

reader = pypdf.PdfReader("HR Policy Ammended.pdf")
page = reader.pages[33]
for idx, img in enumerate(page.images):
    image = Image.open(io.BytesIO(img.data))
    image.save(f"scratch/page_34_annex_k.png")
    print("Saved scratch/page_34_annex_k.png", image.size)
