# RDWIS 2.0 - Complete Host & Client Setup & Deployment Guide

This guide details the complete process for deploying **RDWIS 2.0** on any new Host PC (Server) and connecting Client PCs across the local network.

---

## SECTION 1: HOST PC SETUP (SERVER SIDE)

### 1. Prerequisites on Host PC
Before launching RDWIS 2.0 on a new Host PC, ensure the following are installed:
1. **Windows OS** (Windows 10 / 11 / Server).
2. **Python 3.x** (Added to Windows PATH).
3. **PHP 8.2+** (XAMPP or standalone PHP in PATH) with `pgsql`, `pdo_pgsql`, `openssl`, and `opcache` extensions enabled.
4. **PostgreSQL 18** (or PostgreSQL 13+) installed and running.
5. **Caddy Web Server**: Included inside the project `caddy/` directory (`caddy.exe`).

---

### 2. File & Configuration Checks on Host PC

#### A. `.env` File Check (`/project_root/.env`)
Verify database credentials match the Host PC's PostgreSQL installation:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5433               # Or 5432 depending on PostgreSQL port on Host PC
DB_DATABASE=rdw
DB_USERNAME=postgres
DB_PASSWORD=your_password  # PostgreSQL password on Host PC

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_EXPIRE_ON_CLOSE=true
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict
LOG_LEVEL=error
```

#### B. `php.ini` Check (`C:\xampp\php\php.ini` or host PHP directory)
Ensure `php.ini` has OPcache and production resource limits enabled:
```ini
zend_extension=opcache

[opcache]
opcache.enable=1
opcache.enable_cli=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000

memory_limit=512M
max_execution_time=120
upload_max_filesize=64M
post_max_size=64M
```

#### C. `postgresql.conf` Check (`C:\Program Files\PostgreSQL\18\data\postgresql.conf`)
Ensure max connections is set to handle concurrent users and VA scanners:
```ini
max_connections = 200
```
*(Restart PostgreSQL service if modified: `net stop postgresql-x64-18 && net start postgresql-x64-18`)*

---

### 3. Launching Server on Host PC (1-Click)

1. Copy the entire `RDWIS APP 2.0` project folder to any location on the Host PC (e.g. `D:\RDWIS APP 2.0`).
2. Right-click `start_server.py` and select **Run with Python** OR double-click `START_HTTPS.bat` (It will request Administrator UAC prompt automatically).
3. **What `start_server.py` does automatically**:
   - Auto-detects the Host PC's current local IPv4 address (e.g. `192.168.1.169`).
   - Maps `rdwis -> [Host IP]` in `C:\Windows\System32\drivers\etc\hosts` & flushes DNS.
   - Generates & installs the local SSL Certificate into Windows Trusted Root Certification Authorities store.
   - Copies `caddy-root.crt` to `public/caddy-root.crt` for client PC downloads.
   - Compiles Laravel production caches (`config:cache`, `route:cache`, `view:cache`, `event:cache`).
   - Launches FastCGI 20-worker Process Pool (`php-cgi.exe -b 127.0.0.1:9000`).
   - Launches Caddy HTTPS reverse proxy with global security headers (HSTS, CSP, X-Frame-Options, No-Cache on manifest).
   - Starts background Queue Worker.
   - Auto-opens Chrome browser to `https://rdwis`.

---

## SECTION 2: CLIENT PC SETUP (NETWORK CLIENTS)

Any Client PC connected to the same local network (LAN / Wi-Fi) can access RDWIS 2.0 via `https://rdwis`.

### Method A: Automated Client Setup (Recommended - 1 Click)

1. Copy `start_client.py` (or share it over network) to the Client PC.
2. Run `python start_client.py` (or `START_CLIENT.bat`) as Administrator on the Client PC.
3. Enter the Host Server's IP address when prompted (e.g., `192.168.1.169`).
4. **What `start_client.py` does automatically**:
   - Downloads the SSL certificate from `https://{HOST_IP}/caddy-root.crt`.
   - Installs the certificate into the Client PC's Windows Trusted Root store.
   - Maps `{HOST_IP}   rdwis` in the Client PC's Windows `hosts` file.
   - Auto-opens Chrome to `https://rdwis`.

---

### Method B: Manual Client Setup (No Python Required)

If Python is not installed on the Client PC, follow these 3 simple steps:

#### Step 1: Install SSL Certificate
1. Open browser on Client PC and go to: `https://{HOST_IP}/caddy-root.crt` (or `http://{HOST_IP}:8000/caddy-root.crt`).
2. Double-click the downloaded `caddy-root.crt` file.
3. Click **Install Certificate...** -> Select **Local Machine** (or Current User) -> Next.
4. Select **Place all certificates in the following store** -> Click **Browse...**.
5. Choose **Trusted Root Certification Authorities** -> Click OK -> Next -> Finish.

#### Step 2: Add Domain to Windows Hosts File
1. Open **Notepad** as Administrator (Search Notepad in Start menu -> Right-click -> Run as administrator).
2. Click **File -> Open** -> Navigate to: `C:\Windows\System32\drivers\etc\hosts` (Change filter from `.txt` to `All Files`).
3. Add the following line at the bottom:
   ```text
   192.168.1.169    rdwis
   ```
   *(Replace `192.168.1.169` with the Host PC's actual local IP).*
4. Save the file and close Notepad.

#### Step 3: Access RDWIS 2.0
Open Chrome or Edge on Client PC and go to:
```text
https://rdwis
```
The connection will show a green/secure lock icon with zero security warnings.

---

## SECTION 3: SUMMARY OF CRITICAL PROJECT FILES

| File Path | Purpose |
| :--- | :--- |
| `start_server.py` | Main Host Server Launcher (Auto IP, SSL, FastCGI 20 Workers, Caddy, Laravel Cache) |
| `start_client.py` | Client PC Auto-Config Tool (SSL Install & Hosts Mapping) |
| `Caddyfile` | Caddy HTTPS Server config (Security Headers, FastCGI proxy to `127.0.0.1:9000`) |
| `.env` | Environment config (`SESSION_LIFETIME=120`, `SESSION_SAME_SITE=strict`, `LOG_LEVEL=error`) |
| `resources/views/auth/login.blade.php` | Login View (`autocomplete="off"`) |
| `public/js/login-helper.js` | Extracted helper functions for login view |
| `C:\xampp\php\php.ini` | Host PHP Config (`opcache.enable=1`, `memory_limit=512M`) |
| `C:\Program Files\PostgreSQL\18\data\postgresql.conf` | Host PostgreSQL Config (`max_connections=200`) |
