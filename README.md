<<<<<<< HEAD
# Student-Management
A Student Management System built using PHP, MySQL, HTML, CSS, and JavaScript to manage student information through a simple web interface.
=======
# Student Management System (PHP 8+ & MySQL CRUD)

A professional, full-featured **Student Management System** built with native **PHP 8+**, **MySQL**, **PDO**, **Bootstrap 5**, and clean vanilla JavaScript. Designed specifically for local development with **XAMPP**.

---

## 🚀 Key Features

### 1. Authentication & Admin Security (New)
- **Login Screen (`login.php`)**: Secure authentication system with Bcrypt hashed passwords and CSRF validation.
- **Admin Credentials**:
  - **Email**: `arun@gmail.com`
  - **Password**: `arun4709s`
  - Features 1-click **"Autofill"** and password visibility reveal toggles.
- **Admin Profile Management (`admin/profile.php`)**: View administrator profile, update display name, email, and change passwords.
- **Secure Logout (`logout.php`)**: Full session invalidation and flash confirmation.
- **Auth Guard**: Protects all administrative CRUD routes while keeping `students/verify.php` publicly scannable.

### 2. Dashboard Overview
- **Real-Time Counters**: Total students, Active students count, and Inactive students count.
- **Department Breakdown**: Visual distribution and percentage progress indicators across faculties.
- **Recent Registrations**: Table showing the latest enrolled students with quick actions (View, Edit, Delete).
- **System Information Widget**: Displays PHP version, database engine, and server status.

### 2. Complete CRUD Student Management
- **Create Student**:
  - Auto-generated sequential Student ID format: `STU-YYYY-XXX` (e.g., `STU-2024-001`, `STU-2026-001`).
  - Fields: Full Name, Email (unique), Phone Number, Department, Date of Birth, Gender, Address, Enrollment Date, Status (Active / Inactive).
  - Dual validation: Client-side HTML5 validation and robust server-side PHP validation.
- **QR & AR Digital ID Generator (New)**:
  - **Dynamic Scannable QR Codes**: Instant client-side QR generation containing direct official verification URLs.
  - **1-Click Copy Link**: Shareable credential link with instant clipboard copy.
  - **Download QR Code**: Export QR codes as high-resolution PNG images.
  - **Interactive 3D / AR Digital Student Badge**: Tap or tiltable 3D holographic student ID card showing front profile and back scannable QR badge.
  - **Official Verification Portal (`verify.php`)**: Publicly scannable verification page authenticating active student status, credentials, and printable ID badge.
- **Read & Explore Students**:
  - Responsive, beautifully styled data table.
  - **Live Search**: Instant multi-field search across student name, email, student ID, and phone number.
  - **Filters**: Filter by Department and Enrollment Status (Active / Inactive).
  - **Pagination**: Configurable page limit with query parameter preservation across page navigation.
  - **Quick Details Modal**: View comprehensive student profiles without leaving the list.
  - **Empty States**: Helpful empty state illustrations and reset buttons when no records match.
- **Update Student**:
  - Edit existing student information with pre-populated form fields.
  - Student ID is locked to maintain data integrity.
  - Database-backed email uniqueness validation that excludes the student's current record.
  - Preserves input values if validation fails.
- **Delete Student**:
  - Safe, CSRF-protected deletion via `POST` (prevents accidental URL click deletions).
  - Interactive Bootstrap confirmation modal showing the student's name and ID before deletion.

### 3. Architecture & Security Standards
- **PDO Database Connectivity**: Prepared statements throughout to guarantee protection against **SQL Injection**.
- **Cross-Site Request Forgery (CSRF)**: Cryptographically secure CSRF tokens embedded in all forms and validated on submission.
- **Cross-Site Scripting (XSS)**: All outputs safely escaped using a custom `e()` helper (`htmlspecialchars` with `ENT_QUOTES`).
- **Input Sanitization**: Strip slashes and trim inputs automatically.
- **Flash Messages**: Success, warning, and danger notifications stored in PHP sessions with auto-dismissing Bootstrap alerts.
- **Zero Framework Bloat**: Pure, high-performance PHP 8+ without Laravel, Composer dependencies, or Node.js build steps.

