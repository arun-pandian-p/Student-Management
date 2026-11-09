# Student Management System

A full-featured web-based Student Management System built using **PHP 8+, MySQL, PDO, Bootstrap 5, HTML, CSS and JavaScript**. Designed to simplify student record management through a responsive admin dashboard, secure authentication and complete CRUD operations.

## Application Preview

<table>
  <tr>
    <td width="33%" align="center">
      <img src="https://github.com/user-attachments/assets/01054b84-7a02-4b73-89b6-08c2a7a0b67c" width="100%" alt="Student Management System Preview 1"/>
      <sub><b>Application Preview 01</b></sub>
    </td>
    <td width="33%" align="center">
      <img src="https://github.com/user-attachments/assets/46232b6d-4b36-486d-93f8-9a59c882f5d0" width="100%" alt="Student Management System Preview 2"/>
      <sub><b>Application Preview 02</b></sub>
    </td>
    <td width="33%" align="center">
      <img src="https://github.com/user-attachments/assets/3fdc1d27-798a-4e9b-b5e3-b5ce537faece" width="100%" alt="Student Management System Preview 3"/>
      <sub><b>Application Preview 03</b></sub>
    </td>
  </tr>
</table>

## Tech Stack

| Technology | Purpose |
|---|---|
| PHP 8+ | Backend development and application logic |
| MySQL | Relational database management |
| PDO | Database connectivity and prepared statements |
| HTML5 & CSS3 | Webpage structure and styling |
| Bootstrap 5 | Responsive user interface |
| JavaScript | Interactive elements and client-side functionality |
| XAMPP | Local development environment |

## Features

### 1. Authentication & Admin Management
- Admin login with password hashing.
- Session-based authentication and protected admin pages.
- CSRF validation for protected form submissions.
- Admin profile management.
- Profile information and password updates.
- Secure logout and session invalidation.

### 2. Admin Dashboard
- Overview of total, active and inactive students.
- Department-wise student statistics.
- Recent student registrations.
- Quick access to student management features.
- System information including PHP and database status.

### 3. Student Management (CRUD)

**Create Students**
- Register new students with personal and academic information.
- Automatically generated student IDs.
- Fields include name, email, phone, department, date of birth, gender, address, enrollment date and status.
- Client-side and server-side form validation.
- Unique email validation.

**View Students**
- Responsive student records table.
- Search by student name, email, ID or phone number.
- Department and enrollment-status filters.
- Pagination for student records.
- Quick student profile view.

**Update Students**
- Edit existing student information.
- Pre-filled form fields.
- Student ID protection.
- Database-backed email uniqueness validation.

**Delete Students**
- Confirmation dialog before deletion.
- CSRF-protected POST requests.
- Success and error notifications.

### 4. QR Code & Digital Student ID
- Generate QR codes for student verification.
- Create shareable student verification links.
- Copy verification links to the clipboard.
- Download QR codes as PNG images.
- Interactive 3D student ID card.
- Public student verification page.
- Display student details and enrollment status through QR verification.

### 5. Security & Backend
- PDO database connectivity.
- Prepared SQL statements to help prevent SQL injection.
- Password hashing and verification.
- CSRF token generation and validation.
- Output escaping to reduce XSS risks.
- Server-side input validation.
- PHP session management.
- Reusable helper functions and flash messages.

## Project Structure

```text
student-management/
│
├── admin/
│   └── profile.php
│
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── main.js
│   └── uploads/
│       └── students/
│
├── config/
│   ├── database.php
│   └── database.example.php
│
├── database/
│   └── schema.sql
│
├── includes/
│   ├── header.php
│   ├── sidebar.php
│   ├── footer.php
│   └── functions.php
│
├── students/
│   ├── index.php
│   ├── create.php
│   ├── store.php
│   ├── edit.php
│   ├── update.php
│   ├── delete.php
│   ├── qr-generator.php
│   └── verify.php
│
├── index.php
├── login.php
├── logout.php
├── .gitignore
└── README.md
```

## Installation & Setup

### Requirements
- XAMPP
- PHP 8.0 or later
- MySQL
- Modern web browser

### Step 1: Clone the Repository

```bash
git clone https://github.com/arun-pandian-p/Student-Management.git
```

Move the project folder into your XAMPP `htdocs` directory:

```text
C:\xampp\htdocs\student-management
```

### Step 2: Start XAMPP

1. Open XAMPP Control Panel.
2. Start Apache.
3. Start MySQL.

### Step 3: Create the Database

1. Open phpMyAdmin:

   http://localhost/phpmyadmin/

2. Create a database named:

```sql
student_management
```

3. Select the database.
4. Open the Import tab.
5. Import the project's `database/schema.sql` file.

### Step 4: Configure Database Connection

Copy the example configuration:

```powershell
Copy-Item config/database.example.php config/database.php
```

Update the database settings in `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'student_management');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
```

Use the credentials configured in your local MySQL installation.

### Step 5: Run the Application

Open your browser and visit:

http://localhost/student-management/

Log in using an administrator account configured in your local database.

## Testing Guide

Use the following checklist to test the application:

| Test | Expected Result |
|---|---|
| Admin Login | Authentication and dashboard access |
| Dashboard | Student statistics displayed |
| Add Student | New student record created |
| View Students | Student records displayed |
| Search | Matching student records found |
| Filters | Records filtered by department and status |
| Edit Student | Student information updated |
| Delete Student | Selected record removed after confirmation |
| QR Generator | Student QR code generated |
| Student Verification | Student information displayed through verification link |
| Admin Profile | Profile details updated |

## Learning Objectives

This project helped me practise:

- PHP backend development and server-side programming.
- MySQL database design and SQL queries.
- CRUD operations and form handling.
- Frontend and backend integration.
- Authentication and session management.
- Database connectivity using PDO.
- Building responsive web applications.
- Debugging and troubleshooting application issues.

## Author

**Arun Pandian**

B.Tech – Artificial Intelligence & Data Science (2026)

- GitHub: https://github.com/arun-pandian-p
- LinkedIn: https://linkedin.com/in/arunpandian-developer
- Portfolio: https://arunpandian.online

---

**Student Management System** – Built to practise PHP, MySQL and practical web application development.
