<?php
require_once '../config/db_config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header('Location: ../index.php');
    exit;
}

$staffID = $_SESSION['user_id'];
$staffName = $_SESSION['username'];

$readyOrders = getAll($conn, "
    SELECT o.OrderID, o.OrderDate, o.OrderType, o.TableNumber, o.NetAmount,
           c.CustomerName
    FROM `Order` o
    LEFT JOIN Customer c ON o.CustomerID = c.CustomerID
    WHERE o.OrderStatus = 'เสร็จแล้ว'
    ORDER BY o.OrderDate ASC
");

$today = date('Y-m-d');
$stats = getOne($conn, "
    SELECT 
        COUNT(CASE WHEN OrderStatus = 'จ่ายเงินแล้ว' THEN 1 END) as completed,
        COUNT(CASE WHEN OrderStatus IN ('รอดำเนินการ', 'กำลังทำ', 'เสร็จแล้ว') THEN 1 END) as pending,
        COALESCE(SUM(CASE WHEN OrderStatus = 'จ่ายเงินแล้ว' THEN NetAmount END), 0) as total_sales
    FROM `Order`
    WHERE DATE(OrderDate) = ?
", [$today]);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>พนักงานเสิร์ฟ - Staff Dashboard</title>
    <link rel="stylesheet" href="../css/main.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }
        .navbar {
            background: linear-gradient(135deg, #1A1A1A 0%, #2C2C2C 100%);
            padding: 15px 0;
            color: white;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        .navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .nav-brand h1 {
            font-size: 22px;
            margin: 0;
        }
        .nav-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .btn-logout {
            background: #EE1D23;
            color: white;
            padding: 8px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
        }
        .hero {
            background: linear-gradient(135deg, #EE1D23 0%, #C41E3A 100%);
            color: white;
            padding: 50px 0;
            margin-bottom: 40px;
            text-align: center;
        }
        .hero h2 {
            font-size: 32px;
            margin-bottom: 30px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        .stat-card {
            background: rgba(255, 255, 255, 0.15);
            padding: 25px;
            border-radius: 12px;
            text-align: center;
        }
        .stat-number {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 14px;
            opacity: 0.9;
        }
        .orders-section {
            padding: 40px 0;
        }
        .section-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 25px;
            color: #1A1A1A;
        }
        .orders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }
        .order-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border: 2px solid #06C755;
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
            font-size: 22px;
            font-weight: 700;
            color: #EE1D23;
        }
        .order-badge {
            background: #06C755;
            color: white;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
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
        .info-row .price {
            color: #EE1D23;
            font-size: 18px;
        }
        .order-actions {
            display: flex;
            gap: 10px;
        }
        .btn-action {
            flex: 1;
            padding: 12px;
            border-radius: 8px;
            border: none;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-serve {
            background: #06C755;
            color: white;
        }
        .btn-serve:hover {
            background: #05a647;
            transform: scale(1.05);
        }
        .no-orders {
            text-align: center;
            padding: 80px 20px;
            color: #666;
            font-size: 20px;
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
                <h1>🍱 ซากุระ ซูชิ & ราเมง</h1>
            </div>
            <div class="nav-user">
                <span class="user-name"><?php echo htmlspecialchars($staffName); ?> (เสิร์ฟ)</span>
                <a href="../logout.php" class="btn-logout">ออกจากระบบ</a>
            </div>
        </div>
    </nav>

    <section class="hero">
        <div class="container">
            <h2>สถิติวันนี้</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['completed']; ?></div>
                    <div class="stat-label">ออเดอร์เสร็จ</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['pending']; ?></div>
                    <div class="stat-label">กำลังดำเนินการ</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">฿<?php echo number_format($stats['total_sales']); ?></div>
                    <div class="stat-label">ยอดขายวันนี้</div>
                </div>
            </div>
        </div>
    </section>

    <section class="orders-section">
        <div class="container">
            <h3 class="section-title">🔔 ออเดอร์พร้อมเสิร์ฟ (ครัวทำเสร็จแล้ว)</h3>

            <?php if(empty($readyOrders)): ?>
                <div class="no-orders">
                    <p>ไม่มีออเดอร์พร้อมเสิร์ฟในขณะนี้</p>
                </div>
            <?php else: ?>
                <div class="orders-grid">
                    <?php foreach($readyOrders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div class="order-number">ออเดอร์ #<?php echo $order['OrderID']; ?></div>
                            <div class="order-badge">พร้อมเสิร์ฟ</div>
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
                            <div class="info-row">
                                <span class="label">ยอดรวม:</span>
                                <span class="value price">฿<?php echo number_format($order['NetAmount']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="label">เวลา:</span>
                                <span class="value">
                                    <?php 
                                    $time = new DateTime($order['OrderDate']);
                                    echo $time->format('H:i น.'); 
                                    ?>
                                </span>
                            </div>
                        </div>

                        <div class="order-actions">
                            <button class="btn-action btn-serve" 
                                    onclick="serveOrder(<?php echo $order['OrderID']; ?>)">
                                เสิร์ฟแล้ว
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <script>
        function serveOrder(orderID) {
            if(confirm('ยืนยันการเสิร์ฟอาหารแล้ว?')) {
                fetch('../api/orders.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        action: 'update_status',
                        orderID: orderID,
                        status: 'เสิร์ฟแล้ว'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        alert('บันทึกสำเร็จ');
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