# RDWIS Application Documentation

## Table of Contents
1. [Project Overview](#project-overview)
2. [Database Structure](#database-structure)
3. [Authentication & Security](#authentication--security)
4. [Application Architecture](#application-architecture)
5. [Key Features](#key-features)
6. [Security Measures](#security-measures)

---

## Project Overview

**RDWIS** is a comprehensive business management system built on Laravel 12 with a PostgreSQL backend. It was originally an Access-based application that has been ported to a modern web stack.

### Technology Stack
- **Backend**: Laravel 12 (PHP)
- **Database**: PostgreSQL 18
- **Frontend**: Blade templates with AdminLTE
- **Auth**: Custom authentication system with multiple password schemes

---

## Database Structure

### Database Connection
- **Database**: PostgreSQL (default connection)
- **Connection Name**: `pgsql`
- **Host**: `127.0.0.1` (default)
- **Port**: `5432` (default)
- **Database Name**: `acessdev` (default)
- **Username**: `postgres` (default)
- **Password**: `12345` (default)

### Database Schemas
The database is organized into multiple schemas by module:

1. **`aud`** - Audit & revision tracking
2. **`cen`** - Central (users, accounts, units)
3. **`doc`** - Documents
4. **`fin`** - Finance
5. **`frm`** - Firms
6. **`hr`** - Human Resources
7. **`ina`** - Inventory
8. **`prj`** - Projects
9. **`pur`** - Procurement
10. **`puritems`** - Purchase items
11. **`purnew`** - New procurement

### Key Tables by Schema

#### `cen` Schema (Central)
- **`cen.accounts`** - User accounts table (primary authentication table)
  - `acc_id` - Primary key
  - `acc_username` - Username
  - `acc_pass` - Password (encrypted/hash)
  - `acc_name` - Full name
  - `acc_rank` - Rank
  - `acc_desig` - Designation
  - `acc_unt_id` - Unit ID
  - `acc_untarea` - Unit area (hr, fin, prj, nrdi, rdw, hqs, proc, it, etc.)
  - `acc_auth` - Authorization level (approver, editor, viewer)
  - `acc_status` - Account status (Active/Inactive)
  - `acc_access` - Access type (single/multiple)
  - `acc_lowers`, `acc_uppers` - Lower/upper unit range for single access
  - `acc_lowerm`, `acc_upperm` - Lower/upper module range for multiple access

#### `aud` Schema (Audit)
- **`aud.busdata`** - Business data audit trail
  - Tracks all data changes with username, timestamp, action, form, and old/new values
- **`aud.revs`** - Revisions
- **`aud.revcomps`** - Revision components
- **`aud.revdata`** - Revision data
- **`aud.audattachments`** - Audit attachments

#### `pur` Schema (Procurement)
- Procurement cases, quotes, items, decisions, etc.
- Full purchase workflow management

---

## Authentication & Security

### Custom User Authentication System

#### User Provider
Class: `App\Auth\CenAccountUserProvider`
- Extends Laravel's `EloquentUserProvider`
- Retrieves active users from `cen.accounts` table
- Validates credentials using multiple password schemes

#### Password Hashing & Verification
The application supports **multiple password schemes** for backward compatibility:

1. **Modern bcrypt/argon2** - Standard Laravel hashes (prefixed with `$2y$` or `$argon2`)
2. **Custom AES-256 CBC** - Custom encryption with username as plaintext
3. **Legacy Salted SHA-256** - Salted SHA-256 hashes for older accounts

##### Custom AES Implementation Details (CenAccount Model)
```
Key Features:
- AES-256 with custom S-Box
- 5 encryption rounds by default
- SHA-1 integrity check
- Base64 encoding for storage
- Nonce-based CBC mode (simplified)
```

##### Password Hash Process (Custom AES):
1. Plaintext = username
2. Append SHA-1 hash of plaintext: `sha1(plain):plain`
3. Encrypt using AES with password as key
4. Base64 encode the result

##### Password Verification:
- Tries multiple username variants (original, lowercase, uppercase, trimmed)
- Tries multiple round counts
- Falls back to legacy salted SHA-256

### Session Management
- Session-based authentication (Laravel's default session driver)
- Session regenerated on login
- Session invalidated on logout
- CSRF tokens enabled

### Login Flow (AuthController)
1. Validate username and password
2. Attempt authentication via `Auth::attempt()`
3. Regenerate session
4. Store user details in session
5. Redirect based on user's unit area

---

## Application Architecture

### Directory Structure
```
RDWIS APP 2.0/
├── app/
│   ├── Auth/
│   │   └── CenAccountUserProvider.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── MprController.php
│   │   │   └── ...
│   │   └── Middleware/
│   │       ├── CheckArea.php
│   │       └── RequireApprover.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── CenAccount.php
│   │   ├── Project.php
│   │   ├── Purchase.php
│   │   └── ...
│   └── Services/
│       ├── PurchaseApprovalService.php
│       └── FinancialIntelligenceService.php
├── bootstrap/
├── config/
├── database/
├── exported_vba/          # Original Access VBA code
│   ├── Accounting.bas
│   ├── Encryption.bas
│   └── ...
├── public/
├── resources/
├── routes/
├── storage/
└── tests/
```

### Key Models
- **`App\Models\User`** - User model (aliases CenAccount)
- **`App\Models\CenAccount`** - Primary user model with encryption logic
- **`App\Models\Project`** - Projects
- **`App\Models\Purchase`** - Purchases
- **`App\Models\Role`** - Roles
- **`App\Models\Unit`** - Units

### Services
- **`PurchaseApprovalService`** - Handles purchase workflow approval
- **`FinancialIntelligenceService`** - Financial analysis

---

## Key Features

### User Authorization & Access Control

#### Area Access
Users are assigned to areas (unit areas):
- `hr` - Human Resources
- `fin` - Finance
- `prj` - Projects
- `nrdi` - NRDI
- `rdw` - RDW
- `hqs` - Headquarters
- `proc` - Procurement
- `it` - IT
- `rdwprj` / `prjrdw` - Combined RDW + Projects (SORD)

#### Authorization Levels (`acc_auth`)
- `approver` - Can approve items
- `editor` - Can edit items
- `viewer` - Can only view items

#### Access Types (`acc_access`)
- `single` - Single unit access (range via `acc_lowers` / `acc_uppers`)
- `multiple` - Multi-unit access (range via `acc_lowerm` / `acc_upperm`)

#### User Helper Methods (CenAccount/User models)
- `isSORD()` - Checks if user is RDWPRJ/PRJRDW
- `isDivision()` - Checks if user is in PRJ area
- `isApprover()` - Checks if user is approver/editor
- `isViewer()` - Checks if user is viewer
- `canAccessArea($area)` - Checks area access
- `canSeeRecord($unitId)` - Checks record access
- `isSingleUnitAccess()` / `isMultiUnitAccess()`
- `unitRange()` - Returns unit access range
- `moduleRange()` - Returns module access range

### Procurement Workflow
Complete purchase management with:
- Draft cases visible to DProc early
- Quote management
- Approval workflow with sequential remarks numbering
- Predefined quick comments
- Action buttons renamed to "RECOMMEND" for intermediate authorities

### Audit Trail
Complete audit logging in `aud.busdata` table:
- Username
- Timestamp
- Action
- Form/control/field
- Old/new values
- Employee name/designation

---

## Security Measures

### Current Security Features

1. **Authentication**
   - Multiple password scheme support (bcrypt/argon2 preferred)
   - Session regeneration on login
   - Session invalidation on logout
   - Account status check (only Active accounts can login)
   - Password change enforcement (prevents default password `12345`)
   - Minimum password length (5 characters)

2. **Authorization**
   - Role-based access control (approver, editor, viewer)
   - Area-based access control
   - Unit/module range-based record access
   - Middleware: `CheckArea`, `RequireApprover`

3. **Audit & Accountability**
   - Complete audit trail of all data changes
   - Revision history tracking
   - Forced comments in procurement workflow
   - Disabled action buttons until comments are entered

4. **Password Security**
   - Support for modern bcrypt/argon2 hashes
   - Custom AES encryption (backward compatibility)
   - Legacy salted SHA-256 support
   - Password hidden in model (not exposed in JSON/arrays)
   - Default password change required

### Areas for Potential Improvement
1. Consider migrating all users to bcrypt/argon2 for better security
2. Implement password policies (complexity, expiration)
3. Add account lockout after failed attempts
4. Add HTTPS enforcement
5. Implement rate limiting on login
6. Add two-factor authentication
7. Regular security patches and dependency updates

---

## Quick Reference

### Environment Variables (.env)
```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=acessdev
DB_USERNAME=postgres
DB_PASSWORD=12345
DEFAULT_PASSWORD=12345
```

### Default Password
- Default: `12345`
- Users must change this on first login (enforced by password validation)

### Database Backup Files
- `acessdev.sql` - Main database dump
- `others/DB RDWIS/dump-Newdev.sql`
- `others/DB RDWIS/ERD/` - ER diagrams

### Original VBA Code
All original Microsoft Access VBA code is in `exported_vba/` directory, including:
- `Encryption.bas` - Original encryption logic
- `Accounting.bas`
- `Startup.bas`
- And many more modules and forms

---

*This document was generated to provide an overview of the existing RDWIS application, its database structure, and security measures.*
