<?php
require_once '../config/db_config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'kitchen') {
    header('Location: ../index.php');
    exit;
}

$staffName = $_SESSION['username'];

$orders = getAll($conn, "
    SELECT o.OrderID, o.OrderDate, o.OrderType, o.TableNumber, o.OrderStatus,
           c.CustomerName, s.StaffName,
           GROUP_CONCAT(
               CONCAT(od.Quantity, 'x ', m.MenuName, 
                      CASE WHEN od.SpecialRequest IS NOT NULL 
                      THEN CONCAT(' (', od.SpecialRequest, ')') 
                      ELSE '' END)
               SEPARATOR ', '
           ) as Items
    FROM `Order` o
    LEFT JOIN Customer c ON o.CustomerID = c.CustomerID
    LEFT JOIN Staff s ON o.StaffID = s.StaffID
    LEFT JOIN OrderDetail od ON o.OrderID = od.OrderID
    LEFT JOIN Menu m ON od.MenuID = m.MenuID
    WHERE o.OrderStatus IN ('รอดำเนินการ', 'กำลังทำ')
    GROUP BY o.OrderID
    ORDER BY o.OrderDate ASC
");

$completedToday = getOne($conn, "
    SELECT COUNT(*) as count
    FROM `Order`
    WHERE DATE(OrderDate) = CURDATE()
    AND OrderStatus IN ('เสร็จแล้ว', 'เสิร์ฟแล้ว', 'จ่ายเงินแล้ว')
");
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จอครัว - Kitchen Display</title>
    <link rel="stylesheet" href="../css/main.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: #1A1A1A;
            color: white;
        }
        .navbar {
            background: linear-gradient(135deg, #000000 0%, #1A1A1A 100%);
            padding: 15px 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }
        .navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .nav-brand h1 {
            font-size: 24px;
            margin: 0;
            color: white;
        }
        .nav-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .user-name {
            font-size: 16px;
        }
        .btn-logout {
            background: #EE1D23;
            color: white;
            padding: 8px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
        }
        .stats-section {
            padding: 30px 0;
            background: #2C2C2C;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            border-left: 4px solid #FFB800;
        }
        .stat-card.cooking {
            border-left-color: #EE1D23;
        }
        .stat-card.completed {
            border-left-color: #06C755;
        }
        .stat-number {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .stat-label {
            font-size: 18px;
            opacity: 0.8;
        }
        .orders-section {
            padding: 30px 0;
        }
        .orders-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .orders-header h2 {
            font-size: 28px;
            margin: 0;
        }
        .btn-refresh {
            background: white;
            color: #1A1A1A;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }
        .orders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }
        .order-card {
            background: white;
            color: #1A1A1A;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            position: relative;
            border-left: 6px solid #FFB800;
        }
        .order-card.cooking {
            border-left-color: #EE1D23;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { box-shadow: 0 4px 20px rgba(238, 29, 35, 0.3); }
            50% { box-shadow: 0 4px 30px rgba(238, 29, 35, 0.6); }
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        .order-number {
            font-size: 24px;
            font-weight: 700;
            color: #EE1D23;
        }
        .order-time {
            font-size: 18px;
            font-weight: 600;
            color: #666;
        }
        .order-info {
            margin-bottom: 15px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 15px;
        }
        .info-row .label {
            color: #666;
        }
        .info-row .value {
            font-weight: 600;
        }
        .order-items {
            background: #FFF5F5;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .order-items h4 {
            font-size: 16px;
            margin: 0 0 10px 0;
            color: #1A1A1A;
        }
        .order-items p {
            font-size: 15px;
            line-height: 1.6;
            margin: 0;
        }
        .order-actions {
            display: flex;
            gap: 10px;
        }
        .btn-action {
            flex: 1;
            padding: 14px;
            border-radius: 8px;
            border: none;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-start {
            background: #FFB800;
            color: #1A1A1A;
        }
        .btn-start:hover {
            background: #e5a600;
            transform: scale(1.05);
        }
        .btn-complete {
            background: #06C755;
            color: white;
        }
        .btn-complete:hover {
            background: #05a647;
            transform: scale(1.05);
        }
        .order-status-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            background: #FFB800;
            color: #1A1A1A;
        }
        .order-status-badge.cooking {
            background: #EE1D23;
            color: white;
            animation: blink 1.5s infinite;
        }
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .no-orders {
            text-align: center;
            padding: 100px 20px;
            font-size: 24px;
            color: #666;
        }
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .orders-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <h1>🍳 จอครัว - Kitchen Display</h1>
            </div>
            <div class="nav-user">
                <span class="user-name">👨‍🍳 <?php echo htmlspecialchars($staffName); ?></span>
                <a href="../logout.php" class="btn-logout">ออกจากระบบ</a>
            </div>
        </div>
    </nav>

    <section class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number" id="pendingCount">
                        <?php 
                        $pending = array_filter($orders, fn($o) => $o['OrderStatus'] === 'รอดำเนินการ');
                        echo count($pending);
                        ?>
                    </div>
                    <div class="stat-label">รอทำ</div>
                </div>
                <div class="stat-card cooking">
                    <div class="stat-number" id="cookingCount">
                        <?php 
                        $cooking = array_filter($orders, fn($o) => $o['OrderStatus'] === 'กำลังทำ');
                        echo count($cooking);
                        ?>
                    </div>
                    <div class="stat-label">กำลังทำ</div>
                </div>
                <div class="stat-card completed">
                    <div class="stat-number"><?php echo $completedToday['count'] ?? 0; ?></div>
                    <div class="stat-label">เสร็จวันนี้</div>
                </div>
            </div>
        </div>
    </section>

    <section class="orders-section">
        <div class="container">
            <div class="orders-header">
                <h2>รายการออเดอร์</h2>
                <button class="btn-refresh" onclick="location.reload()">🔄 รีเฟรช</button>
            </div>

            <div class="orders-grid">
                <?php if(empty($orders)): ?>
                    <div class="no-orders">
                        <p>ไม่มีออเดอร์ในขณะนี้</p>
                    </div>
                <?php else: ?>
                    <?php foreach($orders as $order): ?>
                    <div class="order-card <?php echo $order['OrderStatus'] === 'กำลังทำ' ? 'cooking' : ''; ?>">
                        
                        <div class="order-header">
                            <div class="order-number">ออเดอร์ #<?php echo $order['OrderID']; ?></div>
                            <div class="order-time">
                                <?php 
                                $time = new DateTime($order['OrderDate']);
                                echo $time->format('H:i น.'); 
                                ?>
                            </div>
                        </div>

                        <div class="order-info">
                            <div class="info-row">
                                <span class="label">ประเภท:</span>
                                <span class="value"><?php echo $order['OrderType']; ?></span>
                            </div>
                            <?php if($order['TableNumber']): ?>
                            <div class="info-row">
                                <span class="label">โต๊ะ:</span>
                                <span class="value">#<?php echo $order['TableNumber']; ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="info-row">
                                <span class="label">ลูกค้า:</span>
                                <span class="value"><?php echo htmlspecialchars($order['CustomerName']); ?></span>
                            </div>
                        </div>

                        <div class="order-items">
                            <h4>รายการอาหาร:</h4>
                            <p><?php echo htmlspecialchars($order['Items']); ?></p>
                        </div>

                        <div class="order-actions">
                            <?php if($order['OrderStatus'] === 'รอดำเนินการ'): ?>
                                <button class="btn-action btn-start" 
                                        onclick="updateStatus(<?php echo $order['OrderID']; ?>, 'กำลังทำ')">
                                    เริ่มทำ
                                </button>
                            <?php else: ?>
                                <button class="btn-action btn-complete" 
                                        onclick="updateStatus(<?php echo $order['OrderID']; ?>, 'เสร็จแล้ว')">
                                    ทำเสร็จแล้ว
                                </button>
                            <?php endif; ?>
                        </div>

                        <div class="order-status-badge <?php echo $order['OrderStatus'] === 'กำลังทำ' ? 'cooking' : ''; ?>">
                            <?php echo $order['OrderStatus']; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <script>
        function updateStatus(orderID, status) {
            if(confirm('ยืนยันการเปลี่ยนสถานะเป็น "' + status + '"?')) {
                fetch('../api/orders.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        action: 'update_status',
                        orderID: orderID,
                        status: status
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        alert('อัปเดตสถานะสำเร็จ');
                        location.reload();
                    } else {
                        alert('เกิดข้อผิดพลาด: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('เกิดข้อผิดพลาด');
                    console.error('Error:', error);
                });
            }
        }

        // Auto refresh every 30 seconds
        setInterval(() => location.reload(), 30000);
    </script>
</body>
</html>