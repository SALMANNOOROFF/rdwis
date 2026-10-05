import re

with open("scratch/full_policy_ocr.txt", "r", encoding="utf-8") as f:
    text = f.read()

pages = text.split("==================== PAGE ")

for i in range(1, 25):
    print(f"\n==================== PAGE {i} ====================")
    print(pages[i])
