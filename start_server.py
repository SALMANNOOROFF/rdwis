import subprocess
import socket
import os
import sys
import time
import ctypes

# ==========================================================
#  RDWIS 2.0 - Portable HTTPS Server Launcher
# ==========================================================
#  * Fully OFFLINE - no internet needed
#  * Fully PORTABLE - copy project to any PC, run this script
#  * Custom local domain name (https://rdwis)
#  * Auto generates + installs SSL certificate
#  * Auto configures Windows hosts file
#  * Auto starts PHP + Caddy HTTPS server
#  * Auto opens Chrome
#
#  HOW TO USE:
#    Run: python start_server.py (it will auto-elevate)
#
#  FOLDER STRUCTURE:
#    project/
#      caddy/
#        caddy.exe        <-- put caddy.exe here
#        pki/             <-- auto-generated certificates
#      public/            <-- Laravel public folder
#      start_server.py    <-- this script
# ==========================================================

# =================== CONFIGURATION =======================
# Change this domain name for each project!
# Example: "rdwis", "hrms", "payroll", "inventory"
LOCAL_DOMAIN = "rdwis"
PHP_PORT = 8000
# ==========================================================

KEEP_ALIVE_FILES = []


SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
CADDY_DIR = os.path.join(SCRIPT_DIR, "caddy")
CADDY_EXE = os.path.join(CADDY_DIR, "caddy.exe")
PUBLIC_DIR = os.path.join(SCRIPT_DIR, "public")
CADDYFILE = os.path.join(SCRIPT_DIR, "Caddyfile")
HOSTS_FILE = r"C:\Windows\System32\drivers\etc\hosts"

# Certificate stored inside caddy/ folder (portable with project!)
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
    # Method 1: UDP socket trick (no data actually sent, uses OS routing table)
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

    # Method 2: Hostname lookup (fully offline)
    try:
        hostname = socket.gethostname()
        ips = socket.getaddrinfo(hostname, None, socket.AF_INET)
        for addr in ips:
            ip = addr[4][0]
            if not ip.startswith("127."):
                return ip
    except Exception:
        pass

    # Method 3: PowerShell (offline)
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


def kill_existing_servers():
    """Kill any leftover caddy/php processes from previous runs."""
    for name in ["caddy", "php"]:
        os.system(f'taskkill /F /IM {name}.exe >nul 2>&1')
    # Wait for ports to be fully released
    time.sleep(3)


# ----------------------------------------------------------
#  HOSTS FILE MANAGEMENT
# ----------------------------------------------------------

def is_domain_in_hosts(ip):
    """Check if our domain is already in the hosts file."""
    try:
        with open(HOSTS_FILE, "r") as f:
            content = f.read()
        # Check for our domain (any IP mapping)
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
        # Read current content
        with open(HOSTS_FILE, "r") as f:
            content = f.read()

        # Remove any old entries for this domain
        new_lines = []
        for line in content.splitlines():
            stripped = line.strip()
            if stripped and not stripped.startswith("#"):
                parts = stripped.split()
                if len(parts) >= 2 and LOCAL_DOMAIN in parts[1:]:
                    continue  # Skip old entry
            new_lines.append(line)

        # Add our entry
        new_lines.append(f"\n# RDWIS 2.0 - Auto-generated by start_server.py")
        new_lines.append(f"{ip}\t{LOCAL_DOMAIN}")
        new_lines.append("")

        with open(HOSTS_FILE, "w") as f:
            f.write("\n".join(new_lines))

        print(f"[OK] Domain '{LOCAL_DOMAIN}' added to hosts file")
        return True

    except PermissionError:
        print(f"[FAIL] Cannot write to hosts file (need Administrator!)")
        print(f"       Use START_HTTPS.bat or run as Administrator")
        return False
    except Exception as e:
        print(f"[FAIL] Error modifying hosts file: {e}")
        return False


def flush_dns():
    """Flush DNS cache so the new hosts entry takes effect immediately."""
    os.system('ipconfig /flushdns >nul 2>&1')


# ----------------------------------------------------------
#  CERTIFICATE MANAGEMENT
# ----------------------------------------------------------

def get_caddy_env():
    """Environment for Caddy to store data in portable caddy/ folder."""
    env = os.environ.copy()
    env["XDG_DATA_HOME"] = SCRIPT_DIR
    return env


def is_cert_in_store():
    """Check if Caddy root CA is already in any Windows trust store."""
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
    """Start Caddy briefly to generate its internal CA (offline)."""
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
    """Install Caddy root CA into Windows trust store."""
    if is_cert_in_store():
        print("[OK] Certificate already trusted by Windows")
        return True

    if not os.path.isfile(CADDY_ROOT_CERT):
        if not generate_caddy_ca():
            return False

    print("[>>] Installing certificate to Windows trust store...")

    if is_admin():
        # Admin mode: install to MACHINE store (silent, no popup!)
        result = subprocess.run(
            ["certutil", "-addstore", "Root", CADDY_ROOT_CERT],
            capture_output=True, text=True, timeout=30
        )
        if result.returncode == 0:
            print("[OK] Certificate installed silently (machine store)")
            return True
        else:
            print("[WARN] Machine store failed, trying user store...")

    # Non-admin: install to USER store (may show dialog)
    print("[>>] Installing to user certificate store...")
    print("     (If a Security Warning appears, click 'Yes')")
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
#  SERVER MANAGEMENT
# ----------------------------------------------------------

def create_caddyfile(ip):
    """Create Caddyfile for HTTPS reverse proxy with custom domain."""
    content = f"""\
{{
    auto_https disable_redirects
}}

https://{LOCAL_DOMAIN}, https://{ip} {{
    tls internal
    reverse_proxy {ip}:{PHP_PORT}
}}
"""
    with open(CADDYFILE, "w") as f:
        f.write(content)
    print(f"[OK] Caddyfile created (domain: {LOCAL_DOMAIN})")


