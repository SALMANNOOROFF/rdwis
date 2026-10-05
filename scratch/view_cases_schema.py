import sys
sys.stdout.reconfigure(encoding='utf-8')

with open("scratch/schema_columns.txt", "rb") as f:
    raw = f.read()

text = raw.decode("utf-16le" if raw.startswith(b'\xff\xfe') else "utf-8", errors="ignore").replace("\ufeff", "")

target_tables = ['hr.ctrcases', 'hr.ctrcaseplans', 'hr.contracts', 'hr.emps']
for section in text.split("=== "):
    header = section.split("\n")[0].strip()
    for t in target_tables:
        if header.startswith(t):
            print(f"\nTABLE: {header}")
            for line in section.split("\n")[1:]:
                if line.strip():
                    print(f"  {line.strip()}")
