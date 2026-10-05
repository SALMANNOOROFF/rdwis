import pypdf

reader = pypdf.PdfReader("HR Policy Ammended.pdf")
print("Page count:", len(reader.pages))
for i in range(min(5, len(reader.pages))):
    p = reader.pages[i]
    print(f"Page {i+1} images count:", len(p.images))
    for img in p.images:
        print(f"  Img name: {img.name}")
