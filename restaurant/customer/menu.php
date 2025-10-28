<?php
require_once '../config/db_config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
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
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <h1>🍱 ซากุระ ซูชิ & ราเมง</h1>
            </div>
            <div class="nav-menu">
                <a href="menu.php" class="nav-link active">
                    <span class="nav-icon">🍜</span>
                    <span>เมนูอาหาร</span>
                </a>
            </div>
            <div class="nav-user">
                <div class="user-info">
                    <span class="user-icon">👤</span>
                    <span class="user-name"><?php echo htmlspecialchars($customerName); ?></span>
                </div>
                <a href="../logout.php" class="btn-logout">ออกจากระบบ</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h2 class="hero-title">สั่งอาหารออนไลน์</h2>
                <p class="hero-subtitle">เลือกเมนูโปรดของคุณ รวดเร็ว สะดวก ปลอดภัย</p>
                <div class="hero-features">
                    <div class="feature-item">
                        <span class="feature-icon">⚡</span>
                        <span>จัดส่งรวดเร็ว</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">🎯</span>
                        <span>คุณภาพดี</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">💰</span>
                        <span>ราคาถูก</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Menu Section -->
    <section class="menu-section">
        <div class="container">
            <!-- Section Header -->
            <div class="menu-header">
                <h3 class="section-title">เมนูทั้งหมด</h3>
                <div class="menu-count">
                    <span id="menuCount"><?php echo count($menuItems); ?></span> รายการ
                </div>
            </div>
            
            <!-- Category Tabs -->
            <div class="category-tabs">
                <button class="tab-btn active" data-category="all">
                    <span class="tab-icon">🍱</span>
                    <span>ทั้งหมด</span>
                </button>
                <button class="tab-btn" data-category="ซูชิ">
                    <span class="tab-icon">🍣</span>
                    <span>ซูชิ</span>
                </button>
                <button class="tab-btn" data-category="ราเมง">
                    <span class="tab-icon">🍜</span>
                    <span>ราเมง</span>
                </button>
                <button class="tab-btn" data-category="ข้าวหน้า">
                    <span class="tab-icon">🍚</span>
                    <span>ข้าวหน้า</span>
                </button>
                <button class="tab-btn" data-category="ทอด">
                    <span class="tab-icon">🍤</span>
                    <span>ทอด</span>
                </button>
            </div>

            <!-- Menu Grid -->
            <div class="menu-grid" id="menuGrid">
                <?php foreach($menuItems as $item): ?>
                <div class="menu-card" data-category="<?php echo htmlspecialchars($item['Category']); ?>">
                    
                    <?php if($item['IsSignature']): ?>
                        <div class="badge-signature">
                            <span class="badge-icon">⭐</span>
                            <span>แนะนำ</span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="menu-image">
                        <?php if(!empty($item['ImageURL'])): ?>
                            <img src="../images/menu/<?php echo htmlspecialchars($item['ImageURL']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['MenuName']); ?>"
                                 loading="lazy">
                            <div class="image-overlay">
                                <span class="overlay-icon">👁️</span>
                            </div>
                        <?php else: ?>
                            <div class="no-image">
                                <span class="no-image-icon">🍱</span>
                                <span>ไม่มีรูปภาพ</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="menu-info">
                        <div class="menu-category-badge">
                            <?php echo htmlspecialchars($item['Category']); ?>
                        </div>
                        <h4 class="menu-name"><?php echo htmlspecialchars($item['MenuName']); ?></h4>
                        <p class="menu-name-eng"><?php echo htmlspecialchars($item['MenuNameEng']); ?></p>
                        <p class="menu-desc"><?php echo htmlspecialchars($item['Description']); ?></p>
                        
                        <div class="menu-footer">
                            <div class="price-container">
                                <span class="menu-price">฿<?php echo number_format($item['Price']); ?></span>
                            </div>
                            <button class="btn-add-cart" 
                                    data-id="<?php echo $item['MenuID']; ?>"
                                    data-name="<?php echo htmlspecialchars($item['MenuName']); ?>"
                                    data-price="<?php echo $item['Price']; ?>">
                                <span class="btn-icon">➕</span>
                                <span>เพิ่ม</span>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Floating Cart Button -->
    <div class="cart-float" id="cartFloat">
        <div class="cart-badge" id="cartBadge">0</div>
        <span class="cart-icon">🛒</span>
        <span class="cart-text">ตะกร้า</span>
    </div>

    <!-- Cart Sidebar -->
    <div class="cart-sidebar" id="cartSidebar">
        <div class="cart-header">
            <h3>
                <span class="cart-header-icon">🛒</span>
                <span>ตะกร้าสินค้า</span>
            </h3>
            <button class="btn-close" id="btnCloseCart">✕</button>
        </div>
        
        <div class="cart-body" id="cartBody">
            <div class="cart-empty">
                <div class="empty-icon">🛒</div>
                <p>ยังไม่มีรายการอาหาร</p>
                <small>เลือกเมนูที่คุณชอบและเพิ่มลงตะกร้า</small>
            </div>
        </div>
        
        <div class="cart-footer">
            <div class="cart-summary">
                <div class="summary-row">
                    <span>รายการทั้งหมด</span>
                    <span id="cartItemCount">0 รายการ</span>
                </div>
                <div class="summary-row total">
                    <span>ยอดรวมทั้งหมด</span>
                    <span class="total-amount" id="totalAmount">฿0</span>
                </div>
            </div>
            <button class="btn-checkout" id="btnCheckout">
                <span class="btn-checkout-icon">✓</span>
                <span>ยืนยันออเดอร์</span>
            </button>
        </div>
    </div>

    <!-- Overlay -->
    <div class="overlay" id="overlay"></div>

    <script src="../js/main.js"></script>
    <script src="../js/customer.js"></script>
</body>
</html>