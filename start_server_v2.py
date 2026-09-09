import subprocess
import socket
import os
import sys
import time
import ctypes
import shutil

# ==========================================================
#  RDWIS 2.0 - Standalone Direct Server Launcher (V2)
# ==========================================================
#  * WITHOUT Caddy - Direct PHP HTTP Web Server
#  * Standard Port 80 (NO PORT number needed in browser!)
#  * Custom Domain: http://rdwisv2.mil
#  * Fully OFFLINE & PORTABLE (XAMPP PHP support)
#  * Auto-elevates to Administrator (for hosts file & Port 80)
#  * Auto Database Migrations (php artisan migrate --force)
#  * Auto Public Storage Link check (php artisan storage:link)
#  * Auto Cache Clearing & Fresh Rebuild (optimize:clear etc.)
#  * Auto Background Queue Worker (php artisan queue:work)
#  * Auto-opens browser to http://rdwisv2.mil
#
#  HOW TO USE:
#    Run: python start_server_v2.py (it will auto-elevate)
# ==========================================================

# =================== CONFIGURATION =======================
LOCAL_DOMAIN = "rdwisv2.mil"
HTTP_PORT = 80
PHP_EXE = r"C:\xampp\php\php.exe"
# ==========================================================

KEEP_ALIVE_FILES = []

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
PUBLIC_DIR = os.path.join(SCRIPT_DIR, "public")
HOSTS_FILE = r"C:\Windows\System32\drivers\etc\hosts"

BROWSER_PATHS = {
    "chrome": [
        r"C:\Program Files\Google\Chrome\Application\chrome.exe",
        r"C:\Program Files (x86)\Google\Chrome\Application\chrome.exe",
        os.path.expandvars(r"%LOCALAPPDATA%\Google\Chrome\Application\chrome.exe"),
    ],
    "edge": [
        r"C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe",
        r"C:\Program Files\Microsoft\Edge\Application\msedge.exe",
        os.path.expandvars(r"%LOCALAPPDATA%\Microsoft\Edge\Application\msedge.exe"),
    ],
}


# ----------------------------------------------------------
#  UTILITIES
# ----------------------------------------------------------

def is_admin():
    """Check if running as Administrator."""
    try:
        return ctypes.windll.shell32.IsUserAnAdmin()
    except Exception:
        return False


def run_as_admin():
    """Re-launch this script with Administrator privileges (UAC prompt)."""
    print("[>>] Requesting Administrator privileges...")
    print("     (Click 'Yes' on the UAC prompt)")
    script = os.path.abspath(__file__)
    ret = ctypes.windll.shell32.ShellExecuteW(
        None, "runas", sys.executable, f'"{script}"', SCRIPT_DIR, 1
    )
    if ret <= 32:
        print()
        print("[FAIL] Could not get Administrator privileges!")
        print("       Please run your terminal as Administrator and try again.")
        input("\nPress Enter to exit...")
        sys.exit(1)
    sys.exit(0)


def get_network_ip():
    """Detect local network IP - works FULLY OFFLINE."""
    try:
        s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        s.settimeout(0)
        s.connect(("10.254.254.254", 1))
        ip = s.getsockname()[0]
        s.close()
        if not ip.startswith("127."):
            return ip
    except Exception:
        pass

    try:
        hostname = socket.gethostname()
        ips = socket.getaddrinfo(hostname, None, socket.AF_INET)
        for addr in ips:
            ip = addr[4][0]
            if not ip.startswith("127."):
                return ip
    except Exception:
        pass

    try:
        result = subprocess.run(
            ["powershell", "-NoProfile", "-Command",
             "(Get-NetIPAddress -AddressFamily IPv4 | Where-Object "
             "{ $_.IPAddress -notlike '127.*' -and $_.IPAddress -notlike '169.*' } "
             "| Select-Object -First 1).IPAddress"],
            capture_output=True, text=True, timeout=10
        )
        ip = result.stdout.strip()
        if ip and not ip.startswith("127."):
            return ip
    except Exception:
        pass

    return "127.0.0.1"


