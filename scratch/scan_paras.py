import re

with open("scratch/main_policy_pages_1_24.txt", "r", encoding="utf-8") as f:
    text = f.read()

# Find paragraph patterns like "10.", "Para 10", etc.
for match in re.finditer(r'(?:^|\n)\s*(\d{1,2})\.\s+([^\n]+)', text):
    para_num = int(match.group(1))
    para_heading = match.group(2).strip()
    print(f"Para {para_num}: {para_heading[:80]}")
