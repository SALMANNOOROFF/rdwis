import subprocess
import socket
import os
import sys
import time
import ctypes
import shutil

# ==========================================================
#  RDWIS 2.0 - Portable Production HTTPS Server Launcher
# ==========================================================
#  * Fully OFFLINE - no internet needed
#  * Fully PORTABLE - copy project to any PC, run this script
#  * Production Grade - PHP FastCGI (20 parallel workers) + Caddy
#  * Custom local domain name (https://rdwis)
#  * Auto generates + installs SSL certificate
#  * Auto configures Windows hosts file
#  * Auto compiles Laravel caches
#  * Auto opens Chrome
#
#  HOW TO USE:
#    Run: python start_server.py (it will auto-elevate)
# ==========================================================

# =================== CONFIGURATION =======================
LOCAL_DOMAIN = "rdwis"
FASTCGI_PORT = 9000
# ==========================================================

KEEP_ALIVE_FILES = []


SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
CADDY_DIR = os.path.join(SCRIPT_DIR, "caddy")
CADDY_EXE = os.path.join(CADDY_DIR, "caddy.exe")
PUBLIC_DIR = os.path.join(SCRIPT_DIR, "public")
CADDYFILE = os.path.join(SCRIPT_DIR, "Caddyfile")
HOSTS_FILE = r"C:\Windows\System32\drivers\etc\hosts"

CADDY_ROOT_CERT = os.path.join(CADDY_DIR, "pki", "authorities", "local", "root.crt")

CHROME_PATHS = [
    r"C:\Program Files\Google\Chrome\Application\chrome.exe",
    r"C:\Program Files (x86)\Google\Chrome\Application\chrome.exe",
    os.path.expandvars(r"%LOCALAPPDATA%\Google\Chrome\Application\chrome.exe"),
]


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


def find_chrome():
    """Find Chrome on this PC."""
    for path in CHROME_PATHS:
        if os.path.isfile(path):
            return path
    return None


def find_php_cgi():
    """Find php-cgi.exe on host system dynamically."""
    project_php_cgi = os.path.join(SCRIPT_DIR, "php", "php-cgi.exe")
    if os.path.isfile(project_php_cgi):
        return project_php_cgi

    xampp_php_cgi = r"C:\xampp\php\php-cgi.exe"
    if os.path.isfile(xampp_php_cgi):
        return xampp_php_cgi

    path_cgi = shutil.which("php-cgi")
    if path_cgi and os.path.isfile(path_cgi):
        return path_cgi

    return None


def kill_existing_servers():
    """Kill any leftover caddy/php processes from previous runs."""
    for name in ["caddy", "php-cgi", "php"]:
        os.system(f'taskkill /F /IM {name}.exe >nul 2>&1')
    time.sleep(3)


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
        print(f"[OK] Domain '{LOCAL_DOMAIN}' already in hosts file")
        return True

    print(f"[>>] Adding '{LOCAL_DOMAIN} -> {ip}' to Windows hosts file...")

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

        new_lines.append(f"\n# RDWIS 2.0 - Auto-generated by start_server.py")
        new_lines.append(f"{ip}\t{LOCAL_DOMAIN}")
        new_lines.append("")

        with open(HOSTS_FILE, "w") as f:
            f.write("\n".join(new_lines))

        print(f"[OK] Domain '{LOCAL_DOMAIN}' added to hosts file")
        return True

    except PermissionError:
        print(f"[FAIL] Cannot write to hosts file (need Administrator!)")
        return False
    except Exception as e:
        print(f"[FAIL] Error modifying hosts file: {e}")
        return False


def flush_dns():
    """Flush DNS cache so hosts entry takes effect immediately."""
    os.system('ipconfig /flushdns >nul 2>&1')


# ----------------------------------------------------------
#  CERTIFICATE MANAGEMENT
# ----------------------------------------------------------

def get_caddy_env():
    """Environment for Caddy."""
    env = os.environ.copy()
    env["XDG_DATA_HOME"] = SCRIPT_DIR
    return env


def is_cert_in_store():
    """Check if Caddy root CA is trusted."""
    try:
        result = subprocess.run(
            ["certutil", "-verifystore", "Root"],
            capture_output=True, text=True, timeout=15
        )
        if "Caddy" in result.stdout:
            return True
    except Exception:
        pass

    try:
        result = subprocess.run(
            ["certutil", "-user", "-verifystore", "Root"],
            capture_output=True, text=True, timeout=15
        )
        if "Caddy" in result.stdout:
            return True
    except Exception:
        pass

    return False


