with open("scratch/full_policy_ocr.txt", "r", encoding="utf-8") as f:
    text = f.read()

pages = text.split("==================== PAGE ")

for p in range(10, 14):
    print(f"***** PAGE {p} *****")
    print(pages[p])
