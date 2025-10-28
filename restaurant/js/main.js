// ========================================
// Main JavaScript - ใช้ร่วมกันทุกหน้า
// ระบบจัดการร้านอาหารญี่ปุ่น
// ========================================

// ========================================
// Main JavaScript - ใช้ร่วมกันทุกหน้า
// ระบบจัดการร้านอาหารญี่ปุ่น
// ========================================

// === Initialize ===
document.addEventListener('DOMContentLoaded', function() {
    console.log('Restaurant Management System - Ready');
    
    // Add smooth scroll behavior
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    // Close modal on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAllModals();
        }
    });
});

// === Modal Functions ===
function closeAllModals() {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.classList.remove('active');
    });
}

// === Format Currency ===
function formatCurrency(amount) {
    return '฿' + parseFloat(amount).toLocaleString('th-TH', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    });
}

// === Format Date/Time ===
function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('th-TH', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('th-TH', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function formatTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleTimeString('th-TH', {
        hour: '2-digit',
        minute: '2-digit'
    });
}

// === Loading Overlay ===
function showLoading() {
    const loading = document.createElement('div');
    loading.id = 'loadingOverlay';
    loading.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    `;
    loading.innerHTML = `
        <div style="background: white; padding: 30px; border-radius: 12px; text-align: center;">
            <div style="font-size: 48px; margin-bottom: 15px;">⏳</div>
            <p style="font-size: 18px; font-weight: 600; color: #1A1A1A;">กำลังโหลด...</p>
        </div>
    `;
    document.body.appendChild(loading);
}

function hideLoading() {
    const loading = document.getElementById('loadingOverlay');
    if (loading) {
        loading.remove();
    }
}

// === Confirm Dialog ===
function confirmDialog(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// === Alert Dialog ===
function alertDialog(message, type = 'info') {
    const colors = {
        success: '#06C755',
        error: '#FF3B30',
        warning: '#FFB800',
        info: '#EE1D23'
    };

    const icons = {
        success: '✅',
        error: '❌',
        warning: '⚠️',
        info: 'ℹ️'
    };

    const alert = document.createElement('div');
    alert.style.cssText = `
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        z-index: 10000;
        text-align: center;
        min-width: 300px;
        max-width: 500px;
    `;
    
    alert.innerHTML = `
        <div style="font-size: 64px; margin-bottom: 15px;">${icons[type]}</div>
        <p style="font-size: 18px; color: #1A1A1A; line-height: 1.6;">${message}</p>
        <button onclick="this.closest('div').remove(); document.getElementById('alertOverlay').remove();" 
                style="margin-top: 20px; padding: 12px 30px; background: ${colors[type]}; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer;">
            ตกลง
        </button>
    `;

    const overlay = document.createElement('div');
    overlay.id = 'alertOverlay';
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
    document.body.appendChild(alert);
}

// === Toast Notification ===
function showToast(message, type = 'success') {
    const colors = {
        success: '#06C755',
        error: '#FF3B30',
        warning: '#FFB800',
        info: '#1A1A1A'
    };

    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: ${colors[type]};
        color: white;
        padding: 15px 25px;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        z-index: 10000;
        font-size: 15px;
        font-weight: 600;
        animation: slideInUp 0.3s ease;
    `;
    
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'slideOutDown 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// === Debounce Function ===
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// === Local Storage Helper ===
const storage = {
    set(key, value) {
        try {
            localStorage.setItem(key, JSON.stringify(value));
        } catch (e) {
            console.error('LocalStorage error:', e);
        }
    },
    
    get(key) {
        try {
            const item = localStorage.getItem(key);
            return item ? JSON.parse(item) : null;
        } catch (e) {
            console.error('LocalStorage error:', e);
            return null;
        }
    },
    
    remove(key) {
        try {
            localStorage.removeItem(key);
        } catch (e) {
            console.error('LocalStorage error:', e);
        }
    },
    
    clear() {
        try {
            localStorage.clear();
        } catch (e) {
            console.error('LocalStorage error:', e);
        }
    }
};

// === API Helper ===
async function apiRequest(url, options = {}) {
    try {
        showLoading();
        
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json'
            }
        };
        
        const response = await fetch(url, { ...defaultOptions, ...options });
        const data = await response.json();
        
        hideLoading();
        
        return data;
    } catch (error) {
        hideLoading();
        console.error('API Error:', error);
        alertDialog('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
        throw error;
    }
}

// === Add CSS Animations ===
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInUp {
        from {
            transform: translateY(100px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutDown {
        from {
            transform: translateY(0);
            opacity: 1;
        }
        to {
            transform: translateY(100px);
            opacity: 0;
        }
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }
    
    @keyframes fadeOut {
        from {
            opacity: 1;
        }
        to {
            opacity: 0;
        }
    }
    
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

// === Export functions ===
window.restaurantApp = {
    formatCurrency,
    formatDateTime,
    formatDate,
    formatTime,
    showLoading,
    hideLoading,
    confirmDialog,
    alertDialog,
    showToast,
    debounce,
    storage,
    apiRequest
};

console.log('Main JS loaded successfully');