def find_browser():
    """Find available browser (Chrome -> Edge)."""
    for path in BROWSER_PATHS["chrome"]:
        if os.path.isfile(path):
            return "Chrome", path
    for path in BROWSER_PATHS["edge"]:
        if os.path.isfile(path):
            return "Edge", path
    return None, None


def find_php_cli():
    """Find php.exe on host system dynamically."""
    if os.path.isfile(PHP_EXE):
        return PHP_EXE

    project_php = os.path.join(SCRIPT_DIR, "php", "php.exe")
    if os.path.isfile(project_php):
        return project_php

    path_php = shutil.which("php")
    if path_php and os.path.isfile(path_php):
        return path_php

    return "php"


def kill_existing_servers():
    """Kill any leftover server processes on port 80 or previous PHP/Apache/IIS instances."""
    print("[>>] Checking and clearing port 80 & previous processes...")

    # 1. Stop Windows IIS / World Wide Web Publishing Service if running
    try:
        os.system("net stop W3SVC /y >nul 2>&1")
    except Exception:
        pass

    # 2. Stop any Apache httpd instances
    try:
        os.system("taskkill /F /IM httpd.exe >nul 2>&1")
    except Exception:
        pass

    # 3. Check port 80 listeners and terminate PID
    try:
        res = subprocess.run(["netstat", "-ano"], capture_output=True, text=True, timeout=5)
        for line in res.stdout.splitlines():
            if ":80 " in line and "LISTENING" in line:
                parts = line.strip().split()
                pid = parts[-1]
                if pid and pid != "0":
                    print(f"     [>>] Freeing Port 80 (Terminating PID {pid})...")
                    os.system(f"taskkill /F /PID {pid} >nul 2>&1")
    except Exception:
        pass

    # 4. Kill previous php / php-cgi instances
    for name in ["php", "php-cgi"]:
        os.system(f'taskkill /F /IM {name}.exe >nul 2>&1')
    time.sleep(2)


# ----------------------------------------------------------
#  HOSTS FILE MANAGEMENT
# ----------------------------------------------------------

def is_domain_in_hosts(ip):
    """Check if our domain is already in the hosts file."""
    try:
        with open(HOSTS_FILE, "r") as f:
            content = f.read()
        for line in content.splitlines():
            line = line.strip()
            if line and not line.startswith("#"):
                parts = line.split()
                if len(parts) >= 2 and LOCAL_DOMAIN in parts[1:]:
                    return True
    except Exception:
        pass
    return False


def add_domain_to_hosts(ip):
    """Add local domain to Windows hosts file."""
    if is_domain_in_hosts(ip):
        print(f"[OK] Domain '{LOCAL_DOMAIN}' already configured in hosts file")
        return True

    print(f"[>>] Adding '{LOCAL_DOMAIN} -> 127.0.0.1' to Windows hosts file...")

    try:
        with open(HOSTS_FILE, "r") as f:
            content = f.read()

        new_lines = []
        for line in content.splitlines():
            stripped = line.strip()
            if stripped and not stripped.startswith("#"):
                parts = stripped.split()
                if len(parts) >= 2 and LOCAL_DOMAIN in parts[1:]:
                    continue
            new_lines.append(line)

        new_lines.append(f"\n# RDWIS 2.0 V2 - Auto-generated by start_server_v2.py")
        new_lines.append(f"127.0.0.1\t{LOCAL_DOMAIN}")
        if ip and ip != "127.0.0.1":
            new_lines.append(f"{ip}\t{LOCAL_DOMAIN}")
        new_lines.append("")

        with open(HOSTS_FILE, "w") as f:
            f.write("\n".join(new_lines))

        print(f"[OK] Domain '{LOCAL_DOMAIN}' successfully added to hosts file")
        return True

    except PermissionError:
        print(f"[FAIL] Cannot write to hosts file (need Administrator privileges!)")
        return False
    except Exception as e:
        print(f"[FAIL] Error modifying hosts file: {e}")
        return False


