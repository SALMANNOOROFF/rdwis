with open("scratch/full_policy_ocr.txt", "r", encoding="utf-8") as f:
    text = f.read()

pages = text.split("==================== PAGE ")

print(f"Total pages extracted: {len(pages)-1}")

# Search for Annexes and Forms
import re

for i, page in enumerate(pages[1:], 1):
    lines = [l.strip() for l in page.split("\n") if l.strip()]
    for line in lines:
        if re.search(r'\bANNEX\b', line, re.I) or re.search(r'\bRDW/HR/\b', line, re.I) or re.search(r'\bTABLE OF CONTENTS\b', line, re.I):
            print(f"P.{i}: {line[:100]}")
