<?php
/**
 * StudentHub - Universal Database Connection
 * Compatible with local MySQL (password: 123456) and standard XAMPP (password: empty)
 */

mysqli_report(MYSQLI_REPORT_OFF);

$db_host = "127.0.0.1";
$db_user = "root";
$db_name = "studenthub";

// Candidates for root passwords: user's local setup first, then blank default
$password_candidates = ["123456", "", "root", "admin"];

$conn = null;
$last_error = "";
$connected_password = "";

foreach ($password_candidates as $pwd) {
    try {
        $testConn = @new mysqli($db_host, $db_user, $pwd, $db_name);
        if (!$testConn->connect_error) {
            $conn = $testConn;
            $connected_password = $pwd;
            break;
        }
    } catch (Throwable $e) {
        $last_error = $e->getMessage();
    }
}

// If database does not exist yet, connect to MySQL server and create it
if (!$conn) {
    foreach ($password_candidates as $pwd) {
        try {
            $serverConn = @new mysqli($db_host, $db_user, $pwd);
            if (!$serverConn->connect_error) {
                $serverConn->query("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $serverConn->close();

                $conn = @new mysqli($db_host, $db_user, $pwd, $db_name);
                if (!$conn->connect_error) {
                    $connected_password = $pwd;
                    break;
                }
            }
        } catch (Throwable $e) {
            $last_error = $e->getMessage();
        }
    }
}

if (!$conn || $conn->connect_error) {
    $err = $conn ? $conn->connect_error : $last_error;
    die("Database Connection Error: Could not connect to MySQL. Please verify MySQL service is running.<br>Details: " . htmlspecialchars($err));
}

$conn->set_charset("utf8mb4");

// PDO connection for modules using PDO (Practical 8, 13)
$pdo = null;
try {
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
    $pdo = new PDO($dsn, $db_user, $connected_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    // PDO optional fallback; MySQLi remains primary
}
