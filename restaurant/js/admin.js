// ========================================
// JavaScript สำหรับ Admin (เจ้าของร้าน)
// ========================================

// === Menu Management ===

function openAddModal() {
    document.getElementById('modalTitle').textContent = 'เพิ่มเมนูใหม่';
    document.getElementById('formAction').value = 'add';
    document.getElementById('menuForm').reset();
    document.getElementById('imagePreview').innerHTML = '';
    document.getElementById('menuModal').classList.add('active');
}

function openEditModal(menu) {
    document.getElementById('modalTitle').textContent = 'แก้ไขเมนู';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('menuID').value = menu.MenuID;
    document.getElementById('menuName').value = menu.MenuName;
    document.getElementById('menuNameEng').value = menu.MenuNameEng;
    document.getElementById('price').value = menu.Price;
    document.getElementById('category').value = menu.Category;
    document.getElementById('description').value = menu.Description || '';
    document.getElementById('isSignature').checked = menu.IsSignature == 1;
    document.getElementById('isAvailable').checked = menu.IsAvailable == 1;
    document.getElementById('currentImage').value = menu.ImageURL || '';
    
    // แสดงรูปภาพปัจจุบัน
    if (menu.ImageURL) {
        document.getElementById('imagePreview').innerHTML = 
            `<img src="../images/menu/${menu.ImageURL}" alt="Current">`;
    }
    
    document.getElementById('menuModal').classList.add('active');
}

function closeModal() {
    document.getElementById('menuModal').classList.remove('active');
}

function deleteMenu(menuID, menuName) {
    if (confirm(`ยืนยันการลบเมนู "${menuName}"?`)) {
        document.getElementById('deleteMenuID').value = menuID;
        document.getElementById('deleteForm').submit();
    }
}

// === Stock Management ===

function openAddStockModal() {
    document.getElementById('modalTitle').textContent = 'เพิ่มวัตถุดิบใหม่';
    document.getElementById('formAction').value = 'add';
    document.getElementById('stockForm').reset();
    document.getElementById('stockModal').classList.add('active');
}

function openEditStockModal(ingredient) {
    document.getElementById('modalTitle').textContent = 'แก้ไขวัตถุดิบ';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('ingredientID').value = ingredient.IngredientID;
    document.getElementById('ingredientName').value = ingredient.IngredientName;
    document.getElementById('unit').value = ingredient.Unit;
    document.getElementById('stockQuantity').value = ingredient.StockQuantity;
    document.getElementById('minimumStock').value = ingredient.MinimumStock;
    document.getElementById('unitPrice').value = ingredient.UnitPrice;
    document.getElementById('stockModal').classList.add('active');
}

function closeStockModal() {
    document.getElementById('stockModal').classList.remove('active');
}

function openRestockModal(ingredient) {
    document.getElementById('restockID').value = ingredient.IngredientID;
    document.getElementById('restockName').textContent = ingredient.IngredientName;
    document.getElementById('restockCurrent').textContent = 
        `${ingredient.StockQuantity} ${ingredient.Unit}`;
    document.getElementById('restockModal').classList.add('active');
}

function closeRestockModal() {
    document.getElementById('restockModal').classList.remove('active');
}

function deleteStock(ingredientID, ingredientName) {
    if (confirm(`ยืนยันการลบวัตถุดิบ "${ingredientName}"?\n\nการลบจะมีผลทันที`)) {
        document.getElementById('deleteIngredientID').value = ingredientID;
        document.getElementById('deleteForm').submit();
    }
}

// === Kitchen Functions ===

async function updateOrderStatus(orderID, status) {
    try {
        const response = await fetch('../api/orders.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'update_status',
                orderID: orderID,
                status: status
            })
        });

        const result = await response.json();

        if (result.success) {
            showNotification(`อัปเดตสถานะเป็น "${status}" แล้ว`);
            
            // Reload page after 1 second
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            alert('เกิดข้อผิดพลาด: ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('เกิดข้อผิดพลาดในการอัปเดตสถานะ');
    }
}

// === Staff Functions ===

async function serveOrder(orderID) {
    if (confirm('ยืนยันการเสิร์ฟอาหาร?')) {
        await updateOrderStatus(orderID, 'เสิร์ฟแล้ว');
    }
}

async function viewOrderDetail(orderID) {
    try {
        const response = await fetch(`../api/orders.php?action=get_order_detail&orderID=${orderID}`);
        const result = await response.json();

        if (result.success) {
            showOrderDetailModal(result.order, result.items);
        } else {
            alert('ไม่พบข้อมูลออเดอร์');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('เกิดข้อผิดพลาด');
    }
}

function showOrderDetailModal(order, items) {
    const itemsList = items.map(item => 
        `<li>${item.Quantity}x ${item.MenuName} - ฿${item.SubTotal}</li>`
    ).join('');

    const modal = document.createElement('div');
    modal.className = 'modal active';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3>รายละเอียดออเดอร์ #${order.OrderID}</h3>
                <button class="btn-close-modal" onclick="this.closest('.modal').remove()">✕</button>
            </div>
            <div style="padding: 20px;">
                <div class="order-detail-info">
                    <p><strong>ลูกค้า:</strong> ${order.CustomerName}</p>
                    <p><strong>เบอร์โทร:</strong> ${order.PhoneNumber}</p>
                    <p><strong>ประเภท:</strong> ${order.OrderType}</p>
                    <p><strong>สถานะ:</strong> ${order.OrderStatus}</p>
                    <p><strong>วันที่:</strong> ${new Date(order.OrderDate).toLocaleString('th-TH')}</p>
                </div>
                <h4 style="margin-top: 20px;">รายการอาหาร:</h4>
                <ul style="list-style: none; padding: 0;">
                    ${itemsList}
                </ul>
                <div style="margin-top: 20px; padding-top: 20px; border-top: 2px solid #F5F5F5;">
                    <p style="font-size: 18px; font-weight: 700;">
                        ยอดรวม: <span style="color: #EE1D23;">฿${order.NetAmount}</span>
                    </p>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

function refreshPage() {
    location.reload();
}

// === Auto Refresh for Kitchen/Staff (every 30 seconds) ===
if (window.location.pathname.includes('kitchen/') || window.location.pathname.includes('staff/')) {
    setInterval(() => {
        location.reload();
    }, 30000); // 30 seconds
}

// === Notification ===
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
    }, 3000);
}

// === Close modals on ESC key ===
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modals = document.querySelectorAll('.modal.active');
        modals.forEach(modal => modal.classList.remove('active'));
    }
});

// === Update Stats (for kitchen dashboard) ===
function updateKitchenStats() {
    const pendingCards = document.querySelectorAll('.order-card.pending');
    const cookingCards = document.querySelectorAll('.order-card.cooking');
    
    const pendingCount = document.getElementById('pendingCount');
    const cookingCount = document.getElementById('cookingCount');
    
    if (pendingCount) pendingCount.textContent = pendingCards.length;
    if (cookingCount) cookingCount.textContent = cookingCards.length;
}

// Update stats on page load
if (document.getElementById('pendingCount')) {
    updateKitchenStats();
}

// === Refresh button handler ===
const btnRefresh = document.getElementById('btnRefresh');
if (btnRefresh) {
    btnRefresh.addEventListener('click', function() {
        location.reload();
    });
}

console.log('Admin JS loaded successfully');