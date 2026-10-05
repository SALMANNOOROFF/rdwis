import os
import sys
import shutil
import subprocess

# ==============================================================================
# Database Configuration / ڈیٹا بیس کی ترتیبات
# ==============================================================================
DB_HOST = "127.0.0.1"
DB_PORT = "5433"          # Default port for PostgreSQL 18 in RDWIS
DB_NAME = "updatedrdwV1"
DB_USER = "postgres"
DB_PASSWORD = "12345678"

# ==============================================================================
# Find psql executable / psql ٹول تلاش کرنے کا فنکشن
# ==============================================================================
def find_psql():
    possible_paths = [
        r"C:\Program Files\PostgreSQL\18\bin\psql.exe",
        r"C:\Program Files\PostgreSQL\17\bin\psql.exe",
        r"C:\Program Files\PostgreSQL\16\bin\psql.exe",
        r"C:\Program Files\PostgreSQL\15\bin\psql.exe",
        r"C:\Program Files\PostgreSQL\14\bin\psql.exe",
        r"C:\Program Files\PostgreSQL\13\bin\psql.exe",
    ]
    for path in possible_paths:
        if os.path.isfile(path):
            return path
            
    system_psql = shutil.which("psql")
    if system_psql:
        return system_psql
        
    return "psql"

# ==============================================================================
# SQL Migration Script (Runs inside an atomic transaction)
# ==============================================================================
SQL_COMMANDS = """
BEGIN;

-- 1. Ensure foreign key has ON UPDATE CASCADE so role updates propagate seamlessly
ALTER TABLE cen.accounts DROP CONSTRAINT IF EXISTS accounts_fk;
ALTER TABLE cen.accounts ADD CONSTRAINT accounts_fk 
    FOREIGN KEY (acc_desig) REFERENCES cen.roles(rol_desig) 
    ON UPDATE CASCADE ON DELETE RESTRICT;

-- 2. Update Units in cen.units
-- Target 1: Communication Division (200000)
UPDATE cen.units 
SET unt_name = 'Command, Control and Communication (C3)', 
    unt_namesh = 'C#', 
    unt_leaddesig = 'Director Command, Control and Communication (C3)', 
    unt_leaddesigshort = 'Dir C#' 
WHERE unt_id = 200000;

-- Target 2: Systems Division (450000)
UPDATE cen.units 
SET unt_name = 'Ex System Division', 
    unt_namesh = 'Ex Sys Div', 
    unt_leaddesig = 'Director Ex Systems', 
    unt_leaddesigshort = 'Dir Ex Sys' 
WHERE unt_id = 450000;

-- Target 3: Enabling Technology Division (250000)
UPDATE cen.units 
SET unt_name = 'AI and Immersive Technology Division', 
    unt_namesh = 'AI & Emers', 
    unt_leaddesig = 'Director AI and Immersive Technology', 
    unt_leaddesigshort = 'Dir AI & Emers' 
WHERE unt_id = 250000;

-- Target 4: System of Systems Engineering Division (400000)
UPDATE cen.units 
SET unt_name = 'Radar and EW Division', 
    unt_namesh = 'R & EW', 
    unt_leaddesig = 'Director Radar and EW Division', 
    unt_leaddesigshort = 'Dir R & EW' 
WHERE unt_id = 400000;

-- Target 5: Procurement Department (810000)
UPDATE cen.units 
SET unt_name = 'Inventory and Procurement Department', 
    unt_namesh = 'P & I', 
    unt_leaddesig = 'Director Inventory and Procurement', 
    unt_leaddesigshort = 'Dir P & I' 
WHERE unt_id = 810000;

-- Target 6: Naval Weapons System Division (300000)
UPDATE cen.units 
SET unt_name = 'Under Water Tech Division', 
    unt_namesh = 'UWT div', 
    unt_leaddesig = 'Director Under Water Tech', 
    unt_leaddesigshort = 'Dir UWT' 
WHERE unt_id = 300000;

-- Target 7: Sensors Division (350000)
UPDATE cen.units 
SET unt_name = 'Sensors and UAV Technology Division', 
    unt_namesh = 'S & UAV', 
    unt_leaddesig = 'Director Sensors and UAV Technology', 
    unt_leaddesigshort = 'Dir S & UAV' 
WHERE unt_id = 350000;

-- 3. Update Heads in cen.heads
UPDATE cen.heads SET hed_name = 'Command, Control and Communication (C3)', hed_code = 'C#' WHERE hed_id = 200000;
UPDATE cen.heads SET hed_name = 'Ex System Division', hed_code = 'EXSYS' WHERE hed_id = 450000;
UPDATE cen.heads SET hed_name = 'AI and Immersive Technology Division', hed_code = 'AI&EMERS' WHERE hed_id = 250000;
UPDATE cen.heads SET hed_name = 'Radar and EW Division', hed_code = 'R&EW' WHERE hed_id = 400000;
UPDATE cen.heads SET hed_name = 'Under Water Tech Division', hed_code = 'UWT' WHERE hed_id = 300000;
UPDATE cen.heads SET hed_name = 'Sensors and UAV Technology Division', hed_code = 'S&UAV' WHERE hed_id = 350000;

-- 4. Update Roles in cen.roles (Cascades automatically to cen.accounts.acc_desig)
-- Communication
UPDATE cen.roles SET rol_desig = 'Director Command, Control and Communication (C3)', rol_desigshort = 'DC#' WHERE rol_desig = 'Director Communication';
UPDATE cen.roles SET rol_desig = 'Deputy Director Command, Control and Communication (C3)', rol_desigshort = 'DDC#' WHERE rol_desig = 'Deputy Director Communication';

-- Systems
UPDATE cen.roles SET rol_desig = 'Director Ex Systems', rol_desigshort = 'DExSys' WHERE rol_desig = 'Director Systems';
UPDATE cen.roles SET rol_desig = 'Deputy Director Ex Systems', rol_desigshort = 'DDExSys' WHERE rol_desig = 'Deputy Director Systems';
UPDATE cen.roles SET rol_desig = 'PI Ex Systems', rol_desigshort = 'PI ExSys' WHERE rol_desig = 'PI Systems';

-- Enabling Tech -> AI & Immersive
UPDATE cen.roles SET rol_desig = 'Director AI and Immersive Technology', rol_desigshort = 'DAI&Emers' WHERE rol_desig = 'Director Enabling Technology';
UPDATE cen.roles SET rol_desig = 'Deputy Director AI and Immersive Technology', rol_desigshort = 'DDAI&Emers' WHERE rol_desig = 'Deputy Director Enabling Technology';

-- System of Systems -> Radar and EW
UPDATE cen.roles SET rol_desig = 'Director Radar and EW', rol_desigshort = 'DREW' WHERE rol_desig = 'Director System of Systems';
UPDATE cen.roles SET rol_desig = 'Deputy Director Radar and EW', rol_desigshort = 'DDREW' WHERE rol_desig = 'Deputy Director System of Systems';

-- Naval Weapons Systems -> Under Water Tech
UPDATE cen.roles SET rol_desig = 'Director Under Water Tech', rol_desigshort = 'DUWT' WHERE rol_desig = 'Director Naval Weapon Systems';
UPDATE cen.roles SET rol_desig = 'Deputy Director Under Water Tech', rol_desigshort = 'DDUWT' WHERE rol_desig = 'Deputy Director Naval Weapon Systems';

-- Sensors -> Sensors and UAV Tech
UPDATE cen.roles SET rol_desig = 'Director Sensors and UAV Technology', rol_desigshort = 'DS&UAV' WHERE rol_desig = 'Director Sensors';
UPDATE cen.roles SET rol_desig = 'Deputy Director Sensors and UAV Technology', rol_desigshort = 'DDS&UAV' WHERE rol_desig = 'Deputy Director Sensors';

-- Procurement
UPDATE cen.roles SET rol_desig = 'Director Inventory and Procurement', rol_desigshort = 'DProc' WHERE rol_desig = 'Director Procurement';

-- 5. Synchronize all accounts in cen.accounts
UPDATE cen.accounts
SET acc_untname = u.unt_name,
    acc_untnamesh = u.unt_namesh
FROM cen.units u
WHERE cen.accounts.acc_unt_id = u.unt_id;

UPDATE cen.accounts
SET acc_desigshort = r.rol_desigshort
FROM cen.roles r
WHERE cen.accounts.acc_desig = r.rol_desig;

COMMIT;
"""

