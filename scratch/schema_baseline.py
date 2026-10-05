import hashlib
import json
import psycopg2

conn = psycopg2.connect(
    dbname="updatedrdwV1",
    user="postgres",
    password="12345678",
    host="127.0.0.1",
    port="5433"
)

cur = conn.cursor()
cur.execute("""
    SELECT table_schema, table_name, column_name, data_type, is_nullable, column_default
    FROM information_schema.columns
    WHERE table_schema NOT IN ('pg_catalog', 'information_schema', 'hrforms')
    ORDER BY table_schema, table_name, ordinal_position
""")

rows = cur.fetchall()
data = [list(r) for r in rows]
blob = json.dumps(data, sort_keys=True)
md5 = hashlib.md5(blob.encode('utf-8')).hexdigest()

with open("scratch/baseline_schema_snapshot.json", "w", encoding="utf-8") as f:
    json.dump({"total_columns": len(rows), "md5": md5, "data": data}, f, indent=2)

print(f"Recorded baseline schema: {len(rows)} columns across existing schemas. Hash: {md5}")
conn.close()