def generate_caddy_ca():
    """Generate internal CA."""
    if os.path.isfile(CADDY_ROOT_CERT):
        return True

    print("[>>] Generating SSL certificate (first time only)...")
    temp_cf = os.path.join(SCRIPT_DIR, "Caddyfile_init")
    with open(temp_cf, "w") as f:
        f.write('{\n}\n\nhttps://localhost {\n    tls internal\n    respond "ok"\n}\n')

    env = get_caddy_env()
    proc = subprocess.Popen(
        [CADDY_EXE, "run", "--config", temp_cf],
        cwd=SCRIPT_DIR,
        env=env,
        stdout=subprocess.DEVNULL,
        stderr=subprocess.DEVNULL,
    )

    for _ in range(20):
        time.sleep(0.5)
        if os.path.isfile(CADDY_ROOT_CERT):
            break

    proc.terminate()
    try:
        proc.wait(timeout=5)
    except Exception:
        proc.kill()

    if os.path.exists(temp_cf):
        os.remove(temp_cf)

    if os.path.isfile(CADDY_ROOT_CERT):
        print("[OK] SSL certificate generated!")
        return True
    else:
        print("[FAIL] Could not generate certificate")
        return False


def install_certificate():
    """Install Caddy root CA."""
    if is_cert_in_store():
        print("[OK] Certificate already trusted by Windows")
        return True

    if not os.path.isfile(CADDY_ROOT_CERT):
        if not generate_caddy_ca():
            return False

    print("[>>] Installing certificate to Windows trust store...")
    if is_admin():
        result = subprocess.run(
            ["certutil", "-addstore", "Root", CADDY_ROOT_CERT],
            capture_output=True, text=True, timeout=30
        )
        if result.returncode == 0:
            print("[OK] Certificate installed silently")
            return True

    result = subprocess.run(
        ["certutil", "-user", "-addstore", "Root", CADDY_ROOT_CERT],
        timeout=120
    )
    if result.returncode == 0:
        print("[OK] Certificate installed!")
        return True

    print("[WARN] Certificate may not be installed.")
    return False


# ----------------------------------------------------------
#  LARAVEL OPTIMIZATIONS
# ----------------------------------------------------------

def run_laravel_optimizations():
    """Run Laravel production optimization commands."""
    print("[>>] Running Laravel production optimizations...")
    cmds = [
        ["php", "artisan", "config:cache"],
        ["php", "artisan", "route:cache"],
        ["php", "artisan", "view:cache"],
        ["php", "artisan", "event:cache"],
    ]
    for cmd in cmds:
        try:
            res = subprocess.run(cmd, cwd=SCRIPT_DIR, capture_output=True, text=True, timeout=15)
            if res.returncode == 0:
                print(f"     [OK] {' '.join(cmd[1:])}")
            else:
                print(f"     [WARN] {' '.join(cmd[1:])} (Code {res.returncode})")
        except Exception as e:
            print(f"     [WARN] Could not run {' '.join(cmd[1:])}: {e}")


# ----------------------------------------------------------
#  SERVER MANAGEMENT (PRODUCTION FASTCGI + CADDY)
# ----------------------------------------------------------

def create_caddyfile(ip):
    """Create Caddyfile for HTTPS FastCGI proxy with custom domain."""
    public_dir_caddy = PUBLIC_DIR.replace("\\", "/")
    content = f"""\
{{
    auto_https disable_redirects
}}

http://{LOCAL_DOMAIN}, http://{ip} {{
    header Strict-Transport-Security "max-age=31536000; includeSubDomains"
    redir https://{{host}}{{uri}} permanent
}}

https://{LOCAL_DOMAIN}, https://{ip} {{
    tls internal
    root * "{public_dir_caddy}"

    header {{
        Strict-Transport-Security "max-age=31536000; includeSubDomains"
        X-Content-Type-Options "nosniff"
        X-XSS-Protection "1; mode=block"
        X-Frame-Options "SAMEORIGIN"
        Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https:; style-src 'self' 'unsafe-inline' https:; img-src 'self' data: https:; font-src 'self' data: https:; connect-src 'self' https:;"
        Referrer-Policy "strict-origin-when-cross-origin"
        Permissions-Policy "geolocation=(), microphone=(), camera=()"
        -Server
        -X-Powered-By
    }}

    @no_cache {{
        path /manifest.json /service-worker.js
    }}
    header @no_cache Cache-Control "no-store, must-revalidate"

    php_fastcgi 127.0.0.1:{FASTCGI_PORT}
    file_server
}}
"""
    with open(CADDYFILE, "w") as f:
        f.write(content)
    print(f"[OK] Caddyfile created (domain: {LOCAL_DOMAIN}, FastCGI: 127.0.0.1:{FASTCGI_PORT})")


