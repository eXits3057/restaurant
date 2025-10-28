// ========================================
// js/kitchen.js - JavaScript สำหรับครัว
// ========================================

// Update order status
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
            // Play notification sound
            playNotificationSound();
            
            // Show success message
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

function playNotificationSound() {
    const sound = document.getElementById('notificationSound');
    if (sound) {
        sound.play().catch(e => console.log('Cannot play sound:', e));
    }
}

function showNotification(text) {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 100px;
        right: 30px;
        background: #06C755;
        color: white;
        padding: 20px 30px;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        z-index: 10000;
        font-size: 18px;
        font-weight: 700;
        animation: slideIn 0.3s ease;
    `;
    
    notification.textContent = text;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Auto refresh every 30 seconds
setInterval(() => {
    location.reload();
}, 30000);

// Update stats
function updateStats() {
    const pending = document.querySelectorAll('.order-card.pending').length;
    const cooking = document.querySelectorAll('.order-card.cooking').length;
    
    const pendingEl = document.getElementById('pendingCount');
    const cookingEl = document.getElementById('cookingCount');
    
    if (pendingEl) pendingEl.textContent = pending;
    if (cookingEl) cookingEl.textContent = cooking;
}

updateStats();

console.log('Kitchen JS loaded');


// ========================================
// js/staff.js - JavaScript สำหรับเสิร์ฟ
// ========================================

async function serveOrder(orderID) {
    if (confirm('ยืนยันการเสิร์ฟอาหารแล้ว?')) {
        try {
            const response = await fetch('../api/orders.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'update_status',
                    orderID: orderID,
                    status: 'เสิร์ฟแล้ว'
                })
            });

            const result = await response.json();

            if (result.success) {
                showStaffNotification('เสิร์ฟอาหารเรียบร้อยแล้ว');
                setTimeout(() => location.reload(), 1000);
            } else {
                alert('เกิดข้อผิดพลาด: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('เกิดข้อผิดพลาด');
        }
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
        `<li style="padding: 8px 0; border-bottom: 1px solid #eee;">
            ${item.Quantity}x ${item.MenuName} 
            <span style="float: right; font-weight: 700;">฿${parseFloat(item.SubTotal).toLocaleString()}</span>
        </li>`
    ).join('');

    const modal = document.createElement('div');
    modal.className = 'modal active';
    modal.style.cssText = `
        display: flex;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 2000;
        align-items: center;
        justify-content: center;
    `;
    
    modal.innerHTML = `
        <div class="modal-content" style="background: white; border-radius: 12px; width: 90%; max-width: 500px; max-height: 80vh; overflow-y: auto;">
            <div class="modal-header" style="padding: 20px; border-bottom: 2px solid #F5F5F5; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="font-size: 22px; font-weight: 700;">รายละเอียดออเดอร์ #${order.OrderID}</h3>
                <button class="btn-close-modal" onclick="this.closest('.modal').remove()" style="background: transparent; border: none; font-size: 28px; cursor: pointer;">✕</button>
            </div>
            <div style="padding: 20px;">
                <div class="order-detail-info" style="background: #F5F5F5; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <p><strong>ลูกค้า:</strong> ${order.CustomerName}</p>
                    <p><strong>เบอร์โทร:</strong> ${order.PhoneNumber}</p>
                    <p><strong>ประเภท:</strong> ${order.OrderType}</p>
                    ${order.TableNumber ? `<p><strong>โต๊ะ:</strong> #${order.TableNumber}</p>` : ''}
                    <p><strong>สถานะ:</strong> ${order.OrderStatus}</p>
                    <p><strong>วันที่:</strong> ${new Date(order.OrderDate).toLocaleString('th-TH')}</p>
                </div>
                <h4 style="margin-bottom: 15px; font-size: 18px;">รายการอาหาร:</h4>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    ${itemsList}
                </ul>
                <div style="margin-top: 20px; padding-top: 20px; border-top: 2px solid #F5F5F5;">
                    <p style="font-size: 20px; font-weight: 700; text-align: right;">
                        ยอดรวม: <span style="color: #EE1D23;">฿${parseFloat(order.NetAmount).toLocaleString()}</span>
                    </p>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Close on background click
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.remove();
        }
    });
}

function showStaffNotification(text) {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 100px;
        right: 30px;
        background: #06C755;
        color: white;
        padding: 15px 25px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        z-index: 10000;
        font-size: 16px;
        font-weight: 600;
    `;
    
    notification.textContent = '✅ ' + text;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transition = 'opacity 0.3s';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

function refreshPage() {
    location.reload();
}

// Auto refresh every 30 seconds
setInterval(() => {
    location.reload();
}, 30000);

console.log('Staff JS loaded');