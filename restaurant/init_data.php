<?php
/**
 * ไฟล์สำหรับสร้างข้อมูลพนักงานเริ่มต้นอัตโนมัติ
 * ให้รันไฟล์นี้ครั้งเดียวหลังจากติดตั้งระบบ
 * หรือเมื่อต้องการ Reset ข้อมูลพนักงาน
 */

require_once 'config/db_config.php';

// ตรวจสอบว่ามีข้อมูลพนักงานอยู่แล้วหรือไม่
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM Staff");
$stmt->execute();
$result = $stmt->fetch();

$message = '';
$messageType = '';

if ($result['count'] > 0) {
    // มีข้อมูลอยู่แล้ว
    if (isset($_POST['force_reset'])) {
        // Force Reset - ลบข้อมูลเก่าและสร้างใหม่
        try {
            $conn->exec("DELETE FROM Staff");
            createInitialData($conn);
            $message = '✅ Reset และสร้างข้อมูลพนักงานใหม่สำเร็จ!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = '❌ เกิดข้อผิดพลาด: ' . $e->getMessage();
            $messageType = 'error';
        }
    } else {
        $message = 'ℹ️ มีข้อมูลพนักงานอยู่ในระบบแล้ว (' . $result['count'] . ' คน)';
        $messageType = 'info';
    }
} else {
    // ยังไม่มีข้อมูล - สร้างอัตโนมัติ
    try {
        createInitialData($conn);
        $message = '✅ สร้างข้อมูลพนักงานเริ่มต้นสำเร็จ!';
        $messageType = 'success';
    } catch (Exception $e) {
        $message = '❌ เกิดข้อผิดพลาด: ' . $e->getMessage();
        $messageType = 'error';
    }
}

/**
 * ฟังก์ชันสร้างข้อมูลพนักงานเริ่มต้น
 */
