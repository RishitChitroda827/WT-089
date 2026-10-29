<?php
/**
 * StudentHub - Universal Database Connection
 * Compatible with local MySQL (password: 123456), standard XAMPP (password: empty),
 * and Cloud / Vercel environment variables (DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT).
 */

mysqli_report(MYSQLI_REPORT_OFF);

$db_host = getenv("DB_HOST") ?: "127.0.0.1";
$db_user = getenv("DB_USER") ?: "root";
$db_name = getenv("DB_NAME") ?: "studenthub";
$db_port = (int)(getenv("DB_PORT") ?: 3306);
$env_pass = getenv("DB_PASSWORD");

// Candidates for root passwords: user's local setup first, then blank default
$password_candidates = ($env_pass !== false && $env_pass !== null)
    ? [$env_pass]
    : ["123456", "", "root", "admin"];

$conn = null;
$last_error = "";
$connected_password = "";

foreach ($password_candidates as $pwd) {
    try {
        $testConn = @new mysqli($db_host, $db_user, $pwd, $db_name, $db_port);
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
            $serverConn = @new mysqli($db_host, $db_user, $pwd, "", $db_port);
            if (!$serverConn->connect_error) {
                $serverConn->query("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $serverConn->close();

                $conn = @new mysqli($db_host, $db_user, $pwd, $db_name, $db_port);
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

if ($conn && !$conn->connect_error) {
    $conn->set_charset("utf8mb4");

    // PDO connection for modules using PDO (Practical 8, 13)
    $pdo = null;
    try {
        $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4";
        $pdo = new PDO($dsn, $db_user, $connected_password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $e) {
        // PDO optional fallback; MySQLi remains primary
    }
} else {
    // If not connected, keep $conn = null and record error without crashing static content
    $db_connection_error = $conn ? $conn->connect_error : $last_error;
    $conn = null;
    $pdo = null;
}