def flush_dns():
    """Flush DNS cache so hosts entry takes effect immediately."""
    os.system('ipconfig /flushdns >nul 2>&1')


# ----------------------------------------------------------
#  DATABASE MIGRATIONS & LARAVEL SETUP
# ----------------------------------------------------------

def update_env_app_url():
    """Automatically update APP_URL in .env to http://rdwisv2.mil."""
    env_path = os.path.join(SCRIPT_DIR, ".env")
    if not os.path.isfile(env_path):
        return

    try:
        with open(env_path, "r") as f:
            lines = f.readlines()

        new_url = f"http://{LOCAL_DOMAIN}"
        updated = False
        for i, line in enumerate(lines):
            if line.startswith("APP_URL="):
                lines[i] = f"APP_URL={new_url}\n"
                updated = True
                break

        if not updated:
            lines.append(f"\nAPP_URL={new_url}\n")

        with open(env_path, "w") as f:
            f.writelines(lines)

        print(f"     [OK] Updated APP_URL in .env to: {new_url}")
    except Exception as e:
        print(f"     [WARN] Could not update APP_URL in .env: {e}")


def run_database_migrations(php_exe):
    """Run pending database migrations (php artisan migrate --force)."""
    print("[>>] Running database migrations (php artisan migrate --force)...")
    try:
        res = subprocess.run(
            [php_exe, "artisan", "migrate", "--force"],
            cwd=SCRIPT_DIR,
            capture_output=True,
            text=True,
            timeout=60
        )
        output = res.stdout.strip()
        if output:
            for line in output.splitlines():
                if line.strip():
                    print(f"     {line.strip()}")
        if res.returncode == 0:
            print("     [OK] Database migrations up to date")
        else:
            err = res.stderr.strip()
            print(f"     [WARN] Migrations returned code {res.returncode}")
            if err:
                print(f"     {err[:200]}")
    except Exception as e:
        print(f"     [WARN] Could not run database migrations: {e}")


def ensure_storage_link(php_exe):
    """Ensure public/storage junction/symlink exists."""
    storage_link = os.path.join(PUBLIC_DIR, "storage")
    if not os.path.exists(storage_link):
        print("[>>] Creating public/storage link...")
        try:
            res = subprocess.run(
                [php_exe, "artisan", "storage:link"],
                cwd=SCRIPT_DIR,
                capture_output=True,
                text=True,
                timeout=15
            )
            msg = res.stdout.strip() or "storage:link created"
            print(f"     [OK] {msg}")
        except Exception as e:
            print(f"     [WARN] Could not create storage link: {e}")
    else:
        print("[OK] public/storage link verified")


def run_laravel_optimizations(php_exe):
    """Clear all old caches (routes, views, config, cache) and rebuild fresh production caches."""
    # First remove any stale cache files directly from disk to ensure artisan boots cleanly
    cache_dir = os.path.join(SCRIPT_DIR, "bootstrap", "cache")
    if os.path.isdir(cache_dir):
        for fname in ["routes-v7.php", "routes.php", "config.php", "services.php", "packages.php"]:
            fpath = os.path.join(cache_dir, fname)
            if os.path.isfile(fpath):
                try:
                    os.remove(fpath)
                except Exception:
                    pass

    print("[>>] Clearing all old caches (routes, views, config, application cache)...")
    clear_cmds = [
        [php_exe, "artisan", "optimize:clear"],
        [php_exe, "artisan", "view:clear"],
        [php_exe, "artisan", "route:clear"],
        [php_exe, "artisan", "config:clear"],
    ]
    for cmd in clear_cmds:
        try:
            res = subprocess.run(cmd, cwd=SCRIPT_DIR, capture_output=True, text=True, timeout=15)
            if res.returncode == 0:
                print(f"     [OK] {' '.join(cmd[1:])}")
        except Exception as e:
            print(f"     [WARN] Could not run {' '.join(cmd[1:])}: {e}")

    print("[>>] Rebuilding fresh production caches...")
    cache_cmds = [
        [php_exe, "artisan", "config:cache"],
        [php_exe, "artisan", "route:cache"],
        [php_exe, "artisan", "view:cache"],
        [php_exe, "artisan", "event:cache"],
    ]
    for cmd in cache_cmds:
        try:
            res = subprocess.run(cmd, cwd=SCRIPT_DIR, capture_output=True, text=True, timeout=15)
            if res.returncode == 0:
                print(f"     [OK] {' '.join(cmd[1:])}")
            else:
                print(f"     [WARN] {' '.join(cmd[1:])} (Code {res.returncode})")
        except Exception as e:
            print(f"     [WARN] Could not run {' '.join(cmd[1:])}: {e}")


