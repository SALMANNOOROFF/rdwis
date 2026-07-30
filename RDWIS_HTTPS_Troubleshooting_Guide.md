# RDWIS 2.0 - HTTPS & SSL Troubleshooting Guide

This guide provides solutions to common certificate, network, and firewall issues when deploying RDWIS 2.0 on an offline Windows Server 2016 VM or host PC, and connecting client PCs to it.

---

## 1. Quick Reference: Ports & URLs

By default, RDWIS 2.0 does not use standard HTTP/HTTPS ports (80/443) to prevent conflicts with other services like XAMPP or IIS.

* **HTTP Port:** `8000` (Used for automatic redirect to HTTPS and certificate download)
* **HTTPS Port:** `8443` (Main secure port used to run the application)
* **PHP FastCGI Port:** `9000` (Internal port, handled automatically)

### URLs to Access the App:
* **From the Server PC:**
  * `https://rdwis:8443`
  * `https://localhost:8443`
* **From a Client PC (on the same network):**
  * `https://<SERVER_IP>:8443`
  * `https://rdwis:8443` (Only if hosts file is configured on the client)

> [!WARNING]
> **Always append `:8443` at the end of the URL.** If you write `https://rdwis` or `https://192.168.1.100`, Chrome will try port `443` which will fail or hit XAMPP.

---

## 2. Issue: "Site Can't Be Reached" / Connection Timed Out (Firewall)

Windows Server 2016 has a very strict firewall by default that blocks ports `8000` and `8443` from network clients.

### Solution: Allow Ports in Windows Firewall
You must create inbound rules on the **Host Server PC/VM** to allow traffic on ports `8000` and `8443`.

#### Option A: Quick PowerShell Script (Recommended)
1. Search for **PowerShell** in the Start Menu.
2. Right-click and choose **Run as Administrator**.
3. Copy, paste, and run the following commands:
   ```powershell
   # Allow RDWIS HTTP (Certificate Download & Redirects)
   New-NetFirewallRule -DisplayName "RDWIS HTTP Server (Port 8000)" -Direction Inbound -LocalPort 8000 -Protocol TCP -Action Allow -Profile Any

   # Allow RDWIS HTTPS (Main Application)
   New-NetFirewallRule -DisplayName "RDWIS HTTPS Server (Port 8443)" -Direction Inbound -LocalPort 8443 -Protocol TCP -Action Allow -Profile Any
   ```

#### Option B: Manual Windows Firewall GUI Steps
1. Open **Windows Firewall with Advanced Security** (type `wf.msc` in Run dialog).
2. Click **Inbound Rules** in the left panel.
3. Click **New Rule...** in the right panel.
4. Select **Port** and click Next.
5. Choose **TCP** and enter `8000, 8443` in **Specific local ports**. Click Next.
6. Select **Allow the connection** and click Next.
7. Leave Domain, Private, and Public checked. Click Next.
8. Name the rule `RDWIS Server Ports` and click **Finish**.

---

## 3. Issue: "Not Secure" / SSL Certificate Error in Chrome

Even after importing the certificate, Chrome might show "Your connection is not private" (NET::ERR_CERT_AUTHORITY_INVALID).

### Solution A: Correct Windows Certificate Store Import
If you manually double-clicked the certificate and clicked Next/OK, Windows may have placed it in the "Personal" folder, which Chrome does not trust for CA certs.

1. Locate the root certificate (`caddy-root.crt`) in the project's `public` directory or download it from `http://<SERVER_IP>:8000/caddy-root.crt`.
2. Double-click the `.crt` file.
3. Click **Install Certificate...**
4. Select **Local Machine** and click Next (Approve the UAC prompt).
5. Choose **"Place all certificates in the following store"** and click **Browse**.
6. Select **Trusted Root Certification Authorities** and click OK.
7. Click Next and then **Finish**.

### Solution B: Chrome Hard Restart (Clearing SSL Cache)
Chrome caches SSL state and certificate status. If you installed the certificate while Chrome was running, it might not pick it up.

