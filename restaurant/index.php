<?php
require_once 'config/db_config.php';

// ตรวจสอบว่า Login แล้วหรือยัง
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'customer':
            header('Location: customer/menu.php');
            break;
        case 'kitchen':
            header('Location: kitchen/dashboard.php');
            break;
        case 'staff':
            header('Location: staff/dashboard.php');
            break;
        case 'admin':
            header('Location: admin/dashboard.php');
            break;
        default:
            session_destroy();
            break;
    }
    exit;
}

// ประมวลผล Login
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน';
    } else {
        // ตรวจสอบพนักงาน
        $stmt = $conn->prepare("SELECT * FROM Staff WHERE StaffID = ? AND IsActive = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && $password === $user['Password']) {
            $_SESSION['user_id'] = $user['StaffID'];
            $_SESSION['username'] = $user['StaffName'];

            switch ($user['Role']) {
                case 'admin':
                    $_SESSION['role'] = 'admin';
                    header('Location: admin/dashboard.php');
                    break;
                case 'kitchen':
                    $_SESSION['role'] = 'kitchen';
                    header('Location: kitchen/dashboard.php');
                    break;
                case 'staff':
                    $_SESSION['role'] = 'staff';
                    header('Location: staff/dashboard.php');
                    break;
                default:
                    $error = 'ตำแหน่งงานไม่ถูกต้อง';
            }
            exit;
        } else {
            // ตรวจสอบลูกค้า
            $stmt = $conn->prepare("SELECT * FROM Customer WHERE PhoneNumber = ?");
            $stmt->execute([$username]);
            $customer = $stmt->fetch();

            if ($customer && $password === $customer['PhoneNumber']) {
                $_SESSION['user_id'] = $customer['CustomerID'];
                $_SESSION['username'] = $customer['CustomerName'];
                $_SESSION['role'] = $customer['Role'];
                header('Location: customer/menu.php');
                exit;
            } else {
                $error = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
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
    <title>เข้าสู่ระบบ - ซากุระ ซูชิ & ราเมง</title>
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
                <h1>ซากุระ ซูชิ & ราเมง</h1>
                <p>Japanese Restaurant Management System</p>
            </div>

            <?php if ($error): ?>
                <div class="message-box error">
                    <span class="message-icon">⚠️</span>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="auth-form">
                <div class="form-group">
                    <label>
                        <span class="label-icon">👤</span>
                        <span>ชื่อผู้ใช้</span>
                    </label>
                    <div class="input-wrapper">
                        <input type="text" 
                               name="username" 
                               required 
                               placeholder="รหัสพนักงาน หรือ เบอร์โทรศัพท์"
                               autocomplete="off"
                               autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label>
                        <span class="label-icon">🔒</span>
                        <span>รหัสผ่าน</span>
                    </label>
                    <div class="input-wrapper">
                        <input type="password" 
                               name="password" 
                               required 
                               placeholder="รหัสผ่าน" 
                               autocomplete="off">
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <span>เข้าสู่ระบบ</span>
                    <span class="btn-arrow">→</span>
                </button>
            </form>

            <div class="demo-accounts">
                <h3>
                    <span class="demo-icon">🔑</span>
                    <span>บัญชีทดสอบ (Demo Accounts)</span>
                </h3>
                
                <?php
                // ตรวจสอบว่ามีข้อมูลพนักงานหรือไม่
                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM Staff WHERE IsActive = 1");
                $stmt->execute();
                $staffCount = $stmt->fetch()['count'];
                
                if ($staffCount == 0):
                ?>
                    <div class="warning-box">
                        <div class="warning-icon">⚠️</div>
                        <div class="warning-content">
                            <strong>ยังไม่มีข้อมูลพนักงานในระบบ</strong>
                            <p>กรุณาสร้างข้อมูลพนักงานก่อนเข้าสู่ระบบ</p>
                            <a href="init_data.php" class="btn-warning-action">
                                ⚙️ สร้างข้อมูลพนักงาน
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="demo-grid">
                        <div class="demo-item owner">
                            <div class="demo-icon-large">👑</div>
                            <div class="demo-info">
                                <strong>เจ้าของร้าน (Admin)</strong>
                                <p>Username: <code>1</code></p>
                                <p>Password: <code>1</code></p>
                            </div>
                        </div>
                        <div class="demo-item kitchen">
                            <div class="demo-icon-large">👨‍🍳</div>
                            <div class="demo-info">
                                <strong>พ่อครัว</strong>
                                <p>Username: <code>3</code></p>
                                <p>Password: <code>3</code></p>
                            </div>
                        </div>
                        <div class="demo-item staff">
                            <div class="demo-icon-large">🍽️</div>
                            <div class="demo-info">
                                <strong>พนักงานเสิร์ฟ</strong>
                                <p>Username: <code>4</code></p>
                                <p>Password: <code>4</code></p>
                            </div>
                        </div>
                        <div class="demo-item customer">
                            <div class="demo-icon-large">🙋</div>
                            <div class="demo-info">
                                <strong>ลูกค้า</strong>
                                <p>สมัครสมาชิกด้านล่าง</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="auth-divider">
                <span>หรือ</span>
            </div>

            <div class="auth-links">
                <a href="register.php" class="link-register">
                    <span class="link-icon">➕</span>
                    <span>สมัครสมาชิกลูกค้า</span>
                </a>
                <?php if ($staffCount > 0): ?>
                    <a href="init_data.php" class="link-init">
                        <span class="link-icon">⚙️</span>
                        <span>จัดการข้อมูลพนักงาน</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>