def execute_renaming():
    print("=" * 70)
    print("RDWIS CEN SCHEMA RENAMING UTILITY")
    print("=" * 70)
    print(f"Database   : {DB_NAME}")
    print(f"Host:Port  : {DB_HOST}:{DB_PORT}")
    print(f"Username   : {DB_USER}")
    
    psql_bin = find_psql()
    print(f"psql Path  : {psql_bin}")
    
    env = os.environ.copy()
    env["PGPASSWORD"] = DB_PASSWORD

    cmd = [
        psql_bin,
        "-h", DB_HOST,
        "-p", DB_PORT,
        "-U", DB_USER,
        "-d", DB_NAME,
        "-v", "ON_ERROR_STOP=1"
    ]

    print("\nExecuting renaming script inside atomic transaction...")
    res = subprocess.run(cmd, env=env, input=SQL_COMMANDS, capture_output=True, text=True)

    if res.returncode != 0:
        print("\n[ERROR] Transaction failed and rolled back cleanly.")
        print(res.stderr)
        return False

    print("\n[SUCCESS] All units, heads, roles, and accounts renamed and synced successfully!")
    
    # Verification Query
    verify_sql = """
    SELECT acc_id, acc_username, acc_name, acc_desig, acc_desigshort, acc_untname, acc_untnamesh 
    FROM cen.accounts 
    WHERE acc_unt_id IN (200000, 450000, 250000, 400000, 810000, 300000, 350000) 
    ORDER BY acc_unt_id, acc_id;
    """
    verify_cmd = [psql_bin, "-h", DB_HOST, "-p", DB_PORT, "-U", DB_USER, "-d", DB_NAME, "-c", verify_sql]
    verify_res = subprocess.run(verify_cmd, env=env, capture_output=True, text=True)
    
    print("\n--- UPDATED ACCOUNTS IN TARGET UNITS ---")
    print(verify_res.stdout)
    print("=" * 70)
    return True


if __name__ == "__main__":
    success = execute_renaming()
    if not success:
        sys.exit(1)
