import os
import sys
import shutil
import subprocess
import time
import traceback
from datetime import datetime

# ==============================================================================
# Configuration / ترتیبات
# ==============================================================================
# [1] SOURCE STORAGE FOLDER PATH / اصل اسٹوریج فولڈر کا پورا راستہ
# آپ اس فائل کو کمپیوٹر میں کہیں بھی رکھیں (Desktop, D: Drive وغیرہ)، 
# یہ ہمیشہ نیچے دیئے گئے پاتھ والے اسٹوریج فولڈر کو ہی کاپی کرے گی:
SOURCE_STORAGE_DIR = r"H:\RDWIS 2.0\RDWIS APP 2.0\storage"

# [2] Subfolder to automatically create inside each destination
# اگر یہ فولڈر موجود نہیں ہوگا تو اسکرپٹ خود بنالے گا
STORAGE_SUBFOLDER_NAME = "storagerdwis2.0"

# List of Multiple Backup Destinations (Local Drives & Network Shares)
# آپ یہاں جتنے چاہیں نیٹ ورک شیئر یا لوکل ڈرائیو کے راستے شامل کرسکتے ہیں
# اگر کسی ایک جگہ خرابی یا نیٹ ورک کنکشن کا مسئلہ آئے گا تو باقی جگہوں کا بیک اپ نہیں رکے گا!
BACKUP_TARGETS = [
    r"D:\storagebackup2.0",
    r"\\10.120.29.99\rdwiws2.0",
    # r"\\10.120.29.100\rdwis 2.0 backup",
]

# Robocopy Multi-threading Speed (1 to 128)
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
# Resilient Copy Function (Robocopy with Python Fallback)
# ==============================================================================
def copy_storage_folder(src, dst, write_log):
    """
    Copies storage folder using Windows Robocopy for high performance across networks,
    with automatic fallback to Python's robust file-by-file copy.
    Handles locked files (e.g. active laravel.log) gracefully without crashing.
    """
    os.makedirs(dst, exist_ok=True)
    
    robocopy_exe = shutil.which("robocopy")
    if robocopy_exe and sys.platform == "win32":
        write_log(f"    -> Using Windows Robocopy (Multi-threaded /MT:{ROBOCOPY_THREADS}) for network/disk speed...")
        cmd = [
            robocopy_exe,
            src,
            dst,
            "/E",                       # Copy subdirectories, including empty ones
            f"/MT:{ROBOCOPY_THREADS}",  # Multi-threaded copying
            "/R:1",                     # Retry 1 time on locked files
            "/W:1",                     # Wait 1 second before retry
            "/NP",                      # No progress percentage in stdout (clean logs)
            "/NFL",                     # No file list in stdout (fast output)
            "/NDL",                     # No directory list in stdout
        ]
        try:
            res = subprocess.run(cmd, capture_output=True, text=True)
            # Robocopy exit codes: 0-7 mean success/files copied; >=8 means error
            if res.returncode < 8:
                write_log(f"    -> [SUCCESS] Robocopy completed successfully (Exit code: {res.returncode}).")
                return True
            else:
                write_log(f"    -> [WARNING] Robocopy returned code {res.returncode}. Falling back to Python copy...")
        except Exception as e:
            write_log(f"    -> [WARNING] Robocopy exception: {e}. Falling back to Python copy...")

    # Python Native Fallback with file-level error handling
    write_log("    -> Using Python native file copier with error resilience...")
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
                write_log(f"       [LOCKED/SKIPPED] {os.path.join(rel_path, file)}: {pe}")
            except Exception as e:
                fail_count += 1
                write_log(f"       [ERROR] {os.path.join(rel_path, file)}: {e}")

    write_log(f"    -> Copied {success_count} files successfully ({fail_count} locked/skipped).")
    return True


