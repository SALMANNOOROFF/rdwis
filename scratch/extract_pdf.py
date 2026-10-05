import pypdf
import os

pdf_path = "HR Policy Ammended.pdf"
reader = pypdf.PdfReader(pdf_path)
print(f"Total pages: {len(reader.pages)}")

# Print outline / bookmarks if any
try:
    outline = reader.outline
    print("Outline found:", len(outline))
except Exception as e:
    print("No outline:", e)

# Extract first 5 pages to see structure
with open("scratch/pdf_summary.txt", "w", encoding="utf-8") as out:
    for i, page in enumerate(reader.pages):
        text = page.extract_text() or ""
        out.write(f"\n--- PAGE {i+1} ---\n")
        out.write(text)

print("Extracted all pages to scratch/pdf_summary.txt")
