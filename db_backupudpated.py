import os
import sys
import shutil
import subprocess
import traceback
from datetime import datetime

# ==========================================
# Database Configuration
# ==========================================
DB_HOST = "127.0.0.1"
DB_PORT = "5432"          # RDWIS database port (5433 for Postgres 18)
DB_NAME = "rdw"
DB_USER = "postgres"
DB_PASSWORD = "Dev@123"

# ==========================================
# Backup Destination Folders
# ==========================================
# 1. Local PC Backup Directory
LOCAL_BACKUP_ROOT = r"D:\databackup2.0"

# 2. Network Share Backup Directory
NETWORK_BACKUP_ROOT = r"\\10.120.29.100\rdwis 2.0 backup"


# ==========================================
# Locate pg_dump.exe Binary
# ==========================================
def find_pg_dump():
    """Finds the pg_dump executable path across common PostgreSQL installation directories."""
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


def create_dual_backup():
    """
    Creates a PostgreSQL database backup on the local machine
    and synchronizes it to a network share location.
    """
    now = datetime.now()
    timestamp_folder = now.strftime("%Y-%m-%d_%H-%M-%S")
    
    local_target_dir = os.path.join(LOCAL_BACKUP_ROOT, timestamp_folder)
    network_target_dir = os.path.join(NETWORK_BACKUP_ROOT, timestamp_folder)
    
    local_log_path = os.path.join(local_target_dir, "backup_log.txt")
    network_log_path = os.path.join(network_target_dir, "backup_log.txt")

    log_buffer = []

    def log(message):
        """Prints message to console and buffers for writing to log files."""
        print(message)
        log_buffer.append(message)

    log("=" * 75)
    log("               POSTGRESQL DUAL BACKUP LOG (LOCAL & NETWORK)               ")
    log("=" * 75)
    log(f"Date & Time   : {now.strftime('%Y-%m-%d %H:%M:%S')}")
    log(f"Database      : {DB_NAME}")
    log(f"Host:Port     : {DB_HOST}:{DB_PORT}")
    log(f"Username      : {DB_USER}")
    log(f"Local Path    : {local_target_dir}")
    log(f"Network Path  : {network_target_dir}")
    log("=" * 75)

    overall_success = True

    # -------------------------------------------------------------
    # STEP 1: Local Backup Directory Setup
    # -------------------------------------------------------------
    log("\n[STEP 1/4] Preparing Local Backup Directory...")
    try:
        if not os.path.exists(LOCAL_BACKUP_ROOT):
            log(f" -> Creating Local Root Folder: {LOCAL_BACKUP_ROOT}")
            os.makedirs(LOCAL_BACKUP_ROOT, exist_ok=True)
        else:
            log(f" -> Local Root Folder exists: {LOCAL_BACKUP_ROOT}")

        os.makedirs(local_target_dir, exist_ok=True)
        log(f" -> Local timestamp folder ready: {local_target_dir}")
    except Exception as e:
        log(f" -> [ERROR] Failed to create local directory: {e}")
        return False

    # -------------------------------------------------------------
    # STEP 2: Generate Dumps Locally (SQL Plain Text & Directory format)
    # -------------------------------------------------------------
    sql_file = os.path.join(local_target_dir, f"{DB_NAME}.sql")
    dump_dir = os.path.join(local_target_dir, f"{DB_NAME}_directory")

    pg_dump_bin = find_pg_dump()
    log(f"\n[STEP 2/4] Taking Local Database Backup via pg_dump ({pg_dump_bin})...")

    env = os.environ.copy()
    env["PGPASSWORD"] = DB_PASSWORD

    # 2.1 Plain SQL Dump
    log("  -> Generating SQL file backup (.sql)...")
    sql_cmd = [
        pg_dump_bin,
        "-h", DB_HOST,
        "-p", DB_PORT,
        "-U", DB_USER,
        "-d", DB_NAME,
        "-F", "p",
        "-f", sql_file
    ]
    try:
        res_sql = subprocess.run(sql_cmd, env=env, capture_output=True, text=True, check=True)
        sql_size_mb = os.path.getsize(sql_file) / (1024 * 1024)
        log(f"     [SUCCESS] SQL backup created: {sql_file} ({sql_size_mb:.2f} MB)")
        if res_sql.stderr:
            log(f"     [Notice]: {res_sql.stderr.strip()}")
    except subprocess.CalledProcessError as e:
        overall_success = False
        log(f"     [FAILED] SQL Backup failed: {e.stderr.strip() if e.stderr else str(e)}")
    except Exception as e:
        overall_success = False
        log(f"     [FAILED] SQL Backup exception: {str(e)}")

    # 2.2 Directory Format Dump (-F d)
    log("  -> Generating Directory format backup (-F d)...")
    dir_cmd = [
        pg_dump_bin,
        "-h", DB_HOST,
        "-p", DB_PORT,
        "-U", DB_USER,
        "-d", DB_NAME,
        "-F", "d",
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
        log(f"     [SUCCESS] Directory backup created: {dump_dir} ({dir_size_mb:.2f} MB)")
        if res_dir.stderr:
            log(f"     [Notice]: {res_dir.stderr.strip()}")
    except subprocess.CalledProcessError as e:
        overall_success = False
        log(f"     [FAILED] Directory Backup failed: {e.stderr.strip() if e.stderr else str(e)}")
    except Exception as e:
        overall_success = False
        log(f"     [FAILED] Directory Backup exception: {str(e)}")

    # -------------------------------------------------------------
    # STEP 3: Network Path Check & Copying
    # -------------------------------------------------------------
    log("\n[STEP 3/4] Checking Network Path & Copying Backup...")
    log(f" -> Network Target: {NETWORK_BACKUP_ROOT}")

    network_accessible = False
    network_copy_success = False

    try:
        # Check if network root directory exists and is accessible
        if os.path.exists(NETWORK_BACKUP_ROOT):
            log(" -> [STATUS] Network path FOUND! (Network share is accessible).")
            network_accessible = True
        else:
            log(" -> [STATUS] Network path NOT FOUND! (Path does not exist or server is unreachable).")
            log("    Troubleshooting steps:")
            log("    1. Verify server IP 10.120.29.100 is reachable (ping / network connection).")
            log("    2. Check that shared folder 'rdwis 2.0 backup' is shared on the target server.")
            log("    3. Check Windows network credentials / authentication permissions.")
    except Exception as e:
        log(f" -> [STATUS] Network path check encountered an error: {e}")

    if network_accessible:
        try:
            log(f" -> Creating network timestamp folder: {network_target_dir}")
            os.makedirs(network_target_dir, exist_ok=True)
            log(" -> Network folder created successfully.")

            # Copy SQL File to Network
            if os.path.exists(sql_file):
                dest_sql = os.path.join(network_target_dir, os.path.basename(sql_file))
                log(f" -> Copying SQL file to network: {dest_sql} ...")
                shutil.copy2(sql_file, dest_sql)
                dest_sql_size = os.path.getsize(dest_sql) / (1024 * 1024)
                log(f"    [SUCCESS] SQL file copied ({dest_sql_size:.2f} MB).")
            else:
                log("    [SKIP] Local SQL file not found to copy.")

            # Copy Directory Format Backup to Network
            if os.path.exists(dump_dir):
                dest_dir = os.path.join(network_target_dir, os.path.basename(dump_dir))
                log(f" -> Copying Directory backup to network: {dest_dir} ...")
                if os.path.exists(dest_dir):
                    shutil.rmtree(dest_dir)
                shutil.copytree(dump_dir, dest_dir)
                log("    [SUCCESS] Directory backup copied successfully.")
            else:
                log("    [SKIP] Local Directory backup not found to copy.")

            network_copy_success = True
            log(" -> [SUCCESS] Network backup copy completed successfully!")

        except PermissionError as pe:
            overall_success = False
            log(f" -> [ERROR] Permission Denied while writing to network share: {pe}")
            log("    Please ensure write permissions are granted on \\\\10.120.29.100\\rdwis 2.0 backup.")
        except Exception as e:
            overall_success = False
            log(f" -> [ERROR] Failed to copy to network folder: {e}")
            log(f"    Details: {traceback.format_exc()}")
    else:
        overall_success = False
        log(" -> [SKIPPED] Network copy skipped because network share could not be reached.")

    # -------------------------------------------------------------
    # STEP 4: Write Log Files (Both Local and Network)
    # -------------------------------------------------------------
    end_now = datetime.now()
    log("\n[STEP 4/4] Writing Final Logs...")
    log("=" * 75)
    log(f"LOCAL BACKUP STATUS   : {'SUCCESS' if os.path.exists(sql_file) else 'FAILED'}")
    log(f"NETWORK BACKUP STATUS : {'SUCCESS' if network_copy_success else ('FAILED / UNREACHABLE' if not network_accessible else 'FAILED COPY')}")
    log(f"Local Folder Path     : {local_target_dir}")
    log(f"Network Folder Path   : {network_target_dir if network_accessible else NETWORK_BACKUP_ROOT}")
    log(f"Finished At           : {end_now.strftime('%Y-%m-%d %H:%M:%S')}")
    log("=" * 75)

    full_log_text = "\n".join(log_buffer) + "\n"

    # Save to local folder
    try:
        with open(local_log_path, "w", encoding="utf-8") as f:
            f.write(full_log_text)
        print(f"Local Log saved: {local_log_path}")
    except Exception as e:
        print(f"Failed to write local log file: {e}")

    # Save to network folder if accessible
    if network_accessible and os.path.exists(network_target_dir):
        try:
            with open(network_log_path, "w", encoding="utf-8") as f:
                f.write(full_log_text)
            print(f"Network Log saved: {network_log_path}")
        except Exception as e:
            print(f"Failed to write network log file: {e}")

    return overall_success


if __name__ == "__main__":
    success = create_dual_backup()
    if not success:
        sys.exit(1)