def start_php_server(ip):
    """Start PHP built-in server."""
    cmd = f"php -S {ip}:{PHP_PORT} -t public"
    print(f"[>>] Starting PHP: {cmd}")

    log_file = os.path.join(SCRIPT_DIR, "php_server.log")
    log_fh = open(log_file, "w")
    KEEP_ALIVE_FILES.append(log_fh)

    proc = subprocess.Popen(
        ["php", "-S", f"{ip}:{PHP_PORT}", "-t", "public"],
        cwd=SCRIPT_DIR,
        stdin=subprocess.DEVNULL,
        stdout=log_fh,
        stderr=log_fh,
        creationflags=subprocess.CREATE_NEW_PROCESS_GROUP,
    )
    time.sleep(2)

    exit_code = proc.poll()
    if exit_code is not None:
        print(f"[FAIL] PHP server failed to start! (Exit code: {exit_code})")
        # Show error from log
        try:
            with open(log_file, "r") as f:
                err = f.read().strip()
            if err:
                print(f"       {err[:200]}")
        except Exception:
            pass
        print("       Make sure PHP is installed and in your PATH.")
        return None

    print(f"[OK] PHP running -> http://{ip}:{PHP_PORT}")
    print(f"     Log: {log_file}")
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


def cleanup(php_proc, caddy_proc):
    """Stop servers and clean up."""
    print()
    print("[>>] Shutting down...")

    if caddy_proc:
        caddy_proc.terminate()
        try:
            caddy_proc.wait(timeout=5)
        except Exception:
            caddy_proc.kill()

    if php_proc:
        php_proc.terminate()
        try:
            php_proc.wait(timeout=5)
        except Exception:
            php_proc.kill()

    if os.path.exists(CADDYFILE):
        os.remove(CADDYFILE)

    # Close keep alive files
    for fh in KEEP_ALIVE_FILES:
        try:
            fh.close()
        except Exception:
            pass

    # Do not delete log files here to allow debugging if needed

    print("[OK] All cleaned up and stopped!")


# ----------------------------------------------------------
#  MAIN
# ----------------------------------------------------------

def main():
    print()
    print("=" * 55)
    print("   RDWIS 2.0 - Portable HTTPS Server")
    print(f"   Domain: https://{LOCAL_DOMAIN}")
    print("=" * 55)
    print()

    admin = is_admin()
    if not admin:
        run_as_admin()
        return

    print("[OK] Running as Administrator")
    print()

    # ---- Step 0: Kill any old servers ----
    print("[>>] Killing any old server processes...")
    kill_existing_servers()
    print("[OK] Clean slate")
    print()

    # ---- Step 1: Verify required files ----
    errors = []
    if not os.path.isfile(CADDY_EXE):
        errors.append(f"caddy.exe not found in: {CADDY_DIR}")
        errors.append("       Put caddy.exe inside the caddy/ folder")
    if not os.path.isdir(PUBLIC_DIR):
        errors.append("public/ directory not found")

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
    print("[OK] PHP available")
    print()

    # ---- Step 2: Detect IP (offline) ----
    ip = get_network_ip()
    print(f"[OK] Network IP: {ip}")
    print()

    # ---- Step 3: Add domain to hosts file ----
    hosts_ok = add_domain_to_hosts(ip)
    if hosts_ok:
        flush_dns()
    else:
        if not admin:
            print("[!] Cannot modify hosts file without admin.")
            print("    Use START_HTTPS.bat for auto-setup.")
        print()
    print()

    # ---- Step 4: Certificate (generate + install) ----
    install_certificate()
    print()

    # ---- Step 4.5: Expose certificate for client download ----
    try:
        import shutil
        public_cert_path = os.path.join(PUBLIC_DIR, "caddy-root.crt")
        if os.path.isfile(CADDY_ROOT_CERT):
            shutil.copy2(CADDY_ROOT_CERT, public_cert_path)
            print("[OK] Certificate exposed for client download")
    except Exception as e:
        print(f"[WARN] Could not expose certificate: {e}")
    print()

    # ---- Step 5: Create Caddyfile ----
    create_caddyfile(ip)
    print()

    # ---- Step 6: Start PHP server ----
    php_proc = start_php_server(ip)
    if not php_proc:
        input("\nPress Enter to exit...")
        sys.exit(1)
    print()

    # ---- Step 7: Start Caddy HTTPS ----
    caddy_proc = start_caddy(ip)
    if not caddy_proc:
        php_proc.terminate()
        input("\nPress Enter to exit...")
        sys.exit(1)
    print()

    # ---- Step 8: Open Chrome ----
    time.sleep(1)
    open_chrome(f"https://{LOCAL_DOMAIN}")
    print()

    # ---- Summary ----
    print("=" * 55)
    print(f"  PHP   -> http://{ip}:{PHP_PORT}")
    print(f"  HTTPS -> https://{LOCAL_DOMAIN}")
    print("=" * 55)
    print()
    print("Press Ctrl+C to stop servers...")
    print()

    # ---- Keep running ----
    try:
        while True:
            if php_proc.poll() is not None:
                print("[!] PHP server stopped unexpectedly")
                caddy_proc.terminate()
                break
            if caddy_proc.poll() is not None:
                print("[!] Caddy stopped unexpectedly")
                php_proc.terminate()
                break
            time.sleep(2)
    except KeyboardInterrupt:
        pass

    cleanup(php_proc, caddy_proc)
    print()
    input("Press Enter to close...")


if __name__ == "__main__":
    main()