# ==============================================================================
# Multi-Destination Backup Runner / ملٹی ڈیسٹینیشن بیک اپ
# ==============================================================================
def create_multi_storage_backup():
    now = datetime.now()
    timestamp_folder = now.strftime("%Y-%m-%d_%H-%M-%S")
    overall_start_time = time.time()

    global_log_buffer = []

    def log(message):
        print(message)
        global_log_buffer.append(message)

    log("=" * 80)
    log("       RDWIS APP 2.0 - MULTI-DESTINATION & NETWORK STORAGE BACKUP       ")
    log("=" * 80)
    log(f"Date & Time        : {now.strftime('%Y-%m-%d %H:%M:%S')}")
    log(f"Source Storage Path: {SOURCE_STORAGE_DIR}")
    log(f"Subfolder Name     : {STORAGE_SUBFOLDER_NAME}")
    log(f"Configured Targets : {len(BACKUP_TARGETS)} destination(s)")
    for idx, target in enumerate(BACKUP_TARGETS, 1):
        log(f"  [{idx}] {target}")
    log("=" * 80)

    # 1. Validate Source
    if not os.path.exists(SOURCE_STORAGE_DIR):
        log(f"\n[FATAL ERROR] Source storage directory does not exist: {SOURCE_STORAGE_DIR}")
        return False

    # 2. Source Statistics
    log("\n[STEP 1/2] Analyzing Source Storage Directory...")
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
        log(f" -> [Notice] Could not scan source completely: {e}")

    # 3. Process each destination independently
    log("\n[STEP 2/2] Processing Backup Destinations...")
    results_summary = []

    for index, target_root in enumerate(BACKUP_TARGETS, 1):
        target_start = time.time()
        log("\n" + "-" * 80)
        log(f"DESTINATION [{index}/{len(BACKUP_TARGETS)}]: {target_root}")
        log("-" * 80)

        dest_result = {
            "target": target_root,
            "status": "UNKNOWN",
            "files": 0,
            "size": "0 B",
            "time": 0.0,
            "error": None
        }

        # Subfolder logic: If STORAGE_SUBFOLDER_NAME is provided, nest inside it
        if STORAGE_SUBFOLDER_NAME and not target_root.lower().endswith(STORAGE_SUBFOLDER_NAME.lower()):
            effective_target_root = os.path.join(target_root, STORAGE_SUBFOLDER_NAME)
        else:
            effective_target_root = target_root

        target_timestamp_dir = os.path.join(effective_target_root, timestamp_folder)
        target_storage_dir = os.path.join(target_timestamp_dir, "storage")
        target_log_file = os.path.join(target_timestamp_dir, "storage_backup_log.txt")

        # Independent try/except block to ensure failure in one doesn't affect others
        try:
            # Check target accessibility
            log(f" -> Checking accessibility of: {target_root} ...")
            if not os.path.exists(target_root):
                log(f"    [ATTEMPT] Root does not exist or network is offline. Trying to create: {effective_target_root}")
                try:
                    os.makedirs(effective_target_root, exist_ok=True)
                except Exception as ne:
                    log(f"    [FAILED] Destination unreachable or permission denied: {ne}")
                    dest_result["status"] = "UNREACHABLE / ACCESS DENIED"
                    dest_result["error"] = str(ne)
                    results_summary.append(dest_result)
                    continue

            # Ensure the storagerdwis2.0 folder exists
            if not os.path.exists(effective_target_root):
                log(f" -> Subfolder '{STORAGE_SUBFOLDER_NAME}' does not exist. Creating it: {effective_target_root} ...")
                os.makedirs(effective_target_root, exist_ok=True)
                log("    [SUCCESS] Subfolder created.")
            else:
                log(f" -> Subfolder '{STORAGE_SUBFOLDER_NAME}' exists: {effective_target_root}")

            # Create timestamp folder
            os.makedirs(target_timestamp_dir, exist_ok=True)
            log(f" -> Created timestamp directory: {target_timestamp_dir}")

            # Copy storage files
            log(f" -> Copying files to: {target_storage_dir} ...")
            copy_ok = copy_storage_folder(SOURCE_STORAGE_DIR, target_storage_dir, log)

            # Calculate destination size & files
            dest_files = 0
            dest_bytes = 0
            if os.path.exists(target_storage_dir):
                for root, _, files in os.walk(target_storage_dir):
                    for f in files:
                        dest_files += 1
                        try:
                            dest_bytes += os.path.getsize(os.path.join(root, f))
                        except OSError:
                            pass

            dest_time = time.time() - target_start
            dest_result["status"] = "SUCCESSFUL" if copy_ok else "COMPLETED WITH WARNINGS"
            dest_result["files"] = dest_files
            dest_result["size"] = format_size(dest_bytes)
            dest_result["time"] = dest_time

            log(f" -> [STATUS] {dest_result['status']} ({dest_files:,} files, {dest_result['size']} in {dest_time:.1f}s)")

            # Save log file inside destination
            try:
                dest_log_content = "\n".join(global_log_buffer) + f"\n\n[DESTINATION FINISHED: {dest_result['status']}]\n"
                with open(target_log_file, "w", encoding="utf-8") as f:
                    f.write(dest_log_content)
                log(f" -> Log file written to: {target_log_file}")
            except Exception as le:
                log(f" -> [Notice] Could not write log to destination: {le}")

        except PermissionError as pe:
            dest_time = time.time() - target_start
            log(f" -> [ERROR] Permission Denied on '{target_root}': {pe}")
            log("    Ensure current user has write permissions to this local folder or network share.")
            dest_result["status"] = "PERMISSION DENIED"
            dest_result["time"] = dest_time
            dest_result["error"] = str(pe)
        except Exception as e:
            dest_time = time.time() - target_start
            log(f" -> [ERROR] Destination failed with exception: {e}")
            log(f"    Details: {traceback.format_exc()}")
            dest_result["status"] = "FAILED"
            dest_result["time"] = dest_time
            dest_result["error"] = str(e)

        results_summary.append(dest_result)

    # 4. Final Consolidated Report
    total_elapsed = time.time() - overall_start_time
    end_now = datetime.now()

    log("\n" + "=" * 80)
    log("                       FINAL MULTI-BACKUP SUMMARY REPORT                       ")
    log("=" * 80)
    log(f"Started At    : {now.strftime('%Y-%m-%d %H:%M:%S')}")
    log(f"Finished At   : {end_now.strftime('%Y-%m-%d %H:%M:%S')}")
    log(f"Total Duration: {total_elapsed:.2f} seconds ({total_elapsed/60:.2f} minutes)")
    log("-" * 80)
    log(f"{'#':<3} {'DESTINATION':<40} {'STATUS':<20} {'COPIED':<10} {'TIME':<8}")
    log("-" * 80)

    any_success = False
    for idx, r in enumerate(results_summary, 1):
        is_ok = "SUCCESS" in r["status"]
        if is_ok:
            any_success = True
        log(f"{idx:<3} {r['target'][:38]:<40} {r['status']:<20} {r['size']:<10} {r['time']:<6.1f}s")
        if r.get("error"):
            log(f"    -> Error: {r['error']}")
    log("=" * 80)

    # Save consolidated local log file in workspace
    local_log_summary_path = os.path.join(os.path.dirname(__file__), "storage_backup_latest_log.txt")
    try:
        with open(local_log_summary_path, "w", encoding="utf-8") as f:
            f.write("\n".join(global_log_buffer) + "\n")
        print(f"\nConsolidated log saved to: {local_log_summary_path}")
    except Exception as e:
        print(f"Could not write summary log: {e}")

    return any_success


if __name__ == "__main__":
    success = create_multi_storage_backup()
    if not success:
        sys.exit(1)