def start_fastcgi_server(php_cgi_exe):
    """Start FastCGI PHP process pool (20 parallel workers)."""
    print(f"[>>] Starting FastCGI Process Pool (20 workers) on 127.0.0.1:{FASTCGI_PORT}...")

    log_file = os.path.join(SCRIPT_DIR, "php_fastcgi_server.log")
    log_fh = open(log_file, "w")
    KEEP_ALIVE_FILES.append(log_fh)

    env = os.environ.copy()
    env["PHP_FCGI_CHILDREN"] = "20"
    env["PHP_FCGI_MAX_REQUESTS"] = "1000"

    proc = subprocess.Popen(
        [php_cgi_exe, "-b", f"127.0.0.1:{FASTCGI_PORT}"],
        cwd=SCRIPT_DIR,
        env=env,
        stdin=subprocess.DEVNULL,
        stdout=log_fh,
        stderr=log_fh,
        creationflags=subprocess.CREATE_NEW_PROCESS_GROUP,
    )
    time.sleep(2)

    exit_code = proc.poll()
    if exit_code is not None:
        print(f"[FAIL] PHP FastCGI server failed to start! (Exit code: {exit_code})")
        try:
            with open(log_file, "r") as f:
                err = f.read().strip()
            if err:
                print(f"       {err[:200]}")
        except Exception:
            pass
        return None

    print(f"[OK] FastCGI Process Pool running -> 127.0.0.1:{FASTCGI_PORT} (20 workers)")
    print(f"     Log: {log_file}")
    return proc


def start_queue_worker():
    """Start background queue worker."""
    log_file = os.path.join(SCRIPT_DIR, "queue_worker.log")
    log_fh = open(log_file, "w")
    KEEP_ALIVE_FILES.append(log_fh)

    proc = subprocess.Popen(
        ["php", "artisan", "queue:work", "--tries=3"],
        cwd=SCRIPT_DIR,
        stdin=subprocess.DEVNULL,
        stdout=log_fh,
        stderr=log_fh,
        creationflags=subprocess.CREATE_NEW_PROCESS_GROUP,
    )
    print("[OK] Background Queue Worker started")
    return proc


def start_caddy(ip):
    """Start Caddy HTTPS reverse proxy."""
    print("[>>] Starting Caddy HTTPS server...")

    log_file = os.path.join(SCRIPT_DIR, "caddy_server.log")
    log_fh = open(log_file, "w")
    KEEP_ALIVE_FILES.append(log_fh)

    env = get_caddy_env()
    proc = subprocess.Popen(
        [CADDY_EXE, "run", "--config", CADDYFILE],
        cwd=SCRIPT_DIR,
        env=env,
        stdin=subprocess.DEVNULL,
        stdout=log_fh,
        stderr=log_fh,
        creationflags=subprocess.CREATE_NEW_PROCESS_GROUP,
    )
    time.sleep(3)

    exit_code = proc.poll()
    if exit_code is not None:
        print(f"[FAIL] Caddy failed to start! (Exit code: {exit_code})")
        try:
            with open(log_file, "r") as f:
                output = f.read().strip()
            if output:
                for line in output.split("\n")[-5:]:
                    print(f"       {line.strip()}")
        except Exception:
            pass
        return None

    print(f"[OK] Caddy running -> https://{LOCAL_DOMAIN}")
    print(f"     Log: {log_file}")
    return proc


def open_chrome(url):
    """Open Chrome automatically."""
    chrome = find_chrome()
    if chrome:
        print(f"[>>] Opening Chrome: {url}")
        subprocess.Popen(
            [chrome, "--new-window", url],
            stdout=subprocess.DEVNULL,
            stderr=subprocess.DEVNULL,
        )
        print("[OK] Chrome opened!")
    else:
        print("[>>] Chrome not found, opening default browser...")
        try:
            os.startfile(url)
            print("[OK] Browser opened!")
        except Exception:
            print(f"[WARN] Could not open browser. Go to: {url}")


def cleanup(procs):
    """Stop servers and clean up."""
    print()
    print("[>>] Shutting down...")

    for p in procs:
        if p:
            p.terminate()
            try:
                p.wait(timeout=5)
            except Exception:
                p.kill()

    if os.path.exists(CADDYFILE):
        os.remove(CADDYFILE)

    for fh in KEEP_ALIVE_FILES:
        try:
            fh.close()
        except Exception:
            pass

    print("[OK] All cleaned up and stopped!")


