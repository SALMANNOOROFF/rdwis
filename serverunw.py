import os
import sys
import time
import socket
import subprocess
import signal
import shutil
import re

# ================= Configuration =================
PHP_PATH = r"C:\xampp\php\php.exe"
PHP_INI_PATH = r"C:\xampp\php\php.ini"
PORT = 5000
# =================================================

# Global process variables for cleanup
server_proc = None
queue_proc = None

def print_step(msg):
    print(f"\n[+] {msg}")

def print_warning(msg):
    print(f"[!] {msg}")

def print_error(msg):
    print(f"[-] {msg}")

def get_local_ip():
    """Auto-detect local network IP address"""
    s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
    try:
        # connect() for UDP doesn't send packets, just configures the socket to get local IP
        s.connect(("8.8.8.8", 80))
        ip = s.getsockname()[0]
    except Exception:
        ip = "127.0.0.1"
    finally:
        s.close()
    return ip

def kill_existing_php():
    """Kill any hanging php.exe processes to free up ports"""
    print_step("Checking and terminating existing php.exe processes...")
    try:
        subprocess.run(["taskkill", "/F", "/IM", "php.exe"], capture_output=True, text=True)
        time.sleep(1) # Give OS a moment to release ports
    except Exception as e:
        print_warning(f"Failed to run taskkill: {e}")

def update_php_ini():
    """Update required limits in php.ini"""
    if not os.path.exists(PHP_INI_PATH):
        print_warning(f"php.ini not found at {PHP_INI_PATH}. Skipping INI tweaks.")
        return

    print_step("Updating php.ini configuration...")
    shutil.copy(PHP_INI_PATH, PHP_INI_PATH + ".backup")
    
    with open(PHP_INI_PATH, "r", encoding="utf-8") as f:
        lines = f.readlines()
        
    settings = {
        "max_execution_time": "120",
        "memory_limit": "256M",
        "post_max_size": "50M",
        "upload_max_filesize": "50M",
        "session.gc_maxlifetime": "7200"
    }
    
    changed = False
    new_lines = []
    
    for line in lines:
        updated_line = line
        for key, val in settings.items():
            # Matches exact configuration line (not comments starting with ;)
            if re.match(rf"^\s*{key}\s*=.*", line):
                updated_line = f"{key} = {val}\n"
                changed = True
        new_lines.append(updated_line)
        
    if changed:
        with open(PHP_INI_PATH, "w", encoding="utf-8") as f:
            f.writelines(new_lines)
        print("    -> Limits increased (memory, uploads, execution time). Backup created.")
    else:
        print("    -> No changes were necessary in php.ini.")

def update_env(ip):
    """Update .env file for local network access and concurrency fixes"""
    if not os.path.exists(".env"):
        print_error(".env file not found!")
        sys.exit(1)
        
    print_step("Updating .env configuration...")
    shutil.copy(".env", ".env.backup")
    print("    -> Created .env.backup")
    
    updates = {
        "APP_URL": f"http://{ip}:{PORT}",
        "SESSION_DRIVER": "database",
        "SESSION_SECURE_COOKIE": "false",
        "SESSION_SAME_SITE": "lax",
        "CACHE_STORE": "array",
        "QUEUE_CONNECTION": "sync",
        "SANCTUM_STATEFUL_DOMAINS": f"{ip}:{PORT},localhost,127.0.0.1",
        "APP_DEBUG": "true",
        "PHP_CLI_SERVER_WORKERS": "8"
    }
    
    with open(".env", "r", encoding="utf-8") as f:
        content = f.read()
        
    for key, val in updates.items():
        if re.search(rf"^{key}=.*", content, flags=re.MULTILINE):
            content = re.sub(rf"^{key}=.*", f"{key}={val}", content, flags=re.MULTILINE)
        else:
            content += f"\n{key}={val}"
            
    with open(".env", "w", encoding="utf-8") as f:
        f.write(content)
    print(f"    -> Updated APP_URL to http://{ip}:{PORT} and adjusted Session/Cache/CORS drivers.")

def run_artisan_commands():
    """Run required artisan preparation commands"""
    print_step("Running Laravel prep commands...")
    commands = [
        ("config:clear", [PHP_PATH, "artisan", "config:clear"]),
        ("migrate", [PHP_PATH, "artisan", "migrate", "--force"]),
        ("config:cache", [PHP_PATH, "artisan", "config:cache"]),
        ("route:cache", [PHP_PATH, "artisan", "route:cache"]),
    ]
    
    for name, cmd in commands:
        try:
            print(f"    -> Executing '{name}'...")
            subprocess.run(cmd, check=True, capture_output=True, text=True)
        except subprocess.CalledProcessError as e:
            print_warning(f"Command '{name}' failed. Output:\n{e.stderr}")
            # Do not exit, continue (route:cache might fail if closures exist)

def cleanup(sig=None, frame=None):
    """Graceful shutdown"""
    print("\n")
    print_step("Shutting down servers...")
    
    if server_proc and server_proc.poll() is None:
        server_proc.terminate()
        print("    -> Web server terminated.")
        
    if queue_proc and queue_proc.poll() is None:
        queue_proc.terminate()
        print("    -> Queue worker terminated.")
        
    # Attempt force kill just in case subprocess terminate fails on Windows child processes
    subprocess.run(["taskkill", "/F", "/IM", "php.exe"], capture_output=True)
    
    try:
        ans = input("\n[?] Do you want to restore the original .env file from backup? (y/n): ")
        if ans.lower() == 'y' and os.path.exists(".env.backup"):
            shutil.copy(".env.backup", ".env")
            print("    -> .env restored successfully.")
    except Exception:
        pass
        
    print_step("Shutdown complete.")
    sys.exit(0)

def main():
    print("==================================================")
    print("      LARAVEL OFFLINE SERVER LAUNCH SCRIPT        ")
    print("==================================================")
    
    # 1. Pre-checks
    if not os.path.exists(PHP_PATH):
        print_error(f"PHP executable not found at {PHP_PATH}")
        sys.exit(1)
        
    if not os.path.exists("public"):
        print_error("Directory 'public' not found! Please run this script from the Laravel project root.")
        sys.exit(1)
        
    # Register Ctrl+C handler
    signal.signal(signal.SIGINT, cleanup)
    
    # 2. Network / IP
    ip = get_local_ip()
    print_step(f"Detected Local IP: {ip}")
    
    # 3. Kill existing
    kill_existing_php()
    
    # 4. Updates
    update_php_ini()
    update_env(ip)
    
    # 5. Laravel commands
    run_artisan_commands()
    
    # 6. Launch Servers
    global server_proc, queue_proc
    print_step(f"Starting PHP Built-in Server on {ip}:{PORT}...")
    server_cmd = [PHP_PATH, "-S", f"{ip}:{PORT}", "-t", "public"]
    server_proc = subprocess.Popen(server_cmd)
    
    print_step("Starting Laravel Queue Worker...")
    queue_cmd = [PHP_PATH, "artisan", "queue:work", "--tries=3"]
    queue_proc = subprocess.Popen(queue_cmd)
    
    print("\n==================================================")
    print(f" [SUCCESS] Server is running at: http://{ip}:{PORT}")
    print(" Press Ctrl+C to safely stop the server and queue.")
    print("==================================================\n")
    
    # 7. Monitoring Loop
    while True:
        if server_proc.poll() is not None:
            print_warning("PHP Web Server process crashed or stopped!")
        if queue_proc.poll() is not None:
            print_warning("Queue Worker process crashed or stopped!")
        time.sleep(5)

if __name__ == "__main__":
    main()