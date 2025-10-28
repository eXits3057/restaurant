<?php
require_once '../config/db_config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$customerID = $_SESSION['user_id'];
$customerName = $_SESSION['username'];

$menuItems = getAll($conn, "
    SELECT MenuID, MenuName, MenuNameEng, Price, Category, Description, 
           IsSignature, ImageURL
    FROM Menu 
    WHERE IsAvailable = 1 
    ORDER BY IsSignature DESC, Category, Price
");
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สั่งอาหาร - ซากุระ ซูชิ & ราเมง</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/customer.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <h1>🍱 ซากุระ ซูชิ & ราเมง</h1>
            </div>
            <div class="nav-menu">
                <a href="menu.php" class="nav-link active">เมนูอาหาร</a>
            </div>
            <div class="nav-user">
                <span class="user-name"><?php echo htmlspecialchars($customerName); ?></span>
                <a href="../logout.php" class="btn-logout">ออกจากระบบ</a>
            </div>
        </div>
    </nav>

    <section class="hero">
        <div class="container">
            <h2>สั่งอาหารออนไลน์</h2>
            <p>เลือกเมนูโปรดของคุณ รวดเร็ว สะดวก ปลอดภัย</p>
        </div>
    </section>

    <section class="menu-section">
        <div class="container">
            <h3 class="section-title">เมนูทั้งหมด</h3>
            
            <div class="category-tabs">
                <button class="tab-btn active" data-category="all">ทั้งหมด</button>
                <button class="tab-btn" data-category="ซูชิ">ซูชิ</button>
                <button class="tab-btn" data-category="ราเมง">ราเมง</button>
                <button class="tab-btn" data-category="ข้าวหน้า">ข้าวหน้า</button>
                <button class="tab-btn" data-category="ทอด">ทอด</button>
            </div>

            <div class="menu-grid" id="menuGrid">
                <?php foreach($menuItems as $item): ?>
                <div class="menu-card <?php echo $item['IsSignature'] ? 'signature' : ''; ?>" 
                     data-category="<?php echo htmlspecialchars($item['Category']); ?>">
                    
                    <?php if($item['IsSignature']): ?>
                        <div class="badge-signature">แนะนำ</div>
                    <?php endif; ?>
                    
                    <div class="menu-image">
                        <?php if(!empty($item['ImageURL'])): ?>
                            <img src="../images/menu/<?php echo htmlspecialchars($item['ImageURL']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['MenuName']); ?>">
                        <?php else: ?>
                            <div class="no-image">ไม่มีรูปภาพ</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="menu-info">
                        <h4 class="menu-name"><?php echo htmlspecialchars($item['MenuName']); ?></h4>
                        <p class="menu-name-eng"><?php echo htmlspecialchars($item['MenuNameEng']); ?></p>
                        <p class="menu-desc"><?php echo htmlspecialchars($item['Description']); ?></p>
                        
                        <div class="menu-footer">
                            <span class="menu-price">฿<?php echo number_format($item['Price']); ?></span>
                            <button class="btn-add-cart" 
                                    data-id="<?php echo $item['MenuID']; ?>"
                                    data-name="<?php echo htmlspecialchars($item['MenuName']); ?>"
                                    data-price="<?php echo $item['Price']; ?>">
                                เพิ่ม
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <div class="cart-float" id="cartFloat">
        <div class="cart-badge" id="cartBadge">0</div>
        <span class="cart-icon">🛒</span>
    </div>

    <div class="cart-sidebar" id="cartSidebar">
        <div class="cart-header">
            <h3>ตะกร้าสินค้า</h3>
            <button class="btn-close" id="btnCloseCart">✕</button>
        </div>
        
        <div class="cart-body" id="cartBody">
            <div class="cart-empty">
                <p>ยังไม่มีรายการอาหาร</p>
            </div>
        </div>
        
        <div class="cart-footer">
            <div class="cart-total">
                <span>ยอดรวมทั้งหมด</span>
                <span class="total-amount" id="totalAmount">฿0</span>
            </div>
            <button class="btn-checkout" id="btnCheckout">ยืนยันออเดอร์</button>
        </div>
    </div>

    <div class="overlay" id="overlay"></div>

    <script src="../js/main.js"></script>
    <script src="../js/customer.js"></script>
</body>
</html>