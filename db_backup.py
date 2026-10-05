import os
import sys
import shutil
import subprocess
from datetime import datetime

# ==========================================
# Database Configuration / ڈیٹا بیس کی ترتیبات
# ==========================================
DB_HOST = "127.0.0.1"
DB_PORT = "5433"          # RDWIS database port (5433 for Postgres 18)
DB_NAME = "updatedrdwV1"
DB_USER = "postgres"
DB_PASSWORD = "12345678"

# Root Backup Directory / بیک اپ کا مین فولڈر
BACKUP_ROOT_DIR = r"D:\databackup2.0"

# ==========================================
# Locate pg_dump.exe / pg_dump تلاش کرنے کا فنکشن
# ==========================================
def find_pg_dump():
    possible_paths = [
        r"C:\Program Files\PostgreSQL\18\bin\pg_dump.exe",
        r"C:\Program Files\PostgreSQL\17\bin\pg_dump.exe",
        r"C:\Program Files\PostgreSQL\16\bin\pg_dump.exe",
        r"C:\Program Files\PostgreSQL\15\bin\pg_dump.exe",
        r"C:\Program Files\PostgreSQL\14\bin\pg_dump.exe",
        r"C:\Program Files\PostgreSQL\13\bin\pg_dump.exe",
    ]
    for path in possible_paths:
        if os.path.isfile(path):
            return path
            
    system_dump = shutil.which("pg_dump")
    if system_dump:
        return system_dump
        
    return "pg_dump"


def create_backup():
    # 1. Main backup directory check & creation
    if not os.path.exists(BACKUP_ROOT_DIR):
        print(f"Creating root backup folder: {BACKUP_ROOT_DIR}")
        os.makedirs(BACKUP_ROOT_DIR, exist_ok=True)
    else:
        print(f"Root backup folder already exists: {BACKUP_ROOT_DIR}")

    # 2. Create date & time folder (e.g. 2026-10-01_09-45-10)
    now = datetime.now()
    timestamp_folder = now.strftime("%Y-%m-%d_%H-%M-%S")
    current_backup_dir = os.path.join(BACKUP_ROOT_DIR, timestamp_folder)
    os.makedirs(current_backup_dir, exist_ok=True)

    # Log file inside this timestamp folder
    log_file_path = os.path.join(current_backup_dir, "backup_log.txt")

    def write_log(message):
        """Prints to console and appends to backup_log.txt"""
        print(message)
        with open(log_file_path, "a", encoding="utf-8") as f:
            f.write(message + "\n")

    write_log("=" * 60)
    write_log(f"POSTGRESQL BACKUP LOG")
    write_log(f"Date & Time: {now.strftime('%Y-%m-%d %H:%M:%S')}")
    write_log(f"Database   : {DB_NAME}")
    write_log(f"Host:Port  : {DB_HOST}:{DB_PORT}")
    write_log(f"Username   : {DB_USER}")
    write_log(f"Folder     : {current_backup_dir}")
    write_log("=" * 60)

    # File & Directory paths
    sql_file = os.path.join(current_backup_dir, f"{DB_NAME}.sql")
    dump_dir = os.path.join(current_backup_dir, f"{DB_NAME}_directory")

    pg_dump_bin = find_pg_dump()
    write_log(f"Using pg_dump tool: {pg_dump_bin}")

    # Set password in environment securely
    env = os.environ.copy()
    env["PGPASSWORD"] = DB_PASSWORD

    backup_success = True

    # 3. Create .SQL Backup (Plain text SQL script)
    write_log("\n[1/2] Creating SQL backup (.sql)...")
    sql_cmd = [
        pg_dump_bin,
        "-h", DB_HOST,
        "-p", DB_PORT,
        "-U", DB_USER,
        "-d", DB_NAME,
        "-F", "p",          # Plain SQL format
        "-f", sql_file
    ]

    try:
        res_sql = subprocess.run(sql_cmd, env=env, capture_output=True, text=True, check=True)
        sql_size_mb = os.path.getsize(sql_file) / (1024 * 1024)
        write_log(f" -> STATUS: SUCCESSFUL")
        write_log(f" -> File  : {sql_file}")
        write_log(f" -> Size  : {sql_size_mb:.2f} MB")
        if res_sql.stderr:
            write_log(f" -> Notice: {res_sql.stderr.strip()}")
    except subprocess.CalledProcessError as e:
        backup_success = False
        write_log(f" -> STATUS: FAILED")
        write_log(f" -> ERROR : {e.stderr.strip()}")

    # 4. Create Directory Format Backup (-F d)
    write_log("\n[2/2] Creating Directory type backup (-F d)...")
    dir_cmd = [
        pg_dump_bin,
        "-h", DB_HOST,
        "-p", DB_PORT,
        "-U", DB_USER,
        "-d", DB_NAME,
        "-F", "d",          # Directory format
        "-f", dump_dir
    ]

    try:
        res_dir = subprocess.run(dir_cmd, env=env, capture_output=True, text=True, check=True)
        dir_size_bytes = sum(
            os.path.getsize(os.path.join(dirpath, filename))
            for dirpath, _, filenames in os.walk(dump_dir)
            for filename in filenames
        )
        dir_size_mb = dir_size_bytes / (1024 * 1024)
        write_log(f" -> STATUS: SUCCESSFUL")
        write_log(f" -> Folder: {dump_dir}")
        write_log(f" -> Size  : {dir_size_mb:.2f} MB")
        if res_dir.stderr:
            write_log(f" -> Notice: {res_dir.stderr.strip()}")
    except subprocess.CalledProcessError as e:
        backup_success = False
        write_log(f" -> STATUS: FAILED")
        write_log(f" -> ERROR : {e.stderr.strip()}")

    # 5. Final Summary
    end_now = datetime.now()
    write_log("\n" + "=" * 60)
    if backup_success:
        write_log(f"OVERALL STATUS: ALL BACKUPS COMPLETED SUCCESSFULLY!")
    else:
        write_log(f"OVERALL STATUS: BACKUP COMPLETED WITH ERRORS! CHECK DETAILS ABOVE.")
    write_log(f"Finished At   : {end_now.strftime('%Y-%m-%d %H:%M:%S')}")
    write_log(f"Log File Saved: {log_file_path}")
    write_log("=" * 60)

    return backup_success


if __name__ == "__main__":
    success = create_backup()
    if not success:
        sys.exit(1)