function createInitialData($conn) {
    $staffData = [
        [
            'StaffID' => '1',
            'StaffName' => 'สมชาย ใจดี',
            'StaffRole' => 'เจ้าของร้าน',
            'PhoneNumber' => '081-111-1111',
            'Salary' => 50000.00,
            'IsActive' => 1
        ],
        [
            'StaffID' => '2',
            'StaffName' => 'สมหญิง รักงาน',
            'StaffRole' => 'แคชเชียร์',
            'PhoneNumber' => '082-222-2222',
            'Salary' => 15000.00,
            'IsActive' => 1
        ],
        [
            'StaffID' => '3',
            'StaffName' => 'สมศักดิ์ ทำกิน',
            'StaffRole' => 'ครัว',
            'PhoneNumber' => '083-333-3333',
            'Salary' => 18000.00,
            'IsActive' => 1
        ],
        [
            'StaffID' => '4',
            'StaffName' => 'สมศรี บริการ',
            'StaffRole' => 'เสิร์ฟ',
            'PhoneNumber' => '084-444-4444',
            'Salary' => 14000.00,
            'IsActive' => 1
        ],
        [
            'StaffID' => '5',
            'StaffName' => 'สมปอง ขยัน',
            'StaffRole' => 'ครัว',
            'PhoneNumber' => '085-555-5555',
            'Salary' => 17000.00,
            'IsActive' => 1
        ],
        [
            'StaffID' => '6',
            'StaffName' => 'สมใจ ยิ้มแย้ม',
            'StaffRole' => 'เสิร์ฟ',
            'PhoneNumber' => '086-666-6666',
            'Salary' => 14000.00,
            'IsActive' => 1
        ]
    ];

    $stmt = $conn->prepare("
        INSERT INTO Staff (StaffID, StaffName, StaffRole, PhoneNumber, Salary, IsActive) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    foreach ($staffData as $staff) {
        $stmt->execute([
            $staff['StaffID'],
            $staff['StaffName'],
            $staff['StaffRole'],
            $staff['PhoneNumber'],
            $staff['Salary'],
            $staff['IsActive']
        ]);
    }
}

// ดึงข้อมูลพนักงานทั้งหมดมาแสดง
$allStaff = getAll($conn, "SELECT * FROM Staff ORDER BY StaffRole, StaffID");
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สร้างข้อมูลเริ่มต้น - ซากุระ ซูชิ & ราเมง</title>
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
    <div class="init-container">
        <div class="init-box">
            <div class="init-header">
                <div class="init-logo">🍱</div>
                <h1>จัดการข้อมูลพนักงาน</h1>
                <p>ซากุระ ซูชิ & ราเมง</p>
            </div>

            <?php if($message): ?>
                <div class="message-box <?php echo $messageType; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="staff-list">
                <h2>พนักงานในระบบ (<?php echo count($allStaff); ?> คน)</h2>
                
                <?php if(count($allStaff) > 0): ?>
                    <div class="table-responsive">
                        <table class="staff-table">
                            <thead>
                                <tr>
                                    <th>รหัส</th>
                                    <th>ชื่อ</th>
                                    <th>ตำแหน่ง</th>
                                    <th>เบอร์โทร</th>
                                    <th>เงินเดือน</th>
                                    <th>สถานะ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($allStaff as $staff): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($staff['StaffID']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($staff['StaffName']); ?></td>
                                    <td>
                                        <span class="role-badge <?php echo strtolower($staff['StaffRole']); ?>">
                                            <?php 
                                            $roleIcons = [
                                                'เจ้าของร้าน' => '👑',
                                                'แคชเชียร์' => '💰',
                                                'ครัว' => '👨‍🍳',
                                                'เสิร์ฟ' => '🍽️'
                                            ];
                                            echo $roleIcons[$staff['StaffRole']] ?? '👤';
                                            ?> 
                                            <?php echo htmlspecialchars($staff['StaffRole']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($staff['PhoneNumber']); ?></td>
                                    <td class="salary">฿<?php echo number_format($staff['Salary'], 2); ?></td>
                                    <td>
                                        <?php if($staff['IsActive']): ?>
                                            <span class="status-badge active">ใช้งาน</span>
                                        <?php else: ?>
                                            <span class="status-badge inactive">ปิดใช้งาน</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="login-info">
                        <h3>📝 ข้อมูลการเข้าสู่ระบบ</h3>
                        <div class="info-grid">
                            <div class="info-card">
                                <div class="info-icon">👑</div>
                                <div class="info-content">
                                    <strong>เจ้าของร้าน</strong>
                                    <p>Username: <code>1</code></p>
                                    <p>Password: <code>1</code></p>
                                </div>
                            </div>
                            <div class="info-card">
                                <div class="info-icon">💰</div>
                                <div class="info-content">
                                    <strong>แคชเชียร์</strong>
                                    <p>Username: <code>2</code></p>
                                    <p>Password: <code>2</code></p>
                                </div>
                            </div>
                            <div class="info-card">
                                <div class="info-icon">👨‍🍳</div>
                                <div class="info-content">
                                    <strong>พ่อครัว</strong>
                                    <p>Username: <code>3</code> หรือ <code>5</code></p>
                                    <p>Password: <code>3</code> หรือ <code>5</code></p>
                                </div>
                            </div>
                            <div class="info-card">
                                <div class="info-icon">🍽️</div>
                                <div class="info-content">
                                    <strong>พนักงานเสิร์ฟ</strong>
                                    <p>Username: <code>4</code> หรือ <code>6</code></p>
                                    <p>Password: <code>4</code> หรือ <code>6</code></p>
                                </div>
                            </div>
                        </div>
                        <div class="note-box">
                            <strong>💡 หมายเหตุ:</strong> รหัสพนักงานคือชื่อผู้ใช้และรหัสผ่านเดียวกัน
                        </div>
                    </div>

                    <form method="POST" class="reset-form" onsubmit="return confirm('⚠️ คุณแน่ใจหรือไม่ว่าต้องการลบข้อมูลพนักงานทั้งหมดและสร้างใหม่?');">
                        <button type="submit" name="force_reset" class="btn-reset">
                            🔄 Reset และสร้างข้อมูลใหม่
                        </button>
                    </form>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">📋</div>
                        <p>ยังไม่มีข้อมูลพนักงานในระบบ</p>
                        <form method="POST">
                            <button type="submit" class="btn-create">
                                ➕ สร้างข้อมูลพนักงานเริ่มต้น
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>

            <div class="action-buttons">
                <a href="index.php" class="btn-back">← กลับหน้าเข้าสู่ระบบ</a>
                <a href="register.php" class="btn-register-link">สมัครสมาชิกลูกค้า →</a>
            </div>
        </div>
    </div>
</body>
</html>