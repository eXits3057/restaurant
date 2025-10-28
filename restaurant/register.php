<?php
require_once 'config/db_config.php';

// ถ้า Login อยู่แล้ว ให้ไปหน้าหลัก
if (isset($_SESSION['user_id'])) {
    header('Location: customer/menu.php');
    exit;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    // Validate
    if (empty($name) || empty($phone)) {
        $error = 'กรุณากรอกชื่อและเบอร์โทรศัพท์';
    } elseif (strlen($phone) < 10) {
        $error = 'เบอร์โทรศัพท์ไม่ถูกต้อง';
    } else {
        // ตรวจสอบว่าเบอร์นี้มีในระบบแล้วหรือยัง
        $stmt = $conn->prepare("SELECT CustomerID FROM Customer WHERE PhoneNumber = ?");
        $stmt->execute([$phone]);
        
        if ($stmt->fetch()) {
            $error = 'เบอร์โทรศัพท์นี้ถูกใช้งานแล้ว';
        } else {
            // สมัครสมาชิก
            $data = [
                'CustomerName' => $name,
                'PhoneNumber' => $phone,
                'EmailAddress' => $email ?: null
            ];
            
            $customerID = insert($conn, 'Customer', $data);
            
            if ($customerID) {
                // Auto-login หลังสมัครสำเร็จ
                $_SESSION['user_id'] = $customerID;
                $_SESSION['username'] = $name;
                $_SESSION['role'] = 'customer';
                header('Location: customer/menu.php');
                exit;
            } else {
                $error = 'เกิดข้อผิดพลาด กรุณาลองใหม่';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก - ซากุระ ซูชิ & ราเมง</title>
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-background">
            <div class="bg-shape shape-1"></div>
            <div class="bg-shape shape-2"></div>
            <div class="bg-shape shape-3"></div>
        </div>
        
        <div class="auth-box">
            <div class="auth-header">
                <div class="auth-logo">🍱</div>
                <h1>สมัครสมาชิก</h1>
                <p>ซากุระ ซูชิ & ราเมง</p>
            </div>
            
            <?php if($error): ?>
                <div class="message-box error">
                    <span class="message-icon">⚠️</span>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="message-box success">
                    <span class="message-icon">✅</span>
                    <span><?php echo htmlspecialchars($success); ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="auth-form">
                <div class="form-group">
                    <label>
                        <span class="label-icon">👤</span>
                        <span>ชื่อ-นามสกุล</span>
                        <span class="required">*</span>
                    </label>
                    <div class="input-wrapper">
                        <input type="text" 
                               name="name" 
                               required 
                               placeholder="กรอกชื่อ-นามสกุล" 
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                               autofocus>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>
                        <span class="label-icon">📱</span>
                        <span>เบอร์โทรศัพท์</span>
                        <span class="required">*</span>
                    </label>
                    <div class="input-wrapper">
                        <input type="tel" 
                               name="phone" 
                               required 
                               placeholder="08X-XXX-XXXX" 
                               pattern="[0-9]{10}|[0-9]{3}-[0-9]{3}-[0-9]{4}"
                               value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                    </div>
                    <small class="input-hint">ใช้เป็นชื่อผู้ใช้และรหัสผ่านในการเข้าสู่ระบบ</small>
                </div>
                
                <div class="form-group">
                    <label>
                        <span class="label-icon">📧</span>
                        <span>อีเมล</span>
                        <span class="optional">(ไม่บังคับ)</span>
                    </label>
                    <div class="input-wrapper">
                        <input type="email" 
                               name="email" 
                               placeholder="example@email.com"
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                </div>
                
                <button type="submit" class="btn-submit">
                    <span>สมัครสมาชิก</span>
                    <span class="btn-arrow">→</span>
                </button>
            </form>
            
            <div class="info-box">
                <div class="info-icon">💡</div>
                <div class="info-content">
                    <strong>การสมัครสมาชิก</strong>
                    <ul>
                        <li>เบอร์โทรศัพท์จะใช้เป็นชื่อผู้ใช้และรหัสผ่าน</li>
                        <li>สามารถสั่งอาหารออนไลน์ได้ทันทีหลังสมัคร</li>
                        <li>ติดตามสถานะออเดอร์แบบเรียลไทม์</li>
                    </ul>
                </div>
            </div>

            <div class="auth-divider">
                <span>หรือ</span>
            </div>
            
            <div class="auth-links">
                <a href="index.php" class="link-back">
                    <span class="link-icon">←</span>
                    <span>มีบัญชีอยู่แล้ว? เข้าสู่ระบบ</span>
                </a>
            </div>
        </div>
    </div>
</body>
</html>