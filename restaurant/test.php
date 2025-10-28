<?php
// ไฟล์ทดสอบระบบ
// วางไฟล์นี้ไว้ที่ C:\xampp\htdocs\restaurant\test.php
// เปิดที่: http://localhost/restaurant/test.php

echo "<h1>🍱 ทดสอบระบบร้านอาหารญี่ปุ่น</h1>";
echo "<hr>";

// 1. ทดสอบ PHP
echo "<h2>1. ✅ PHP ทำงานได้</h2>";
echo "เวอร์ชัน PHP: " . phpversion() . "<br>";
echo "เวลาปัจจุบัน: " . date('Y-m-d H:i:s') . "<br><br>";

// 2. ทดสอบโครงสร้างไฟล์
echo "<h2>2. ตรวจสอบโครงสร้างไฟล์</h2>";

$folders = [
    'config',
    'css',
    'js',
    'images',
    'images/menu',
    'api',
    'customer',
    'kitchen',
    'staff',
    'admin'
];

foreach ($folders as $folder) {
    if (is_dir($folder)) {
        echo "✅ โฟลเดอร์ <strong>$folder</strong> มีอยู่<br>";
    } else {
        echo "❌ โฟลเดอร์ <strong>$folder</strong> ไม่พบ - <span style='color:red;'>กรุณาสร้าง!</span><br>";
    }
}

echo "<br>";

// 3. ทดสอบไฟล์สำคัญ
echo "<h2>3. ตรวจสอบไฟล์สำคัญ</h2>";

$files = [
    'index.php',
    'logout.php',
    'config/db_config.php',
    'css/main.css',
    'js/main.js',
    'api/orders.php',
    'customer/menu.php',
    'kitchen/dashboard.php',
    'staff/dashboard.php',
    'admin/dashboard.php',
    'admin/menu-management.php',
    'admin/stock-management.php',
    'admin/reports.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ ไฟล์ <strong>$file</strong> มีอยู่<br>";
    } else {
        echo "❌ ไฟล์ <strong>$file</strong> ไม่พบ - <span style='color:red;'>กรุณาสร้าง!</span><br>";
    }
}

echo "<br>";

// 4. ทดสอบการเชื่อมต่อ Database
echo "<h2>4. ทดสอบการเชื่อมต่อฐานข้อมูล</h2>";

try {
    $conn = new PDO(
        "mysql:host=localhost;dbname=japanese_restaurant;charset=utf8mb4",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "✅ <span style='color:green;'>เชื่อมต่อฐานข้อมูลสำเร็จ!</span><br>";
    
    // ตรวจสอบตาราง
    echo "<br><strong>ตารางในฐานข้อมูล:</strong><br>";
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) > 0) {
        foreach ($tables as $table) {
            $count = $conn->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
            echo "✅ ตาราง <strong>$table</strong> มี $count แถว<br>";
        }
    } else {
        echo "❌ <span style='color:red;'>ไม่พบตารางในฐานข้อมูล - กรุณา Import database.sql</span><br>";
    }
    
} catch(PDOException $e) {
    echo "❌ <span style='color:red;'>ไม่สามารถเชื่อมต่อฐานข้อมูล: " . $e->getMessage() . "</span><br>";
    echo "<br><strong>แก้ไข:</strong><br>";
    echo "1. ตรวจสอบว่า MySQL ทำงานอยู่ใน XAMPP Control Panel<br>";
    echo "2. สร้างฐานข้อมูลชื่อ <strong>japanese_restaurant</strong><br>";
    echo "3. Import ไฟล์ database.sql<br>";
}

echo "<br>";

// 5. สรุป
echo "<h2>5. 🎯 สรุปและขั้นตอนถัดไป</h2>";
echo "<div style='background:#f0f0f0; padding:15px; border-radius:8px;'>";

if (file_exists('index.php')) {
    echo "✅ ระบบพร้อมใช้งาน!<br><br>";
    echo "<strong>เข้าสู่ระบบ:</strong><br>";
    echo "<a href='index.php' style='display:inline-block; background:#EE1D23; color:white; padding:10px 20px; text-decoration:none; border-radius:8px; margin-top:10px;'>
        🍱 เข้าสู่ระบบร้านอาหาร
    </a><br><br>";
    echo "<strong>บัญชีทดสอบ:</strong><br>";
    echo "- เจ้าของร้าน: Username = 1, Password = 1<br>";
    echo "- พ่อครัว: Username = 3, Password = 3<br>";
    echo "- พนักงานเสิร์ฟ: Username = 4, Password = 4<br>";
    echo "- ลูกค้า: Username = 091-111-2222, Password = 091-111-2222<br>";
} else {
    echo "❌ กรุณาสร้างไฟล์ที่ขาดหายไปตามที่แสดงด้านบน<br>";
}

echo "</div>";

// 6. ข้อมูลระบบ
echo "<br><h2>6. ℹ️ ข้อมูลระบบ</h2>";
echo "Path ปัจจุบัน: " . __DIR__ . "<br>";
echo "Server: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
?>

<style>
    body {
        font-family: Arial, sans-serif;
        max-width: 1000px;
        margin: 20px auto;
        padding: 20px;
        background: #f5f5f5;
    }
    h1 {
        color: #EE1D23;
        border-bottom: 3px solid #EE1D23;
        padding-bottom: 10px;
    }
    h2 {
        color: #1A1A1A;
        margin-top: 30px;
        border-left: 4px solid #EE1D23;
        padding-left: 10px;
    }
</style>