---

## 📁 Project Structure

```text
student-management/
├── config/
│   └── database.php         # PDO connection & dynamic BASE_URL configuration
├── includes/
│   ├── header.php           # Global HTML head, navbar, and flash message renderer
│   ├── sidebar.php          # Responsive sidebar navigation
│   ├── footer.php           # Global footer, delete modal, profile modal, and scripts
│   └── functions.php        # Reusable helpers (CSRF, XSS, validation, flash alerts)
├── students/
│   ├── index.php            # Read: table, search, filter, pagination
│   ├── create.php           # Create: new student registration form
│   ├── store.php            # Create: POST processing & DB insert
│   ├── edit.php             # Update: edit student details form
│   ├── update.php           # Update: POST processing & DB update
│   └── delete.php           # Delete: CSRF-protected POST record deletion
├── assets/
│   ├── css/
│   │   └── style.css        # Modern admin dashboard stylesheet
│   └── js/
│       └── main.js          # Modal triggers, tooltips, validation, auto-dismiss
├── database/
│   └── schema.sql           # Database schema, table indexes, and sample records
└── index.php                # Dashboard homepage with metrics and recent students
```

---

## 🛠️ Step-by-Step Setup Guide (XAMPP)

### Step 1: Place the Project in XAMPP `htdocs`
Copy the `student-management` folder into your XAMPP web root directory:
```text
C:\xampp\htdocs\student-management\
```

### Step 2: Start Apache and MySQL
1. Launch the **XAMPP Control Panel**.
2. Click **Start** next to **Apache**.
3. Click **Start** next to **MySQL**.
*(Both modules should turn green).*

### Step 3: Create Database & Import Schema
#### Method A: Using phpMyAdmin (Recommended)
1. Open your web browser and navigate to: [http://localhost/phpmyadmin/](http://localhost/phpmyadmin/)
2. Click on **New** (or **Databases**) in the left sidebar.
3. In the "Database name" input, enter:
   ```text
   student_management
   ```
4. Click **Create**.
5. With `student_management` selected, click on the **Import** tab at the top.
6. Click **Choose File** and select:
   ```text
   C:\xampp\htdocs\student-management\database\schema.sql
   ```
7. Click the **Import** button at the bottom.

#### Method B: Using MySQL Command Line
Run this command in Windows PowerShell or Command Prompt:
```cmd
C:\xampp\mysql\bin\mysql.exe -u root < C:\xampp\htdocs\student-management\database\schema.sql
```

### Step 4: Open the Application
Open your browser and visit:
👉 **[http://localhost/student-management/](http://localhost/student-management/)**

---

## ⚙️ Database Configuration

If your MySQL setup uses a custom port or a root password, modify `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'student_management');
define('DB_USER', 'root');
define('DB_PASS', '');          // Set your MySQL password here if applicable
define('DB_CHARSET', 'utf8mb4');
```

---

## 🧪 Testing CRUD Functionality

1. **Test Dashboard**:
   - Verify metrics for Total Students, Active Students, and Inactive Students match the database.
2. **Test Search & Filters**:
   - Go to **All Students** (`/students/index.php`).
   - Search for a student by name (e.g. `Sophia` or `Alexander`).
   - Filter by department (e.g. `Computer Science`) and status (`Active`).
   - Click the page numbers to verify pagination retains filter criteria.
3. **Test Create (Add Student)**:
   - Click **Add Student** (`/students/create.php`).
   - Attempt to submit an empty form to observe both client-side and server-side validation.
   - Enter duplicate email to verify database uniqueness error feedback.
   - Complete valid inputs and submit; check the green success alert on the student list.
4. **Test Quick Profile View**:
   - Click the eye icon (`View`) next to any student to open the instant profile modal.
5. **Test Update (Edit Student)**:
   - Click the pencil icon (`Edit`) next to any student.
   - Modify fields (e.g., change department or phone number) and save.
   - Verify the update is reflected in the table and database.
6. **Test Delete**:
   - Click the trash icon (`Delete`).
   - Confirm that the deletion modal shows the student's name and ID.
   - Click **Yes, Delete** to execute the deletion and observe the flash confirmation.

>>>>>>> master