# ----------------------------------------------------------
#  SERVER MANAGEMENT (DIRECT HTTP ON PORT 80 WITHOUT CADDY)
# ----------------------------------------------------------

def start_http_server(php_exe):
    """Start PHP HTTP server directly on 0.0.0.0:80 without Caddy and without nested artisan serve wrapper."""
    print(f"[>>] Starting HTTP Server on 0.0.0.0:{HTTP_PORT} (http://{LOCAL_DOMAIN})...")

    log_file = os.path.join(SCRIPT_DIR, "php_http_server.log")
    log_fh = open(log_file, "w")
    KEEP_ALIVE_FILES.append(log_fh)

    server_script = os.path.join(SCRIPT_DIR, "server.php")
    if not os.path.isfile(server_script):
        server_script = os.path.join(SCRIPT_DIR, "vendor", "laravel", "framework", "src", "Illuminate", "Foundation", "resources", "server.php")

    cmd = [php_exe, "-S", f"0.0.0.0:{HTTP_PORT}", server_script]

    proc = subprocess.Popen(
        cmd,
        cwd=SCRIPT_DIR,
        stdin=subprocess.DEVNULL,
        stdout=log_fh,
        stderr=log_fh,
        creationflags=subprocess.CREATE_NEW_PROCESS_GROUP,
    )
    time.sleep(2)

    exit_code = proc.poll()
    if exit_code is not None:
        print(f"[FAIL] HTTP Server failed to start on Port {HTTP_PORT}! (Exit code: {exit_code})")
        try:
            with open(log_file, "r") as f:
                err = f.read().strip()
            if err:
                print(f"       {err[:400]}")
        except Exception:
            pass
        return None

    print(f"[OK] HTTP Server running -> http://{LOCAL_DOMAIN} (Port {HTTP_PORT} - NO PORT NEEDED!)")
    print(f"     Log: {log_file}")
    return proc


def start_queue_worker(php_exe):
    """Start background queue worker."""
    log_file = os.path.join(SCRIPT_DIR, "queue_worker_v2.log")
    log_fh = open(log_file, "w")
    KEEP_ALIVE_FILES.append(log_fh)

    proc = subprocess.Popen(
        [php_exe, "artisan", "queue:work", "--tries=3"],
        cwd=SCRIPT_DIR,
        stdin=subprocess.DEVNULL,
        stdout=log_fh,
        stderr=log_fh,
        creationflags=subprocess.CREATE_NEW_PROCESS_GROUP,
    )
    print("[OK] Background Queue Worker started")
    return proc


def open_browser(url):
    """Open the best available browser automatically."""
    name, path = find_browser()
    if path:
        print(f"[>>] Opening {name}: {url}")
        subprocess.Popen(
            [path, "--new-window", url],
            stdout=subprocess.DEVNULL,
            stderr=subprocess.DEVNULL,
        )
        print(f"[OK] {name} opened!")
    else:
        print("[>>] Opening default browser...")
        try:
            os.startfile(url)
            print("[OK] Browser opened!")
        except Exception:
            print(f"[WARN] Could not open browser automatically. Go to: {url}")


