with open("scratch/pdf_summary.txt", "r", encoding="utf-8") as f:
    lines = f.readlines()

for idx, line in enumerate(lines):
    l_str = line.strip()
    if any(k in l_str.upper() for k in ["ANNEX", "PARAGRAPH", "TABLE OF CONTENTS", "RDW/HR/", "CONTENTS"]):
        if len(l_str) < 100:
            print(f"L{idx+1}: {l_str}")
