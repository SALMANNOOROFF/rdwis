import subprocess
import os
import sys
import time
import ctypes
import urllib.request

# ==========================================================
#  RDWIS 2.0 - Client-Side Auto Configuration Tool
# ==========================================================
#  * Run this script on any client PC on the local network.
#  * Prompts for the host server's IP address.
#  * Downloads the SSL certificate from the host server.
#  * Installs the certificate to Windows Trusted Root store.
#  * Maps the server's dynamic IP to "rdwis" in the hosts file.
#  * Auto-opens Chrome directly to: https://rdwis
# ==========================================================

# =================== CONFIGURATION =======================
LOCAL_DOMAIN = "rdwis"
SERVER_PHP_PORT = 8000
# ==========================================================

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
LOCAL_CERT_PATH = os.path.join(SCRIPT_DIR, "caddy-root.crt")
HOSTS_FILE = r"C:\Windows\System32\drivers\etc\hosts"

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


def find_chrome():
    """Find Chrome on this PC."""
    for path in CHROME_PATHS:
        if os.path.isfile(path):
            return path
    return None


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


# ----------------------------------------------------------
#  HOSTS FILE MANAGEMENT
# ----------------------------------------------------------

def is_domain_in_hosts(ip):
    """Check if our domain is already in the hosts file with the exact IP."""
    try:
        with open(HOSTS_FILE, "r") as f:
            content = f.read()
        for line in content.splitlines():
            line = line.strip()
            if line and not line.startswith("#"):
                parts = line.split()
                if len(parts) >= 2 and parts[0] == ip and LOCAL_DOMAIN in parts[1:]:
                    return True
    except Exception:
        pass
    return False


def add_domain_to_hosts(ip):
    """Add/Update local domain to Windows hosts file."""
    if is_domain_in_hosts(ip):
        print(f"[OK] Domain '{LOCAL_DOMAIN}' already mapped correctly to {ip}")
        return True

    print(f"[>>] Updating hosts file: Mapping '{LOCAL_DOMAIN} -> {ip}'...")

    try:
        with open(HOSTS_FILE, "r") as f:
            content = f.read()

        # Remove any old entries mapped to this domain (even with different IPs)
        new_lines = []
        for line in content.splitlines():
            stripped = line.strip()
            if stripped and not stripped.startswith("#"):
                parts = stripped.split()
                if len(parts) >= 2 and LOCAL_DOMAIN in parts[1:]:
                    continue  # Remove old mapping
            new_lines.append(line)

        # Add new mapping
        new_lines.append(f"\n# RDWIS 2.0 Client - Auto-generated for host {ip}")
        new_lines.append(f"{ip}\t{LOCAL_DOMAIN}")
        new_lines.append("")

        with open(HOSTS_FILE, "w") as f:
            f.write("\n".join(new_lines))

        print(f"[OK] Hosts file updated successfully!")
        os.system('ipconfig /flushdns >nul 2>&1')
        return True

    except PermissionError:
        print("[FAIL] Permission denied writing to hosts file.")
        return False
    except Exception as e:
        print(f"[FAIL] Error writing hosts file: {e}")
        return False


# ----------------------------------------------------------
#  CERTIFICATE MANAGEMENT
# ----------------------------------------------------------

def download_certificate(server_ip):
    """Download Caddy's root CA certificate from the server."""
    url = f"http://{server_ip}:{SERVER_PHP_PORT}/caddy-root.crt"
    print(f"[>>] Downloading certificate from: {url}")
    try:
        urllib.request.urlretrieve(url, LOCAL_CERT_PATH)
        print("[OK] Certificate downloaded successfully!")
        return True
    except Exception as e:
        print(f"[FAIL] Could not connect to server at {server_ip}:{SERVER_PHP_PORT}")
        print("       1. Make sure start_server.py is running on the host server PC.")
        print("       2. Check if host PC Firewall is blocking port 8000/443.")
        print(f"       Debug info: {e}")
        return False


def install_certificate():
    """Install the downloaded root CA certificate into Windows Trusted Root store."""
    if not os.path.isfile(LOCAL_CERT_PATH):
        print("[FAIL] Local certificate file not found!")
        return False

    print("[>>] Installing certificate to Windows Trusted Root store...")

    # Install to machine store silently since we have admin
    result = subprocess.run(
        ["certutil", "-addstore", "Root", LOCAL_CERT_PATH],
        capture_output=True, text=True, timeout=30
    )

    if result.returncode == 0:
        print("[OK] SSL certificate trusted successfully!")
        # Clean up downloaded cert file
        try:
            os.remove(LOCAL_CERT_PATH)
        except Exception:
            pass
        return True
    else:
        print(f"[FAIL] certutil failed with error: {result.stderr or result.stdout}")
        return False


# ----------------------------------------------------------
#  MAIN
# ----------------------------------------------------------

def main():
    print()
    print("=" * 55)
    print("   RDWIS 2.0 - Client Auto-Configurator")
    print("   Maps domain + trusts SSL certificate automatically")
    print("=" * 55)
    print()

    # Step 0: Ensure Admin Privileges
    if not is_admin():
        run_as_admin()
        return

    print("[OK] Running as Administrator")
    print()

    # Step 1: Prompt for Server IP
    print("Enter the IP Address of the host server PC.")
    print("(You can find this IP printed on the host server screen, e.g. 192.168.1.169)")
    server_ip = input("Server IP Address: ").strip()

    if not server_ip:
        print("[FAIL] IP address cannot be empty!")
        input("\nPress Enter to exit...")
        sys.exit(1)

    print()

    # Step 2: Download SSL Certificate
    if not download_certificate(server_ip):
        input("\nPress Enter to exit...")
        sys.exit(1)
    print()

    # Step 3: Install certificate
    if not install_certificate():
        input("\nPress Enter to exit...")
        sys.exit(1)
    print()

    # Step 4: Map domain in hosts file
    if not add_domain_to_hosts(server_ip):
        input("\nPress Enter to exit...")
        sys.exit(1)
    print()

    # Step 5: Launch browser
    url = f"https://{LOCAL_DOMAIN}"
    print(f"[>>] Launching RDWIS domain...")
    open_chrome(url)
    print()

    print("=" * 55)
    print(f"  Configuration completed successfully!")
    print(f"  You can now access: {url}")
    print("=" * 55)
    print()
    input("Press Enter to close...")


if __name__ == "__main__":
    main()