# ----------------------------------------------------------
#  MAIN
# ----------------------------------------------------------

def main():
    print()
    print("=" * 55)
    print("   RDWIS 2.0 - Production Server Launcher")
    print(f"   Domain: https://{LOCAL_DOMAIN}")
    print("=" * 55)
    print()

    admin = is_admin()
    if not admin:
        run_as_admin()
        return

    print("[OK] Running as Administrator")
    print()

    # ---- Step 0: Kill old servers ----
    print("[>>] Cleaning up previous server processes...")
    kill_existing_servers()
    print("[OK] Clean slate")
    print()

    # ---- Step 1: Verify required binaries ----
    errors = []
    if not os.path.isfile(CADDY_EXE):
        errors.append(f"caddy.exe not found in: {CADDY_DIR}")
    if not os.path.isdir(PUBLIC_DIR):
        errors.append("public/ directory not found")

    php_cgi_exe = find_php_cgi()
    if not php_cgi_exe:
        errors.append("php-cgi.exe not found! Put php-cgi in PATH or C:\\xampp\\php\\php-cgi.exe")

    try:
        subprocess.run(["php", "-v"], capture_output=True, timeout=5)
    except Exception:
        errors.append("PHP not found! Install PHP and add to PATH.")

    if errors:
        for err in errors:
            print(f"[FAIL] {err}")
        input("\nPress Enter to exit...")
        sys.exit(1)

    print("[OK] caddy.exe found")
    print("[OK] public/ directory found")
    print(f"[OK] php-cgi.exe found ({php_cgi_exe})")
    print()

    # ---- Step 2: Detect IP ----
    ip = get_network_ip()
    print(f"[OK] Network IP: {ip}")
    print()

    # ---- Step 3: Hosts File ----
    hosts_ok = add_domain_to_hosts(ip)
    if hosts_ok:
        flush_dns()
    print()

    # ---- Step 4: Certificate ----
    install_certificate()
    print()

    # ---- Step 4.5: Expose cert ----
    try:
        public_cert_path = os.path.join(PUBLIC_DIR, "caddy-root.crt")
        if os.path.isfile(CADDY_ROOT_CERT):
            shutil.copy2(CADDY_ROOT_CERT, public_cert_path)
            print("[OK] Certificate exposed for client download")
    except Exception as e:
        print(f"[WARN] Could not expose certificate: {e}")
    print()

    # ---- Step 5: Laravel Optimizations ----
    run_laravel_optimizations()
    print()

    # ---- Step 6: Create Caddyfile ----
    create_caddyfile(ip)
    print()

    # ---- Step 7: Start FastCGI Process Pool ----
    fastcgi_proc = start_fastcgi_server(php_cgi_exe)
    if not fastcgi_proc:
        input("\nPress Enter to exit...")
        sys.exit(1)
    print()

    # ---- Step 8: Start Caddy HTTPS ----
    caddy_proc = start_caddy(ip)
    if not caddy_proc:
        fastcgi_proc.terminate()
        input("\nPress Enter to exit...")
        sys.exit(1)
    print()

    # ---- Step 9: Start Queue Worker ----
    queue_proc = start_queue_worker()
    print()

    # ---- Step 10: Open Chrome ----
    time.sleep(1)
    open_chrome(f"https://{LOCAL_DOMAIN}")
    print()

    # ---- Summary ----
    print("=" * 55)
    print("  [OK] Server running in PRODUCTION mode via PHP FastCGI + Caddy")
    print(f"  FastCGI Pool -> 127.0.0.1:{FASTCGI_PORT} (20 Workers)")
    print(f"  HTTPS Url    -> https://{LOCAL_DOMAIN} (IP: {ip})")
    print("=" * 55)
    print()
    print("Press Ctrl+C to stop servers...")
    print()

    # ---- Keep running ----
    procs = [fastcgi_proc, caddy_proc, queue_proc]
    try:
        while True:
            if fastcgi_proc.poll() is not None:
                print("[!] FastCGI server stopped unexpectedly")
                break
            if caddy_proc.poll() is not None:
                print("[!] Caddy stopped unexpectedly")
                break
            time.sleep(2)
    except KeyboardInterrupt:
        pass

    cleanup(procs)
    print()
    input("Press Enter to close...")


if __name__ == "__main__":
    main()
