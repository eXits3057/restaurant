<?php
require_once '../config/db_config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$ownerName = $_SESSION['username'];

// ช่วงเวลา
$period = $_GET['period'] ?? 'today';
$customStart = $_GET['start_date'] ?? date('Y-m-d');
$customEnd = $_GET['end_date'] ?? date('Y-m-d');

// กำหนดช่วงเวลา
switch($period) {
    case 'today':
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d');
        break;
    case 'yesterday':
        $startDate = date('Y-m-d', strtotime('-1 day'));
        $endDate = date('Y-m-d', strtotime('-1 day'));
        break;
    case 'week':
        $startDate = date('Y-m-d', strtotime('-7 days'));
        $endDate = date('Y-m-d');
        break;
    case 'month':
        $startDate = date('Y-m-01');
        $endDate = date('Y-m-d');
        break;
    case 'custom':
        $startDate = $customStart;
        $endDate = $customEnd;
        break;
    default:
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d');
}

// สถิติยอดขาย
$salesStats = getOne($conn, "
    SELECT 
        COUNT(*) as total_orders,
        COALESCE(SUM(NetAmount), 0) as total_sales,
        COALESCE(AVG(NetAmount), 0) as avg_order_value
    FROM `Order`
    WHERE DATE(OrderDate) BETWEEN ? AND ?
    AND OrderStatus = 'จ่ายเงินแล้ว'
", [$startDate, $endDate]);

// ยอดขายแยกตามวัน
$dailySales = getAll($conn, "
    SELECT 
        DATE(OrderDate) as date,
        COUNT(*) as orders,
        COALESCE(SUM(NetAmount), 0) as sales
    FROM `Order`
    WHERE DATE(OrderDate) BETWEEN ? AND ?
    AND OrderStatus = 'จ่ายเงินแล้ว'
    GROUP BY DATE(OrderDate)
    ORDER BY date ASC
", [$startDate, $endDate]);

// เมนูขายดี
$topMenus = getAll($conn, "
    SELECT m.MenuName, m.Price, m.Category,
           SUM(od.Quantity) as total_sold,
           SUM(od.SubTotal) as total_revenue
    FROM OrderDetail od
    JOIN Menu m ON od.MenuID = m.MenuID
    JOIN `Order` o ON od.OrderID = o.OrderID
    WHERE DATE(o.OrderDate) BETWEEN ? AND ?
    AND o.OrderStatus = 'จ่ายเงินแล้ว'
    GROUP BY od.MenuID
    ORDER BY total_sold DESC
    LIMIT 10
", [$startDate, $endDate]);

// ยอดขายตามหมวดหมู่
$categoryStats = getAll($conn, "
    SELECT m.Category,
           COUNT(DISTINCT o.OrderID) as orders,
           SUM(od.Quantity) as items_sold,
           SUM(od.SubTotal) as revenue
    FROM OrderDetail od
    JOIN Menu m ON od.MenuID = m.MenuID
    JOIN `Order` o ON od.OrderID = o.OrderID
    WHERE DATE(o.OrderDate) BETWEEN ? AND ?
    AND o.OrderStatus = 'จ่ายเงินแล้ว'
    GROUP BY m.Category
    ORDER BY revenue DESC
", [$startDate, $endDate]);

// ประเภทการสั่ง
$orderTypes = getAll($conn, "
    SELECT OrderType,
           COUNT(*) as count,
           COALESCE(SUM(NetAmount), 0) as revenue
    FROM `Order`
    WHERE DATE(OrderDate) BETWEEN ? AND ?
    AND OrderStatus = 'จ่ายเงินแล้ว'
    GROUP BY OrderType
", [$startDate, $endDate]);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานยอดขาย - Admin</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <h1>🍱 ซากุระ ซูชิ & ราเมง - ระบบจัดการ</h1>
            </div>
            <div class="nav-menu">
                <a href="dashboard.php" class="nav-link">หน้าหลัก</a>
                <a href="menu-management.php" class="nav-link">จัดการเมนู</a>
                <a href="stock-management.php" class="nav-link">สต๊อกวัตถุดิบ</a>
                <a href="reports.php" class="nav-link active">รายงาน</a>
            </div>
            <div class="nav-user">
                <span class="user-name">👨‍💼 <?php echo htmlspecialchars($ownerName); ?></span>
                <a href="../logout.php" class="btn-logout">ออกจากระบบ</a>
            </div>
        </div>
    </nav>

    <section class="page-header">
        <div class="container">
            <h2>📊 รายงานยอดขาย</h2>
            <div class="period-selector">
                <a href="?period=today" class="period-btn <?php echo $period === 'today' ? 'active' : ''; ?>">วันนี้</a>
                <a href="?period=yesterday" class="period-btn <?php echo $period === 'yesterday' ? 'active' : ''; ?>">เมื่อวาน</a>
                <a href="?period=week" class="period-btn <?php echo $period === 'week' ? 'active' : ''; ?>">7 วันล่าสุด</a>
                <a href="?period=month" class="period-btn <?php echo $period === 'month' ? 'active' : ''; ?>">เดือนนี้</a>
                <button class="period-btn" onclick="showCustomDate()">กำหนดเอง</button>
            </div>
        </div>
    </section>

    <!-- Custom Date Form -->
    <div id="customDateForm" style="display: none;">
        <div class="container">
            <form method="GET" class="date-form">
                <input type="hidden" name="period" value="custom">
                <input type="date" name="start_date" value="<?php echo $customStart; ?>" required>
                <span>ถึง</span>
                <input type="date" name="end_date" value="<?php echo $customEnd; ?>" required>
                <button type="submit" class="btn-primary">ดูรายงาน</button>
            </form>
        </div>
    </div>

    <!-- Stats Overview -->
    <section class="stats-overview">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-number">฿<?php echo number_format($salesStats['total_sales']); ?></div>
                    <div class="stat-label">ยอดขายรวม</div>
                    <div class="stat-detail"><?php echo $salesStats['total_orders']; ?> ออเดอร์</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">📈</div>
                    <div class="stat-number">฿<?php echo number_format($salesStats['avg_order_value']); ?></div>
                    <div class="stat-label">ยอดเฉลี่ยต่อออเดอร์</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">📅</div>
                    <div class="stat-number"><?php echo count($dailySales); ?></div>
                    <div class="stat-label">จำนวนวันที่มีการขาย</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">🍱</div>
                    <div class="stat-number"><?php echo count($topMenus); ?></div>
                    <div class="stat-label">เมนูที่มีการสั่ง</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Charts -->
    <section class="content-section">
        <div class="container">
            <div class="charts-grid">
                <!-- Daily Sales Chart -->
                <div class="chart-box">
                    <h3>📊 ยอดขายรายวัน</h3>
                    <canvas id="dailySalesChart"></canvas>
                </div>

                <!-- Category Chart -->
                <div class="chart-box">
                    <h3>🎯 ยอดขายตามหมวดหมู่</h3>
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>

            <!-- Top Menu Table -->
            <div class="content-box">
                <h3>🏆 เมนูขายดี Top 10</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>อันดับ</th>
                            <th>เมนู</th>
                            <th>หมวดหมู่</th>
                            <th>ราคา</th>
                            <th>จำนวนขาย</th>
                            <th>รายได้</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $rank = 1; foreach($topMenus as $menu): ?>
                        <tr>
                            <td class="rank">#<?php echo $rank++; ?></td>
                            <td><strong><?php echo htmlspecialchars($menu['MenuName']); ?></strong></td>
                            <td><?php echo htmlspecialchars($menu['Category']); ?></td>
                            <td>฿<?php echo number_format($menu['Price']); ?></td>
                            <td><?php echo $menu['total_sold']; ?> จาน</td>
                            <td class="price">฿<?php echo number_format($menu['total_revenue']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Category Stats -->
            <div class="content-box">
                <h3>📦 สถิติตามหมวดหมู่</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>หมวดหมู่</th>
                            <th>จำนวนออเดอร์</th>
                            <th>จำนวนจานที่ขาย</th>
                            <th>รายได้</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($categoryStats as $cat): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($cat['Category']); ?></strong></td>
                            <td><?php echo $cat['orders']; ?> ออเดอร์</td>
                            <td><?php echo $cat['items_sold']; ?> จาน</td>
                            <td class="price">฿<?php echo number_format($cat['revenue']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Order Types -->
            <div class="content-box">
                <h3>🎯 ประเภทการสั่ง</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ประเภท</th>
                            <th>จำนวนออเดอร์</th>
                            <th>รายได้</th>
                            <th>สัดส่วน</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($orderTypes as $type): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($type['OrderType']); ?></td>
                            <td><?php echo $type['count']; ?> ออเดอร์</td>
                            <td class="price">฿<?php echo number_format($type['revenue']); ?></td>
                            <td><?php echo $salesStats['total_sales'] > 0 ? round(($type['revenue'] / $salesStats['total_sales']) * 100, 1) : 0; ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <script>
        // Daily Sales Chart
        const dailyCtx = document.getElementById('dailySalesChart').getContext('2d');
        new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($dailySales, 'date')); ?>,
                datasets: [{
                    label: 'ยอดขาย (บาท)',
                    data: <?php echo json_encode(array_column($dailySales, 'sales')); ?>,
                    borderColor: '#EE1D23',
                    backgroundColor: 'rgba(238, 29, 35, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: true }
                }
            }
        });

        // Category Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($categoryStats, 'Category')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($categoryStats, 'revenue')); ?>,
                    backgroundColor: [
                        '#EE1D23', '#FF4757', '#C41E3A', 
                        '#FFB800', '#06C755', '#4A4A4A'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        function showCustomDate() {
            document.getElementById('customDateForm').style.display = 'block';
        }
    </script>
</body>
</html>