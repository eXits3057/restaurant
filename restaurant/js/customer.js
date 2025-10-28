// ========================================
// JavaScript สำหรับหน้าลูกค้า (Customer)
// ระบบตะกร้าสินค้าและการสั่งอาหาร
// ========================================

let cart = [];

// === Initialize ===
document.addEventListener('DOMContentLoaded', function() {
    initCategoryFilter();
    initCartButtons();
    initAddToCartButtons();
    loadCartFromStorage();
});

// === Category Filter ===
function initCategoryFilter() {
    const tabs = document.querySelectorAll('.tab-btn');
    const menuCards = document.querySelectorAll('.menu-card');

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Remove active class
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            const category = this.getAttribute('data-category');

            // Filter menu
            menuCards.forEach(card => {
                if (category === 'all') {
                    card.style.display = 'block';
                } else {
                    const cardCategory = card.getAttribute('data-category');
                    card.style.display = cardCategory === category ? 'block' : 'none';
                }
            });
        });
    });
}

// === Cart Buttons ===
function initCartButtons() {
    const cartFloat = document.getElementById('cartFloat');
    const btnCloseCart = document.getElementById('btnCloseCart');
    const overlay = document.getElementById('overlay');
    const btnCheckout = document.getElementById('btnCheckout');

    if (cartFloat) {
        cartFloat.addEventListener('click', toggleCart);
    }

    if (btnCloseCart) {
        btnCloseCart.addEventListener('click', toggleCart);
    }

    if (overlay) {
        overlay.addEventListener('click', toggleCart);
    }

    if (btnCheckout) {
        btnCheckout.addEventListener('click', checkout);
    }
}

// === Toggle Cart ===
function toggleCart() {
    const cartSidebar = document.getElementById('cartSidebar');
    const overlay = document.getElementById('overlay');

    cartSidebar.classList.toggle('active');
    overlay.classList.toggle('active');
}

// === Add to Cart Buttons ===
function initAddToCartButtons() {
    const buttons = document.querySelectorAll('.btn-add-cart');

    buttons.forEach(btn => {
        btn.addEventListener('click', function() {
            const menuID = parseInt(this.getAttribute('data-id'));
            const menuName = this.getAttribute('data-name');
            const price = parseFloat(this.getAttribute('data-price'));

            addToCart(menuID, menuName, price);
        });
    });
}

// === Add to Cart ===
function addToCart(id, name, price) {
    const existingItem = cart.find(item => item.id === id);

    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            id: id,
            name: name,
            price: price,
            quantity: 1
        });
    }

    updateCartDisplay();
    saveCartToStorage();
    showNotification(`เพิ่ม ${name} ลงตะกร้าแล้ว`);
}

// === Update Cart Display ===
function updateCartDisplay() {
    const cartBadge = document.getElementById('cartBadge');
    const cartBody = document.getElementById('cartBody');
    const totalAmount = document.getElementById('totalAmount');

    // Update badge
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    cartBadge.textContent = totalItems;

    // Update cart body
    if (cart.length === 0) {
        cartBody.innerHTML = '<div class="cart-empty"><p>ยังไม่มีรายการอาหาร</p></div>';
    } else {
        cartBody.innerHTML = cart.map(item => `
            <div class="cart-item">
                <div class="cart-item-info">
                    <span class="cart-item-name">${item.name}</span>
                    <span class="cart-item-price">฿${item.price.toLocaleString()}</span>
                </div>
                <div class="cart-item-controls">
                    <button class="qty-btn" onclick="updateQuantity(${item.id}, -1)">−</button>
                    <span class="qty-display">${item.quantity}</span>
                    <button class="qty-btn" onclick="updateQuantity(${item.id}, 1)">+</button>
                    <button class="btn-remove" onclick="removeFromCart(${item.id})">🗑️</button>
                </div>
            </div>
        `).join('');
    }

    // Update total
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    totalAmount.textContent = `฿${total.toLocaleString()}`;
}

// === Update Quantity ===
function updateQuantity(id, change) {
    const item = cart.find(item => item.id === id);
    if (item) {
        item.quantity += change;
        if (item.quantity <= 0) {
            removeFromCart(id);
        } else {
            updateCartDisplay();
            saveCartToStorage();
        }
    }
}

// === Remove from Cart ===
function removeFromCart(id) {
    cart = cart.filter(item => item.id !== id);
    updateCartDisplay();
    saveCartToStorage();
}

// === Checkout ===
async function checkout() {
    if (cart.length === 0) {
        alert('กรุณาเลือกรายการอาหารก่อนยืนยันออเดอร์');
        return;
    }

    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const orderDetails = cart.map(item => `${item.name} x${item.quantity}`).join(', ');

    const confirmation = confirm(
        `ยืนยันออเดอร์\n\n` +
        `รายการ: ${orderDetails}\n\n` +
        `ยอดรวม: ฿${total.toLocaleString()}\n\n` +
        `กดตกลงเพื่อยืนยันออเดอร์`
    );

    if (confirmation) {
        try {
            // Send order to server
            const response = await fetch('../api/orders.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'create_order',
                    items: cart,
                    total: total
                })
            });

            const result = await response.json();

            if (result.success) {
                showSuccessMessage(result.orderID);
                cart = [];
                updateCartDisplay();
                saveCartToStorage();
                toggleCart();
            } else {
                alert('เกิดข้อผิดพลาด: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('เกิดข้อผิดพลาดในการสั่งอาหาร');
        }
    }
}

// === Show Success Message ===
function showSuccessMessage(orderID) {
    const message = document.createElement('div');
    message.style.cssText = `
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        padding: 40px;
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        z-index: 10000;
        text-align: center;
        min-width: 300px;
    `;
    
    message.innerHTML = `
        <div style="font-size: 64px; margin-bottom: 20px;">✅</div>
        <h2 style="color: #EE1D23; margin-bottom: 10px;">สั่งอาหารสำเร็จ!</h2>
        <p style="font-size: 18px; color: #4A4A4A;">หมายเลขออเดอร์: #${orderID}</p>
        <p style="font-size: 14px; color: #4A4A4A; margin-top: 10px;">กำลังส่งไปครัว...</p>
    `;

    const overlay = document.createElement('div');
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 9999;
    `;

    document.body.appendChild(overlay);
    document.body.appendChild(message);

    setTimeout(() => {
        message.remove();
        overlay.remove();
        // Redirect to order status page
        window.location.href = 'order-status.php';
    }, 2000);
}

// === Show Notification ===
function showNotification(text) {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 100px;
        right: 30px;
        background: #1A1A1A;
        color: white;
        padding: 15px 25px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        z-index: 10000;
        font-size: 14px;
        animation: slideIn 0.3s ease;
    `;
    
    notification.textContent = text;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 2000);
}

// === LocalStorage Functions ===
function saveCartToStorage() {
    localStorage.setItem('cart', JSON.stringify(cart));
}

function loadCartFromStorage() {
    const savedCart = localStorage.getItem('cart');
    if (savedCart) {
        cart = JSON.parse(savedCart);
        updateCartDisplay();
    }
}

// === Add Animation Styles ===
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);