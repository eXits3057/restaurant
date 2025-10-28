<?php
session_start();


$error = '';

// ประมวลผล Login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // ตัวอย่างข้อมูล Users (ในระบบจริงจะเช็คจาก Database)
    $users = [
        // ลูกค้า
        ['id' => 1, 'username' => 'customer1', 'password' => '1234', 'name' => 'คุณสมชาย', 'role' => 'customer'],
        ['id' => 2, 'username' => 'customer2', 'password' => '1234', 'name' => 'คุณสุดา', 'role' => 'customer'],
        
        // พนักงานเสิร์ฟ
        ['id' => 3, 'username' => 'waiter1', 'password' => '1234', 'name' => 'น้องแพร', 'role' => 'waiter'],
        ['id' => 4, 'username' => 'waiter2', 'password' => '1234', 'name' => 'พี่มิ้นท์', 'role' => 'waiter'],
        
        // พ่อครัว
        ['id' => 5, 'username' => 'kitchen1', 'password' => '1234', 'name' => 'พี่เอก', 'role' => 'kitchen'],
        ['id' => 6, 'username' => 'kitchen2', 'password' => '1234', 'name' => 'พี่นุ่น', 'role' => 'kitchen'],
        
        // เจ้าของร้าน
        ['id' => 7, 'username' => 'owner', 'password' => 'admin1234', 'name' => 'คุณสมชาย ใจดี', 'role' => 'owner'],
    ];
    
    // ค้นหา User
    $foundUser = null;
    foreach ($users as $user) {
        if ($user['username'] === $username && $user['password'] === $password) {
            $foundUser = $user;
            break;
        }
    }
    
    if ($foundUser) {
        // Login สำเร็จ
        $_SESSION['user_id'] = $foundUser['id'];
        $_SESSION['user_name'] = $foundUser['name'];
        $_SESSION['user_role'] = $foundUser['role'];
        $_SESSION['username'] = $foundUser['username'];
        
        // Redirect ตาม Role
        if ($foundUser['role'] == 'owner') {
            header('Location: dashboard.php');
        } elseif ($foundUser['role'] == 'kitchen') {
            header('Location: kitchen.php');
        } else {
            header('Location: index.php');
        }
        exit();
    } else {
        $error = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - ซากุระ ซูชิ & ราเมง</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #EE1D23 0%, #C41E3A 100%);
            padding: 20px;
        }
        
        .login-box {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 450px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-logo {
            font-size: 64px;
            margin-bottom: 10px;
        }
        
        .login-title {
            font-size: 28px;
            font-weight: 700;
            color: #1A1A1A;
            margin-bottom: 5px;
        }
        
        .login-subtitle {
            font-size: 14px;
            color: #4A4A4A;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #1A1A1A;
        }
        
        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #F5F5F5;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #EE1D23;
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #EE1D23 0%, #C41E3A 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(238, 29, 35, 0.4);
        }
        
        .error-message {
            background: #FFEBEE;
            color: #C62828;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .demo-accounts {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 2px solid #F5F5F5;
        }
        
        .demo-title {
            font-weight: 700;
            margin-bottom: 15px;
            color: #1A1A1A;
        }
        
        .demo-list {
            font-size: 14px;
            color: #4A4A4A;
            line-height: 1.8;
        }
        
        .demo-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding: 8px;
            background: #F5F5F5;
            border-radius: 6px;
        }
        
        .demo-role {
            font-weight: 600;
            color: #EE1D23;
        }
        
        .demo-credentials {
            font-family: monospace;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <div class="login-logo">🍱</div>
                <h1 class="login-title">ซากุระ ซูชิ & ราเมง</h1>
                <p class="login-subtitle">ระบบจัดการร้านอาหารญี่ปุ่น</p>
            </div>
            
            <?php if ($error): ?>
            <div class="error-message">
                ⚠️ <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">ชื่อผู้ใช้</label>
                    <input type="text" name="username" class="form-input" 
                           placeholder="กรอกชื่อผู้ใช้" required autofocus>
                </div>
                
                <div class="form-group">
                    <label class="form-label">รหัสผ่าน</label>
                    <input type="password" name="password" class="form-input" 
                           placeholder="กรอกรหัสผ่าน" required>
                </div>
                
                <button type="submit" class="btn-login">เข้าสู่ระบบ</button>
            </form>
            
            <div class="demo-accounts">
                <div class="demo-title">🔑 บัญชีทดสอบ (Demo Accounts)</div>
                <div class="demo-list">
                    <div class="demo-item">
                        <span class="demo-role">👤 ลูกค้า:</span>
                        <span class="demo-credentials">customer1 / 1234</span>
                    </div>
                    <div class="demo-item">
                        <span class="demo-role">🍽️ พนักงานเสิร์ฟ:</span>
                        <span class="demo-credentials">waiter1 / 1234</span>
                    </div>
                    <div class="demo-item">
                        <span class="demo-role">🍳 พ่อครัว:</span>
                        <span class="demo-credentials">kitchen1 / 1234</span>
                    </div>
                    <div class="demo-item">
                        <span class="demo-role">👨‍💼 เจ้าของร้าน:</span>
                        <span class="demo-credentials">owner / admin1234</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>