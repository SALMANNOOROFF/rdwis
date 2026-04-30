import subprocess
import socket
import time
import webbrowser

PROJECT_DIR = r"E:\RDWIS ENHANCED"
LARAVEL_HOST = "172.16.4.92"
LARAVEL_PORT = 8000
OPEN_URL = "https://172.16.4.92"

def log(msg):
    print(f"[RDWIS] {msg}")

def is_process_running(name):
    try:
        out = subprocess.check_output(
            ["tasklist", "/FI", f"IMAGENAME eq {name}"],
            encoding="utf-8", errors="ignore"
        )
        return name.lower() in out.lower()
    except:
        return False

def is_port_open(host, port):
    try:
        s = socket.create_connection((host, port), timeout=2)
        s.close()
        return True
    except:
        return False

log("Checking servers...")

if not is_process_running("caddy.exe"):
    log("Caddy not running — starting...")
    subprocess.Popen(
        ["caddy", "reverse-proxy", "--from", "https://172.16.4.92", "--to", "http://172.16.4.92:8000"],
        creationflags=subprocess.CREATE_NO_WINDOW
    )
else:
    log("Caddy already running.")

if not is_port_open(LARAVEL_HOST, LARAVEL_PORT):
    log("Laravel not running — starting...")
    subprocess.Popen(
        ["php", "artisan", "serve", f"--host={LARAVEL_HOST}", f"--port={LARAVEL_PORT}"],
        cwd=PROJECT_DIR,
        creationflags=subprocess.CREATE_NO_WINDOW
    )
    log("Waiting for Laravel to boot...")
    time.sleep(5)
else:
    log("Laravel already running.")

log(f"Opening {OPEN_URL} in browser...")
webbrowser.open(OPEN_URL)

log("Done!")
input("\nPress Enter to close...")