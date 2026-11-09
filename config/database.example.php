<?php
/**
 * Database Configuration & Connection
 * Student Management System
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Credentials (Default XAMPP settings)
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'student_management');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Dynamically resolve the application BASE_URL
 * Works across root, subdirectories, or local development ports
 */
if (!defined('BASE_URL')) {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    
    // Find directory containing 'student-management'
    $pos = strpos($scriptDir, 'student-management');
    if ($pos !== false) {
        $base = substr($scriptDir, 0, $pos + strlen('student-management'));
    } else {
        $base = rtrim($scriptDir, '/');
    }
    define('BASE_URL', rtrim($base, '/'));
}

/**
 * Establishes and returns a PDO database connection
 * 
 * @return PDO
 */
function get_db_connection(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Check if database doesn't exist or MySQL is down
            $errorMsg = $e->getMessage();
            $isDbMissing = str_contains($errorMsg, 'Unknown database');
            
            // Render user-friendly diagnostic error page
            http_response_code(500);
            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Database Connection Error</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
                <style>
                    body { background-color: #f4f6f9; font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; }
                    .error-card { max-width: 650px; margin: 80px auto; border: none; border-radius: 14px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
                    .code-block { background: #212529; color: #a5d6a7; padding: 15px; border-radius: 8px; font-family: monospace; font-size: 0.9rem; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="card error-card">
                        <div class="card-body p-4 p-md-5">
                            <div class="text-center mb-4">
                                <div class="badge bg-danger-subtle text-danger p-3 rounded-circle mb-3 fs-3">
                                    <i class="bi bi-database-fill-x"></i>
                                </div>
                                <h3 class="fw-bold text-dark">Database Connection Failed</h3>
                                <p class="text-muted">Could not connect to the MySQL database <code><?= htmlspecialchars(DB_NAME) ?></code>.</p>
                            </div>

                            <div class="alert alert-danger d-flex align-items-center" role="alert">
                                <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                                <div><strong>PDO Error:</strong> <?= htmlspecialchars($errorMsg) ?></div>
                            </div>

                            <h6 class="fw-bold mt-4 mb-2"><i class="bi bi-tools me-2 text-primary"></i>How to resolve this in XAMPP:</h6>
                            <ol class="small text-secondary ps-3 mb-4">
                                <li class="mb-2">Open the <strong>XAMPP Control Panel</strong> and ensure both <strong>Apache</strong> and <strong>MySQL</strong> services are running (green status).</li>
                                <li class="mb-2">Open <strong>phpMyAdmin</strong> by visiting <a href="http://localhost/phpmyadmin/" target="_blank" class="fw-semibold">http://localhost/phpmyadmin/</a>.</li>
                                <li class="mb-2">Create the database: click <strong>Databases</strong>, enter <code>student_management</code>, and click <strong>Create</strong>.</li>
                                <li class="mb-2">Click on the newly created database, open the <strong>Import</strong> tab, and import the SQL file located at:<br>
                                    <div class="code-block my-2">database/schema.sql</div>
                                </li>
                                <li>Refresh this page once imported.</li>
                            </ol>

                            <div class="d-grid gap-2">
                                <button onclick="window.location.reload();" class="btn btn-primary py-2 fw-medium">
                                    <i class="bi bi-arrow-clockwise me-1"></i> Retry Connection
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </body>
            </html>
            <?php
            exit;
        }
    }

    return $pdo;
}

