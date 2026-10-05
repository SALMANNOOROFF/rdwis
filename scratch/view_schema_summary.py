import sys
sys.stdout.reconfigure(encoding='utf-8')

with open("scratch/schema_columns.txt", "rb") as f:
    raw = f.read()

text = raw.decode("utf-16le" if raw.startswith(b'\xff\xfe') else "utf-8", errors="ignore").replace("\ufeff", "")

for section in text.split("=== "):
    if not section.strip():
        continue
    lines = section.split("\n")
    header = lines[0].strip()
    cols = [l.strip() for l in lines[1:] if l.strip()]
    print(f"\nTABLE: {header}")
    for c in cols:
        print(f"  {c}")
