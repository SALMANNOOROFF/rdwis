import re

with open("scratch/full_policy_ocr.txt", "r", encoding="utf-8") as f:
    text = f.read()

pages = text.split("==================== PAGE ")

for i, page in enumerate(pages[1:], 1):
    m = re.search(r'(ANNEX\s*[-–—]?\s*[A-Z0-9\-]+|RDW/HR/[A-Z0-9\-]+)', page, re.IGNORECASE)
    if m or i >= 19:
        first_few = [l.strip() for l in page.split('\n') if l.strip()][:5]
        print(f"Page {i:2d}: {' | '.join(first_few[:3])}")
