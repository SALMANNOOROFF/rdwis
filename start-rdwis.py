import subprocess
import time

PROJECT_DIR = r"E:\RDWIS ENHANCED"

def start_caddy():
    subprocess.Popen(
        ["caddy", "reverse-proxy", "--from", "https://172.16.4.92", "--to", "http://172.16.4.92:8000"],
        creationflags=subprocess.CREATE_NO_WINDOW
    )
    print("[RDWIS] Caddy started.")

def start_laravel():
    subprocess.Popen(
        ["php", "artisan", "serve", "--host=172.16.4.92", "--port=8000"],
        cwd=PROJECT_DIR,
        creationflags=subprocess.CREATE_NO_WINDOW
    )
    print("[RDWIS] Laravel started.")

print("[RDWIS] Waiting 10s for network to settle...")
time.sleep(10)

start_caddy()
time.sleep(3)
start_laravel()
print("[RDWIS] All servers started.")