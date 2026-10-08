# Complete Student Management System

A full-stack web application built with **PHP 8+**, **MySQL**, **HTML5**, **CSS3**, **Bootstrap 5**, and **JavaScript**.

---

## 🌟 Features Overview

- **Unified Authentication & Authorization**:
  - Single login page (`login.php`) for both Students and Administrators.
  - Role-based session management (`admin` vs `student`).
  - Automatic redirection to `Admin Dashboard` or `Student Dashboard`.
- **Student Registration (`signup.php`)**:
  - Captures Personal details, Education info, Multiple Interests, and Aadhaar PDF.
  - **Duplicate Email Validation**: Stopping registration and displaying exact message: `"This email is already registered."`
  - **Strict Server-Side PDF Upload Security**: Accepts PDF files (`application/pdf`) up to 5MB, rejects non-PDF extensions/MIME types, and generates unique server filenames (`student_TIMESTAMP_HASH.pdf`).
- **Forgot Password & Reset Flow**:
  - Generates secure 64-character reset tokens expiring in 30 minutes.
  - Development fallback provided for local testing without SMTP configuration.
- **Admin Dashboard (`admin/dashboard.php`)**:
  - Displays registered students in a responsive Bootstrap table.
  - **Instant Real-Time Search & Filtering**: Multi-attribute filtering by Name (as you type), Class selection, Minimum Age, and Maximum Age.
  - **Aadhaar Document Viewer**: Clickable `"View PDF"` link opening PDF safely in a new browser tab.
  - **Edit & Delete Student Records**: Full CRUD handling with safe POST/DELETE confirmation dialogs.
  - **Field Safeguards**: Immutability enforced on student **Name** and **Email** both on UI and strictly on PHP backend.
- **Student Dashboard (`student/dashboard.php`)**:
  - Features exact welcome banner format: `Welcome, [User Name] (User ID: #[ID])`.
  - Displays complete profile overview, calculated age, academic details, and Aadhaar document view link.
  - Profile Editing with locked **Email** field and PDF replacement support.
- **Security Protections**:
  - PDO Prepared Statements against SQL Injection.
  - Password hashing using PHP `password_hash()` and verification with `password_verify()`.
  - CSRF Token generation & validation on all POST forms.
  - XSS output escaping using `htmlspecialchars()`.
  - Directory protection (`.htaccess`) in upload folders preventing script execution.

---

## 📁 Application Architecture & File Structure

```text
student-management-system/
│
├── index.php                 # Home / Landing Page
├── login.php                 # Unified Login Page (Admin & Student)
├── signup.php                # Student Registration Page with PDF Upload
├── forgot-password.php       # Password Reset Request Page
├── reset-password.php        # Password Reset Form Handler
├── logout.php                # Session Cleanup & Logout Handler
├── README.md                 # Setup & Testing Documentation
│
├── admin/
│   ├── dashboard.php         # Admin Directory & Instant Filter Dashboard
│   ├── edit-student.php      # Admin Student Edit Form (Name & Email Locked)
│   └── delete-student.php    # Admin Delete Student Handler (POST)
│
├── student/
│   ├── dashboard.php         # Student Profile Dashboard & Welcome Banner
│   └── edit-profile.php      # Student Edit Profile Page (Email Locked)
│
├── config/
│   └── database.php          # Centralized PDO DB Config & Helpers
│
├── includes/
│   ├── auth.php              # Base Authentication & CSRF Helpers
│   ├── admin-auth.php        # Admin Role Guard
│   ├── student-auth.php      # Student Role Guard
│   ├── header.php            # Shared Layout Header & Navbar
│   └── footer.php            # Shared Layout Footer & Scripts
│
├── uploads/
│   └── aadhaar/              # Secure PDF Storage (.htaccess Protected)
│
├── assets/
│   ├── css/
│   │   └── style.css         # Modern Styling & Custom Design Tokens
│   └── js/
│       └── script.js         # Real-time Filter, PDF check & UI toggles
│
└── database/
    └── database.sql          # Complete MySQL Database Schema & Seed Data
```

---

## ⚙️ Installation & Setup Guide

