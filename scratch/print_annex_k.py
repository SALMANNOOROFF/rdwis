with open("scratch/full_policy_ocr.txt", "r", encoding="utf-8") as f:
    text = f.read()

pages = text.split("==================== PAGE ")
print("***** PAGE 34 (ANNEX K) *****")
print(pages[34])
