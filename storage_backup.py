import os
import sys
import shutil
import subprocess
import time
from datetime import datetime

# ==============================================================================
# Configuration / ترتیبات
# ==============================================================================
# [1] SOURCE STORAGE FOLDER PATH / اصل اسٹوریج فولڈر کا پورا راستہ
# آپ اس فائل کو کمپیوٹر میں کہیں بھی رکھیں (Desktop, D: Drive وغیرہ)، 
# یہ ہمیشہ نیچے دیئے گئے پاتھ والے اسٹوریج فولڈر کو ہی کاپی کرے گی:
SOURCE_STORAGE_DIR = r"H:\RDWIS 2.0\RDWIS APP 2.0\storage"

# [2] LOCAL PC BACKUP ROOT DIRECTORY / لوکل پی سی کا بیک اپ مین فولڈر
LOCAL_BACKUP_ROOT = r"D:\storagebackup2.0"

# Robocopy Thread Count for Fast Windows Copy (Multi-threading)
ROBOCOPY_THREADS = 8


# ==============================================================================
# Helper: Format Bytes to Human Readable / سائز معلوم کرنے کا فنکشن
# ==============================================================================
def format_size(bytes_val):
    for unit in ['B', 'KB', 'MB', 'GB', 'TB']:
        if bytes_val < 1024.0:
            return f"{bytes_val:.2f} {unit}"
        bytes_val /= 1024.0
    return f"{bytes_val:.2f} PB"


# ==============================================================================
# Fast & Resilient Copy Function (Robocopy with Python Fallback)
# ==============================================================================
def copy_storage_folder(src, dst, write_log):
    """
    Copies storage folder using Windows Robocopy for high performance (multi-threaded),
    with automatic fallback to Python's robust file-by-file copy.
    Handles locked files (like active laravel.log) gracefully without crashing.
    """
    os.makedirs(dst, exist_ok=True)
    
    # Try Robocopy first (Built-in Windows tool, ultra fast for large gigabyte transfers)
    robocopy_exe = shutil.which("robocopy")
    if robocopy_exe and sys.platform == "win32":
        write_log(f" -> Using Windows Robocopy (Multi-threaded /MT:{ROBOCOPY_THREADS}) for ultra-fast copying...")
        cmd = [
            robocopy_exe,
            src,
            dst,
            "/E",              # Copy subdirectories, including empty ones
            f"/MT:{ROBOCOPY_THREADS}",  # Multi-threaded copying
            "/R:1",            # Retry 1 time on locked files
            "/W:1",            # Wait 1 second before retry
            "/NP",             # No progress percentage in stdout (clean logs)
            "/NFL",            # No file list in stdout (fast output)
            "/NDL",            # No directory list in stdout
        ]
        try:
            res = subprocess.run(cmd, capture_output=True, text=True)
            # Robocopy exit codes: 0-7 mean success/files copied; >=8 means serious error
            if res.returncode < 8:
                write_log(f" -> [SUCCESS] Robocopy completed successfully (Exit code: {res.returncode}).")
                return True
            else:
                write_log(f" -> [WARNING] Robocopy returned code {res.returncode}. Falling back to Python copy...")
        except Exception as e:
            write_log(f" -> [WARNING] Robocopy exception: {e}. Falling back to Python copy...")

    # Python Native Fallback with file-level error handling
    write_log(" -> Using Python native file copier with error resilience...")
    success_count = 0
    fail_count = 0
    
    for root, dirs, files in os.walk(src):
        rel_path = os.path.relpath(root, src)
        dest_dir = os.path.join(dst, rel_path) if rel_path != "." else dst
        os.makedirs(dest_dir, exist_ok=True)
        
        for file in files:
            src_file = os.path.join(root, file)
            dst_file = os.path.join(dest_dir, file)
            try:
                shutil.copy2(src_file, dst_file)
                success_count += 1
            except (PermissionError, OSError) as pe:
                fail_count += 1
                write_log(f"    [LOCKED/SKIPPED] {os.path.join(rel_path, file)}: {pe}")
            except Exception as e:
                fail_count += 1
                write_log(f"    [ERROR] {os.path.join(rel_path, file)}: {e}")

    write_log(f" -> Copied {success_count} files successfully ({fail_count} locked/skipped).")
    return True