### 1. Prerequisites (XAMPP / WAMP / Apache + MySQL + PHP)
- Download and install [XAMPP](https://www.apachefriends.org/) or [WAMP](https://www.wampserver.com/en/).
- Ensure **PHP 8.0+** and **MySQL / MariaDB** are installed.

### 2. Copy Project Files
Place this repository directory in your web server root directory:
- For XAMPP: `C:\xampp\htdocs\student-management-system`
- For WAMP: `C:\wamp64\www\student-management-system`

### 3. Database Setup (Importing `database.sql`)
1. Start **Apache** and **MySQL** services in the XAMPP / WAMP Control Panel.
2. Open **phpMyAdmin** in your browser: `http://localhost/phpmyadmin`
3. Click on **Databases** tab and create a database named:
   ```text
   student_management
   ```
4. Select `student_management` database and click **Import**.
5. Choose the file `database/database.sql` from the project directory and click **Import / Go**.

*(Note: The `config/database.php` script also includes an automated fallback that automatically creates the `student_management` database, table schema, and seed admin user on first connection if missing).*

### 4. Database Configuration
Open `config/database.php` to adjust MySQL credentials if your local setup differs from standard defaults:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'student_management');
define('DB_USER', 'root');
define('DB_PASS', ''); // Add your password if configured
```

---

## 🔐 Credentials & Default Accounts

### Predefined Administrator Account
- **Email:** `admin@example.com`
- **Password:** `Admin@123`

### Pre-populated Demo Student Account
- **Email:** `student1@example.com`
- **Password:** `Student@123`

---

## 🧪 Verification & Testing Workflow

Follow these steps to test all required capabilities:

### Test 1 — Student Registration (`signup.php`)
1. Open `http://localhost/student-management-system/signup.php`.
2. Enter details:
   - **Name:** Test Student
   - **Email:** `student2@example.com`
   - **Password:** `Student@123`
   - **DOB:** `2004-05-20`
   - **Gender:** Male / Female
   - **Qualification:** Intermediate
   - **Class:** 12th
   - **Subject:** Physics
   - **Marks:** 89.5
   - **Interests:** Select Coding, Sports, Reading
   - **Upload Aadhaar:** Select a valid PDF file.
3. Submit form. Verify success alert: `"Registration successful. You can now login."`

### Test 2 — Duplicate Email Block
1. Return to `signup.php`.
2. Try registering again with `student2@example.com`.
3. Submit form. Verify exact error message:
   ```text
   This email is already registered.
   ```

### Test 3 — Duplicate File Upload Isolation
1. Register two different students and upload PDF files named `aadhaar.pdf` for both.
2. Inspect `uploads/aadhaar/`. Verify both files exist separately with unique generated filenames like `student_1728384930_a1b2c3d4.pdf`.

### Test 4 — Unified Student Login
1. Navigate to `login.php`.
2. Enter `student2@example.com` and `Student@123`.
3. Verify redirection to **Student Dashboard** (`student/dashboard.php`).
4. Verify exact welcome banner:
   ```text
   Welcome, Test Student (User ID: #2)
   ```

### Test 5 — Unified Admin Login
1. Click Logout, then open `login.php`.
2. Enter `admin@example.com` and `Admin@123`.
3. Verify redirection to **Admin Dashboard** (`admin/dashboard.php`).

### Test 6 — Admin Table Search & Multi-Filtering
1. On `admin/dashboard.php`, type student name in **Search by Name**.
2. Select **Class** from dropdown.
3. Input **Minimum Age** and **Maximum Age**.
4. Observe the table filtering records instantly.
5. Click **"View PDF"** in Aadhaar column to confirm opening the uploaded PDF.

### Test 7 — Admin Edit (Locked Name & Email)
1. Click **Edit** on a student row.
2. Note that **Name** and **Email** fields are locked/disabled.
3. Update Date of Birth or Marks and save.
4. Verify alert: `"Profile updated successfully."`

### Test 8 — Student Profile Edit (Locked Email)
1. Login as a student and click **Edit Profile**.
2. Verify **Email** is read-only.
3. Upload a new Aadhaar PDF file to test replacing document storage.
4. Verify old file removal and database update.

### Test 9 — Safe Deletion
1. In Admin Dashboard, click **Delete** for a test student.
2. Confirm popup dialog: `"Are you sure you want to delete this student?"`
3. Verify record and Aadhaar file are removed from server.

### Test 10 — Authorization Security
1. Logged in as student, manually try navigating to `http://localhost/student-management-system/admin/dashboard.php`.
2. Verify access is blocked and redirected to Student Dashboard with access denied alert.
