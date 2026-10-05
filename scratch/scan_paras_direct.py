import re

with open("scratch/full_policy_ocr.txt", "r", encoding="utf-8") as f:
    text = f.read()

# Pages 6 to 20 contain main policy
pages = text.split("==================== PAGE ")

for p_num in range(6, 21):
    page = pages[p_num]
    print(f"\n--- PAGE {p_num} ---")
    for match in re.finditer(r'(?:^|\n)\s*(\d{1,2})\.\s+([^\n]+)', page):
        para_num = int(match.group(1))
        para_heading = match.group(2).strip()
        print(f"  Para {para_num}: {para_heading[:80]}")
