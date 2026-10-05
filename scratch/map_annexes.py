import re

with open("scratch/full_policy_ocr.txt", "r", encoding="utf-8") as f:
    text = f.read()

pages = text.split("==================== PAGE ")

annex_map = {}
for i, p in enumerate(pages[1:], 1):
    m = re.findall(r'(ANNEX\s*[-–—]?\s*[A-Z0-9\-]+|RDW/HR/[A-Z0-9\-]+)', p, re.IGNORECASE)
    if m:
        for x in m:
            clean = re.sub(r'\s+', ' ', x).strip().upper()
            if clean not in annex_map:
                annex_map[clean] = []
            annex_map[clean].append(i)

for k, v in sorted(annex_map.items()):
    print(f"{k}: pages {v}")
