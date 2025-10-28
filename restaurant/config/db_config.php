<?php
// ========================================
// Database Configuration
// ระบบจัดการร้านอาหารญี่ปุ่น
// ========================================

// ข้อมูลเชื่อมต่อฐานข้อมูล
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'japanese_restaurant');
define('DB_CHARSET', 'utf8mb4');

// สร้างการเชื่อมต่อ
try {
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// ฟังก์ชันช่วยเหลือ
function executeQuery($conn, $sql, $params = []) {
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch(PDOException $e) {
        error_log("Query Error: " . $e->getMessage());
        return false;
    }
}

function getAll($conn, $sql, $params = []) {
    $stmt = executeQuery($conn, $sql, $params);
    return $stmt ? $stmt->fetchAll() : [];
}

function getOne($conn, $sql, $params = []) {
    $stmt = executeQuery($conn, $sql, $params);
    return $stmt ? $stmt->fetch() : null;
}

function insert($conn, $table, $data) {
    $columns = implode(', ', array_keys($data));
    $placeholders = ':' . implode(', :', array_keys($data));
    $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
    
    $stmt = executeQuery($conn, $sql, $data);
    return $stmt ? $conn->lastInsertId() : false;
}

function update($conn, $table, $data, $where, $whereParams) {
    $set = [];
    foreach($data as $key => $value) {
        $set[] = "$key = :$key";
    }
    $setString = implode(', ', $set);
    
    $sql = "UPDATE $table SET $setString WHERE $where";
    $params = array_merge($data, $whereParams);
    
    return executeQuery($conn, $sql, $params) !== false;
}

function delete($conn, $table, $where, $params) {
    $sql = "DELETE FROM $table WHERE $where";
    return executeQuery($conn, $sql, $params) !== false;
}

// ฟังก์ชันสำหรับ JSON Response
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// ตั้งค่า Timezone
date_default_timezone_set('Asia/Bangkok');

// Session Start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>