1. Open Chrome.
2. Type `chrome://restart` in the address bar and press Enter. Chrome will close and reopen all tabs.
3. Try accessing `https://rdwis:8443` again.
4. Alternatively, test the URL inside an **Incognito / Private Window** to bypass the cache.

---

## 4. Issue: "rdwis" Domain Does Not Resolve (DNS Issues)

If `https://localhost:8443` works but `https://rdwis:8443` does not resolve, the hosts file configuration failed or is missing.

### Solution: Configure Windows Hosts File

#### On the Host VM/Server:
The `start_server.py` script automatically does this if run as Administrator. To verify:
1. Open `C:\Windows\System32\drivers\etc\hosts` using Notepad (as Administrator).
2. Ensure there is a line like this at the bottom:
   ```text
   192.168.1.100    rdwis
   ```
   *(Where `192.168.1.100` is the actual IP of the VM).*
3. Open CMD and type:
   ```cmd
   ipconfig /flushdns
   ping rdwis
   ```
   It should resolve and ping your VM's IP address.

#### On Client PCs:
Client PCs do not know what `rdwis` is unless you run the setup script or edit their hosts file.
* **Automated Way:** Copy `start_client.py` to the Client PC, run it as Administrator, and enter the Host VM's IP address.
* **Manual Way:** Edit the Client PC's hosts file (`C:\Windows\System32\drivers\etc\hosts`) and add:
  ```text
  <SERVER_IP>    rdwis
  ```
  Then run `ipconfig /flushdns` in CMD.

---

## 5. Issue: Server Script Fails to Start (Port Conflict)

If the Python script crashes or outputs `[FAIL] Caddy failed to start!` or `[FAIL] PHP FastCGI server failed to start!`.

### Solution: Kill Conflicting Processes
Another service is already listening on port `8000`, `8443`, or `9000`.

1. Open Command Prompt as Administrator.
2. Find the Process ID (PID) occupying the port (e.g., `8443`):
   ```cmd
   netstat -ano | findstr 8443
   ```
3. Look at the number at the far right of the output (this is the PID).
4. Terminate that process using taskkill:
   ```cmd
   taskkill /F /PID <PID_NUMBER>
   ```
5. Rerun `start_server.py`.

---

## 6. How to Handle AppScan / Security Scanner Low Findings

If you run a security audit tool (like HCL AppScan) on RDWIS 2.0, you may see a few remaining **Low Severity** findings. Here is the explanation and action required for each:

### A. Finding: "Missing or Insecure 'Script-Src' policy in 'Content-Security-Policy' header"
* **Why it happens:** The scanner flags that the CSP header contains `'unsafe-inline'` and `'unsafe-eval'`.
* **Technical Reality:** RDWIS 2.0 is a Laravel application. Its frontend templates (Blade views) contain inline Javascript blocks for page behavior, dynamic forms, AJAX calls, and interactive UI components (like charts, knobs, and overlays). Removing `'unsafe-inline'` or `'unsafe-eval'` will **break the user interface**.
* **Remediation:** Since the application runs in a **fully offline, closed intranet VM environment** with no internet access, the risk of Cross-Site Scripting (XSS) via external sources is extremely low. Mark this finding as **Accepted Risk / False Positive**.

### B. Finding: "Hidden Directory Detected" (`admin/`, `login/`, `training/`)
* **Why it happens:** Scanners guess common web directory names. If the URL returns a success code (`200 OK`) or a redirect (`302`), it assumes it discovered a "hidden" folder.
* **Technical Reality:** These are **not** physical directories or leftover files. They are the actual Laravel routing entry points of the application:
  * `/login` is the main login portal.
  * `/admin` is the administrator dashboard.
  * `/training` is the training module entry point.
  * These routes must exist for the application to function.
* **Remediation:** Mark these findings as **Noise / False Positive** in the scanner, as they represent legitimate public endpoints, not exposed system folders.
