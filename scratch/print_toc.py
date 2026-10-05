with open("scratch/full_policy_ocr.txt", "r", encoding="utf-8") as f:
    text = f.read()

pages = text.split("==================== PAGE ")

for i in range(1, 7):
    print(f"--- PAGE {i} ---")
    print(pages[i][:1500])
