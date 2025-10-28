<?php
// ========================================
// ไฟล์ติดตั้งฐานข้อมูลอัตโนมัติ
// เปิดที่: http://localhost/Myproject/restaurant/install.php
// ========================================

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "japanese_restaurant";

try {
    // เชื่อมต่อ MySQL (ยังไม่เลือก database)
    $conn = new PDO("mysql:host=$servername", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h2>🔧 เริ่มติดตั้งฐานข้อมูล...</h2>";

    // สร้าง Database
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $conn->exec($sql);
    echo "<p>✅ สร้างฐานข้อมูล '$dbname' สำเร็จ</p>";

    // เลือก Database
    $conn->exec("USE $dbname");

    // ลบตารางเก่า (ถ้ามี)
    $tables = ['Payment', 'OrderDetail', '`Order`', 'MenuIngredient', 'Menu', 'Ingredient', 'Staff', 'Customer'];
    foreach ($tables as $table) {
        $conn->exec("DROP TABLE IF EXISTS $table");
    }
    echo "<p>✅ ลบตารางเก่า (ถ้ามี)</p>";

    // สร้างตาราง Customer
    $conn->exec("
        CREATE TABLE Customer (
            CustomerID INT PRIMARY KEY AUTO_INCREMENT,
            CustomerName VARCHAR(100) NOT NULL,
            PhoneNumber VARCHAR(20) NOT NULL,
            EmailAddress VARCHAR(100),
            Role ENUM('customer') DEFAULT 'customer',
            CreatedDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "<p>✅ สร้างตาราง Customer</p>";

    // สร้างตาราง Staff
    $conn->exec("
        CREATE TABLE Staff (
            StaffID INT PRIMARY KEY AUTO_INCREMENT,
            StaffName VARCHAR(100) NOT NULL,
            Password VARCHAR(255) NOT NULL,
            Role ENUM('staff', 'kitchen', 'admin') NOT NULL,
            PhoneNumber VARCHAR(20),
            HireDate DATE,
            IsActive BOOLEAN DEFAULT TRUE
        )
    ");
    echo "<p>✅ สร้างตาราง Staff</p>";

    // สร้างตาราง Menu
    $conn->exec("
        CREATE TABLE Menu (
            MenuID INT PRIMARY KEY AUTO_INCREMENT,
            MenuName VARCHAR(100) NOT NULL,
            MenuNameEng VARCHAR(100),
            Price DECIMAL(10,2) NOT NULL,
            Category ENUM('ซูชิ', 'ราเมง', 'ข้าวหน้า', 'ทอด', 'เครื่องดื่ม', 'ของหวาน') NOT NULL,
            Description TEXT,
            IsSignature BOOLEAN DEFAULT FALSE,
            IsAvailable BOOLEAN DEFAULT TRUE,
            ImageURL VARCHAR(255)
        )
    ");
    echo "<p>✅ สร้างตาราง Menu</p>";

    // สร้างตาราง Ingredient
    $conn->exec("
        CREATE TABLE Ingredient (
            IngredientID INT PRIMARY KEY AUTO_INCREMENT,
            IngredientName VARCHAR(100) NOT NULL,
            Unit VARCHAR(20) NOT NULL,
            StockQuantity DECIMAL(10,2) NOT NULL DEFAULT 0,
            MinimumStock DECIMAL(10,2) DEFAULT 10,
            UnitPrice DECIMAL(10,2),
            LastRestockDate DATE
        )
    ");
    echo "<p>✅ สร้างตาราง Ingredient</p>";

    // สร้างตาราง MenuIngredient
    $conn->exec("
        CREATE TABLE MenuIngredient (
            MenuIngredientID INT PRIMARY KEY AUTO_INCREMENT,
            MenuID INT NOT NULL,
            IngredientID INT NOT NULL,
            QuantityNeeded DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (MenuID) REFERENCES Menu(MenuID) ON DELETE CASCADE,
            FOREIGN KEY (IngredientID) REFERENCES Ingredient(IngredientID) ON DELETE RESTRICT
        )
    ");
    echo "<p>✅ สร้างตาราง MenuIngredient</p>";

    // สร้างตาราง Order
    $conn->exec("
        CREATE TABLE `Order` (
            OrderID INT PRIMARY KEY AUTO_INCREMENT,
            CustomerID INT,
            StaffID INT NOT NULL,
            OrderDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            OrderType ENUM('กินที่ร้าน', 'กลับบ้าน', 'เดลิเวอรี่') NOT NULL DEFAULT 'กินที่ร้าน',
            TableNumber INT,
            OrderStatus ENUM('รอดำเนินการ', 'กำลังทำ', 'เสร็จแล้ว', 'เสิร์ฟแล้ว', 'จ่ายเงินแล้ว', 'ยกเลิก') DEFAULT 'รอดำเนินการ',
            TotalAmount DECIMAL(10,2) NOT NULL DEFAULT 0,
            DiscountAmount DECIMAL(10,2) DEFAULT 0,
            NetAmount DECIMAL(10,2) NOT NULL DEFAULT 0,
            Notes TEXT,
            FOREIGN KEY (CustomerID) REFERENCES Customer(CustomerID) ON DELETE SET NULL,
            FOREIGN KEY (StaffID) REFERENCES Staff(StaffID) ON DELETE RESTRICT
        )
    ");
    echo "<p>✅ สร้างตาราง Order</p>";

    // สร้างตาราง OrderDetail
    $conn->exec("
        CREATE TABLE OrderDetail (
            OrderDetailID INT PRIMARY KEY AUTO_INCREMENT,
            OrderID INT NOT NULL,
            MenuID INT NOT NULL,
            Quantity INT NOT NULL DEFAULT 1,
            UnitPrice DECIMAL(10,2) NOT NULL,
            SubTotal DECIMAL(10,2) NOT NULL,
            ItemStatus ENUM('รอทำ', 'กำลังทำ', 'เสร็จแล้ว', 'เสิร์ฟแล้ว') DEFAULT 'รอทำ',
            SpecialRequest TEXT,
            FOREIGN KEY (OrderID) REFERENCES `Order`(OrderID) ON DELETE CASCADE,
            FOREIGN KEY (MenuID) REFERENCES Menu(MenuID) ON DELETE RESTRICT
        )
    ");
    echo "<p>✅ สร้างตาราง OrderDetail</p>";

    // สร้างตาราง Payment
    $conn->exec("
        CREATE TABLE Payment (
            PaymentID INT PRIMARY KEY AUTO_INCREMENT,
            OrderID INT NOT NULL,
            PaymentMethod ENUM('เงินสด', 'QR Code', 'บัตรเครดิต', 'บัตรเดบิต', 'โอนเงิน') NOT NULL,
            AmountPaid DECIMAL(10,2) NOT NULL,
            PaymentDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            ReceivedBy INT,
            TransactionRef VARCHAR(100),
            FOREIGN KEY (OrderID) REFERENCES `Order`(OrderID) ON DELETE RESTRICT,
            FOREIGN KEY (ReceivedBy) REFERENCES Staff(StaffID) ON DELETE SET NULL
        )
    ");
    echo "<p>✅ สร้างตาราง Payment</p>";

    echo "<hr><h3>📊 เพิ่มข้อมูลตัวอย่าง...</h3>";

    // เพิ่มข้อมูลพนักงาน
    $conn->exec("
    INSERT INTO Staff (StaffName,Password ,Role, PhoneNumber, HireDate) VALUES
    ('คุณสมชาย ใจดี', '1','admin', '081-234-5678', '2023-01-01'),  -- เจ้าของร้าน
    ('น้องมินท์','2', 'staff', '082-345-6789', '2023-02-15'),      -- แคชเชียร์
    ('พี่เอก','3' ,'kitchen', '083-456-7890', '2023-02-20'),        -- ครัว
    ('น้องแพร','4' ,'staff', '084-567-8901', '2023-03-10'),         -- เสิร์ฟ
    ('พี่นุ่น','5', 'kitchen', '085-678-9012', '2023-03-15')        -- ครัว
");
    echo "<p>✅ เพิ่มข้อมูลพนักงาน 5 คน</p>";

    // เพิ่มข้อมูลลูกค้า
    // ✅ เพิ่มข้อมูลลูกค้าตัวอย่าง
    $conn->exec("
    INSERT INTO Customer (CustomerName, PhoneNumber, EmailAddress, Role) VALUES
    ('คุณอรุณ สว่างไสว', '091-111-2222', 'arun@email.com', 'customer'),
    ('คุณสุดา รักสวย', '092-222-3333', 'suda@email.com', 'customer'),
    ('คุณวิชัย กล้าหาญ', '093-333-4444', NULL, 'customer'),
    ('คุณนิดา น่ารัก', '094-444-5555', 'nida@email.com', 'customer'),
    ('Walk-in Customer', '000-000-0000', NULL, 'customer')
");
    echo "<p>✅ เพิ่มข้อมูลลูกค้า 5 คน</p>";


    // เพิ่มข้อมูลเมนู
    $conn->exec("
        INSERT INTO Menu (MenuName, MenuNameEng, Price, Category, Description, IsSignature, IsAvailable) VALUES
        ('ซูชิแซลมอน', 'Salmon Nigiri', 40.00, 'ซูชิ', 'แซลมอนสดชิ้นโตวางบนข้าวซูชิ', TRUE, TRUE),
        ('ซูชิปลาทูน่า', 'Tuna Roll', 60.00, 'ซูชิ', 'ทูน่าสดห่อสาหร่าย', FALSE, TRUE),
        ('ซูชิกุ้ง', 'Ebi Sushi', 40.00, 'ซูชิ', 'กุ้งต้มสดใหม่', FALSE, TRUE),
        ('ซูชิไข่หวาน', 'Tamago Sushi', 20.00, 'ซูชิ', 'ไข่หวานญี่ปุ่น', FALSE, TRUE),
        ('ราเมงโชยุ', 'Shoyu Ramen', 120.00, 'ราเมง', 'ราเมงซีอิ๊ว', FALSE, TRUE),
        ('ราเมงมิโซะ', 'Miso Ramen', 130.00, 'ราเมง', 'ราเมงมิโซะ', FALSE, TRUE),
        ('ราเมงทงคตสึ', 'Tonkotsu Ramen', 140.00, 'ราเมง', 'น้ำซุปกระดูกหมู 8 ชม.', TRUE, TRUE),
        ('ราเมงชาชู', 'Chashu Ramen', 150.00, 'ราเมง', 'ราเมงชาชู', FALSE, TRUE),
        ('ข้าวหน้าเนื้อทอดไข่ข้น', 'Katsudon', 110.00, 'ข้าวหน้า', 'เนื้อทอดไข่ข้น', FALSE, TRUE),
        ('ข้าวหน้าเนื้อเทอริยากิ', 'Gyudon', 100.00, 'ข้าวหน้า', 'เนื้อเทอริยากิ', FALSE, TRUE),
        ('ข้าวหน้าไก่ไข่ข้น', 'Oyakodon', 95.00, 'ข้าวหน้า', 'ไก่ไข่ข้น', FALSE, TRUE),
        ('ข้าวหน้าปลาไหลย่าง', 'Unadon', 150.00, 'ข้าวหน้า', 'ปลาไหลย่าง', TRUE, TRUE),
        ('ข้าวแกงกะหรี่ญี่ปุ่น', 'Curry Rice', 120.00, 'ข้าวหน้า', 'แกงกะหรี่ญี่ปุ่น', FALSE, TRUE),
        ('กุ้งเทมปุระทอด', 'Shrimp Tempura', 135.00, 'ทอด', 'กุ้งทอด 4 ตัว', FALSE, TRUE)
    ");
    echo "<p>✅ เพิ่มเมนูอาหาร 14 รายการ</p>";

    // เพิ่มข้อมูลวัตถุดิบ
    $conn->exec("
        INSERT INTO Ingredient (IngredientName, Unit, StockQuantity, MinimumStock, UnitPrice) VALUES
        ('แซลมอนสด', 'กก.', 15.00, 5.00, 450.00),
        ('ปลาทูน่าสด', 'กก.', 10.00, 3.00, 380.00),
        ('กุ้งสด', 'กก.', 20.00, 5.00, 220.00),
        ('ไข่ไก่', 'ฟอง', 200.00, 50.00, 5.00),
        ('ข้าวญี่ปุ่น', 'กก.', 50.00, 10.00, 45.00),
        ('เส้นราเมง', 'กก.', 30.00, 10.00, 60.00),
        ('น้ำซุปกระดูกหมู', 'ลิตร', 25.00, 5.00, 80.00),
        ('ซอสโชยุ', 'ลิตร', 10.00, 2.00, 95.00),
        ('มิโซะ', 'กก.', 8.00, 2.00, 120.00),
        ('เนื้อวัว', 'กก.', 12.00, 5.00, 280.00),
        ('เนื้อไก่', 'กก.', 15.00, 5.00, 120.00),
        ('ปลาไหล', 'กก.', 8.00, 2.00, 550.00),
        ('สาหร่าย', 'แพ็ค', 40.00, 10.00, 15.00),
        ('แป้งเทมปุระ', 'กก.', 10.00, 3.00, 85.00),
        ('ผงกะหรี่ญี่ปุ่น', 'กก.', 5.00, 2.00, 180.00)
    ");
    echo "<p>✅ เพิ่มวัตถุดิบ 15 รายการ</p>";

    echo "<hr><h2>🎉 ติดตั้งสำเร็จ!</h2>";
    echo "<p><strong>ฐานข้อมูล:</strong> japanese_restaurant</p>";
    echo "<p><strong>ตาราง:</strong> 8 ตาราง</p>";
    echo "<p><strong>ข้อมูลตัวอย่าง:</strong> พร้อมใช้งาน</p>";
    echo "<hr>";
    echo "<h3>🔑 บัญชีทดสอบ:</h3>";
    echo "<ul>";
    echo "<li><strong>เจ้าของร้าน:</strong> Username = 1, Password = 1</li>";
    echo "<li><strong>พ่อครัว:</strong> Username = 3, Password = 3</li>";
    echo "<li><strong>พนักงานเสิร์ฟ:</strong> Username = 4, Password = 4</li>";
    echo "<li><strong>ลูกค้า:</strong> Username = 091-111-2222, Password = 091-111-2222</li>";
    echo "</ul>";
    echo "<hr>";
    echo "<p><a href='index.php' style='display:inline-block; background:#EE1D23; color:white; padding:15px 30px; text-decoration:none; border-radius:8px; font-weight:bold;'>🍱 เข้าสู่ระบบ</a></p>";
    echo "<p style='color:#999; margin-top:30px;'><small>หมายเหตุ: ลบไฟล์ install.php หลังติดตั้งเสร็จเพื่อความปลอดภัย</small></p>";

} catch (PDOException $e) {
    echo "<h2>❌ เกิดข้อผิดพลาด!</h2>";
    echo "<p style='color:red;'>" . $e->getMessage() . "</p>";
}

$conn = null;
?>

<style>
    body {
        font-family: Arial, sans-serif;
        max-width: 800px;
        margin: 50px auto;
        padding: 20px;
        background: #f5f5f5;
    }

    h2 {
        color: #EE1D23;
    }

    p {
        line-height: 1.8;
    }
</style>