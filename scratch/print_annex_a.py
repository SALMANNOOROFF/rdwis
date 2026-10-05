with open("scratch/full_policy_ocr.txt", "r", encoding="utf-8") as f:
    text = f.read()

pages = text.split("==================== PAGE ")

for p in [20, 21]:
    print(f"***** PAGE {p} *****")
    print(pages[p])