# ==============================================================================
# Main Backup Execution / مین بیک اپ فنکشن
# ==============================================================================
def create_local_storage_backup():
    now = datetime.now()
    timestamp_folder = now.strftime("%Y-%m-%d_%H-%M-%S")
    start_time = time.time()

    # Destination directories
    current_backup_dir = os.path.join(LOCAL_BACKUP_ROOT, timestamp_folder)
    dest_storage_dir = os.path.join(current_backup_dir, "storage")
    log_file_path = os.path.join(current_backup_dir, "storage_backup_log.txt")

    log_buffer = []

    def log(message):
        print(message)
        log_buffer.append(message)

    log("=" * 75)
    log("              RDWIS APP 2.0 - LOCAL STORAGE FOLDER BACKUP               ")
    log("=" * 75)
    log(f"Date & Time : {now.strftime('%Y-%m-%d %H:%M:%S')}")
    log(f"Source Path : {SOURCE_STORAGE_DIR}")
    log(f"Target Root : {LOCAL_BACKUP_ROOT}")
    log(f"Target Path : {current_backup_dir}")
    log("=" * 75)

    # 1. Validate Source Folder
    if not os.path.exists(SOURCE_STORAGE_DIR):
        log(f"\n[FATAL ERROR] Source storage folder does not exist: {SOURCE_STORAGE_DIR}")
        return False

    # 2. Setup Destination Root Folder
    log("\n[STEP 1/3] Preparing Destination Directory...")
    try:
        os.makedirs(current_backup_dir, exist_ok=True)
        log(f" -> Backup folder created: {current_backup_dir}")
    except Exception as e:
        log(f" -> [FATAL ERROR] Could not create destination folder: {e}")
        return False

    # 3. Calculate Source Stats
    log("\n[STEP 2/3] Analyzing Source Storage Directory...")
    src_total_files = 0
    src_total_bytes = 0
    try:
        for root, _, files in os.walk(SOURCE_STORAGE_DIR):
            for f in files:
                src_total_files += 1
                try:
                    src_total_bytes += os.path.getsize(os.path.join(root, f))
                except OSError:
                    pass
        log(f" -> Total Files in Source: {src_total_files:,}")
        log(f" -> Total Size in Source : {format_size(src_total_bytes)}")
    except Exception as e:
        log(f" -> [Notice] Could not scan source directory completely: {e}")

    # 4. Perform Backup
    log(f"\n[STEP 3/3] Copying storage contents to: {dest_storage_dir} ...")
    copy_success = copy_storage_folder(SOURCE_STORAGE_DIR, dest_storage_dir, log)

    # 5. Verify Backup
    dest_total_files = 0
    dest_total_bytes = 0
    if os.path.exists(dest_storage_dir):
        for root, _, files in os.walk(dest_storage_dir):
            for f in files:
                dest_total_files += 1
                try:
                    dest_total_bytes += os.path.getsize(os.path.join(root, f))
                except OSError:
                    pass

    elapsed_time = time.time() - start_time
    end_now = datetime.now()

    # 6. Final Summary
    log("\n" + "=" * 75)
    log(f"OVERALL STATUS  : {'SUCCESSFUL' if copy_success else 'COMPLETED WITH WARNINGS'}")
    log(f"Files Copied    : {dest_total_files:,} / {src_total_files:,}")
    log(f"Total Size      : {format_size(dest_total_bytes)}")
    log(f"Time Taken      : {elapsed_time:.2f} seconds ({elapsed_time/60:.2f} minutes)")
    log(f"Finished At     : {end_now.strftime('%Y-%m-%d %H:%M:%S')}")
    log(f"Storage Folder  : {dest_storage_dir}")
    log(f"Log File Saved  : {log_file_path}")
    log("=" * 75)

    # Write log file to backup folder
    try:
        with open(log_file_path, "w", encoding="utf-8") as f:
            f.write("\n".join(log_buffer) + "\n")
    except Exception as e:
        print(f"Failed to write log file: {e}")

    return copy_success


if __name__ == "__main__":
    success = create_local_storage_backup()
    if not success:
        sys.exit(1)