def cleanup(procs):
    """Stop servers and clean up."""
    print()
    print("[>>] Shutting down servers...")

    for p in procs:
        if p:
            p.terminate()
            try:
                p.wait(timeout=5)
            except Exception:
                p.kill()

    for fh in KEEP_ALIVE_FILES:
        try:
            fh.close()
        except Exception:
            pass

    print("[OK] All servers stopped cleanly!")


# ----------------------------------------------------------
#  MAIN
# ----------------------------------------------------------

def main():
    print()
    print("=" * 60)
    print("   RDWIS 2.0 (V2) - Standalone HTTP Server (Port 80)")
    print(f"   Domain: http://{LOCAL_DOMAIN} (NO PORT REQUIRED!)")
    print("=" * 60)
    print()

    admin = is_admin()
    if not admin:
        run_as_admin()
        return

    print("[OK] Running as Administrator")
    print()

    # ---- Step 0: Clean slate & clear port 80 ----
    kill_existing_servers()
    print("[OK] Clean slate ready")
    print()

    # ---- Step 1: Verify PHP CLI ----
    php_exe = find_php_cli()
    try:
        res = subprocess.run([php_exe, "-v"], capture_output=True, text=True, timeout=5)
        if res.returncode != 0:
            print(f"[FAIL] PHP CLI error at: {php_exe}")
            input("\nPress Enter to exit...")
            sys.exit(1)
    except Exception as e:
        print(f"[FAIL] PHP not found at '{php_exe}': {e}")
        input("\nPress Enter to exit...")
        sys.exit(1)

    print(f"[OK] PHP CLI verified ({php_exe})")
    print(f"[OK] Public directory verified ({PUBLIC_DIR})")
    print()

    # ---- Step 2: Detect Network IP ----
    ip = get_network_ip()
    print(f"[OK] Network IP: {ip}")
    print()

    # ---- Step 3: Configure Windows Hosts File ----
    hosts_ok = add_domain_to_hosts(ip)
    if hosts_ok:
        flush_dns()
        print("[OK] DNS cache flushed")
    print()

    # ---- Step 4: Migrations, Storage Link & Optimizations ----
    update_env_app_url()
    run_database_migrations(php_exe)
    ensure_storage_link(php_exe)
    run_laravel_optimizations(php_exe)
    print()

    # ---- Step 5: Start Direct HTTP Server on Port 80 ----
    http_proc = start_http_server(php_exe)
    if not http_proc:
        input("\nPress Enter to exit...")
        sys.exit(1)
    print()

    # ---- Step 6: Start Queue Worker ----
    queue_proc = start_queue_worker(php_exe)
    print()

    # ---- Step 7: Open Browser ----
    target_url = f"http://{LOCAL_DOMAIN}"
    time.sleep(1)
    open_browser(target_url)
    print()

    # ---- Summary ----
    print("=" * 60)
    print("  [SUCCESS] RDWIS 2.0 V2 IS RUNNING ON PORT 80!")
    print(f"  Primary Address:  http://{LOCAL_DOMAIN}   <-- Simply type this!")
    print(f"  Localhost Direct: http://localhost")
    if ip and ip != "127.0.0.1":
        print(f"  Network Address:  http://{ip}")
    print("=" * 60)
    print()
    print("Press Ctrl+C to stop server...")
    print()

    # ---- Keep Running ----
    procs = [http_proc, queue_proc]
    try:
        while True:
            if http_proc.poll() is not None:
                print("[!] HTTP Server stopped unexpectedly")
                break
            time.sleep(2)
    except KeyboardInterrupt:
        pass

    cleanup(procs)
    print()
    input("Press Enter to close...")


if __name__ == "__main__":
    main()
