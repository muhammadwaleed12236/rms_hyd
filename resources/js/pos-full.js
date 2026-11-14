// ===== STATE MANAGEMENT =====
const cart = [];
const payments = [];
let selectedSaleType = null;

// ===== DOM ELEMENTS =====
const productCards = document.querySelectorAll('.product-card');
const cartItemsContainer = document.getElementById('cartItems');
const cartCount = document.getElementById('cartCount');
const subtotalEl = document.getElementById('subtotal');
const totalEl = document.getElementById('total');
const clearCartBtn = document.getElementById('clearCartBtn');
const checkoutBtn = document.getElementById('checkoutBtn');
const toastEl = document.getElementById('toast');

// Search elements (POS)
const posSearchInput = document.getElementById('posSearchInput');
const noResultsMessage = document.getElementById('noResultsMessage');

if (posSearchInput) {
    posSearchInput.addEventListener('input', (e) => {
        const q = String(e.target.value || '').trim().toLowerCase();
        const cards = document.querySelectorAll('.product-card');
        let visible = 0;
        cards.forEach(card => {
            const name = String(card.dataset.itemName || card.getAttribute('data-item-name') || '').toLowerCase();
            const category = String(card.dataset.category || card.getAttribute('data-category') || '').toLowerCase();
            if (!q || name.includes(q) || category.includes(q)) {
                card.style.display = '';
                visible += 1;
            } else {
                card.style.display = 'none';
            }
        });
        if (noResultsMessage) {
            noResultsMessage.classList.toggle('hidden', visible > 0);
        }
    });
}

// Search icon focus (if present)
const posSearchIcon = document.getElementById('posSearchIcon');
if (posSearchIcon && posSearchInput) {
    posSearchIcon.addEventListener('click', () => {
        posSearchInput.focus();
        posSearchInput.select();
    });
}

// Order type elements
const saleTypeButtons = document.querySelectorAll('.sale-type-btn');
const deliverySection = document.getElementById('deliverySection');
const customerNameInput = document.getElementById('customerName');
const customerPhoneInput = document.getElementById('customerPhone');
const customerAddressInput = document.getElementById('customerAddress');
const deliveryChargeInput = document.getElementById('deliveryCharge');

// Payment elements
const paymentMethodSelect = document.getElementById('paymentMethod');
const paymentAmountInput = document.getElementById('paymentAmount');
const addPaymentRowBtn = document.getElementById('addPaymentRowBtn');
const paymentsList = document.getElementById('paymentsList');
const remainingAmountEl = document.getElementById('remainingAmount');

// Receipt modal
const receiptModal = document.getElementById('receiptModal');
const printableReceipt = document.getElementById('printableReceipt');
const closeReceiptModal = document.getElementById('closeReceiptModal');
const closeReceiptBtn = document.getElementById('closeReceiptBtn');
const printReceiptBtn = document.getElementById('printReceiptBtn');

// ===== UTILITY FUNCTIONS =====
function showToast(message, type = 'success') {
    toastEl.textContent = message;
    toastEl.className = `fixed bottom-4 right-4 px-4 py-3 rounded-lg text-white text-sm shadow-lg z-40 ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    }`;
    toastEl.classList.remove('hidden');
    setTimeout(() => toastEl.classList.add('hidden'), 3000);
}

function formatCurrency(amount) {
    return `Rs. ${Math.round(amount).toLocaleString()}`;
}

// ===== ADD TO CART (SINGLE CLICK WITH DEFAULT SIZE) =====
productCards.forEach(card => {
    card.addEventListener('click', () => {
        const itemId = parseInt(card.dataset.itemId);
        const itemData = window.__ALL_ITEMS_DATA.find(item => item.id === itemId);

        if (!itemData || !itemData.sizes || itemData.sizes.length === 0) {
            showToast('Item has no sizes available', 'error');
            return;
        }

        // Use first size as default
        const defaultSize = itemData.sizes[0];
        
        // Add to cart or increase quantity
        const existingCartItem = cart.find(
            item => item.item_id === itemId && item.size_id === defaultSize.id
        );

        if (existingCartItem) {
            existingCartItem.quantity += 1;
            existingCartItem.total = existingCartItem.quantity * existingCartItem.price;
        } else {
            cart.push({
                item_id: itemId,
                name: itemData.name,
                size: defaultSize.size,
                size_id: defaultSize.id,
                price: defaultSize.price,
                quantity: 1,
                total: defaultSize.price,
            });
        }

        updateCartUI();
        showToast(`${itemData.name} added to cart`);
    });
});

// ===== UPDATE CART UI =====
function updateCartUI() {
    cartCount.textContent = cart.length;

    if (cart.length === 0) {
        cartItemsContainer.innerHTML = `
            <div class="text-center py-8 text-gray-500">
                <p class="text-3xl mb-2">🛒</p>
                <p class="text-sm">Cart is empty</p>
            </div>
        `;
        checkoutBtn.disabled = true;
        return;
    }

    cartItemsContainer.innerHTML = cart.map((item, index) => `
        <div class="flex items-start gap-2 bg-gray-50 p-2 rounded-lg border border-gray-200 hover:border-orange-300 transition">
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-gray-900 truncate">${item.name}</p>
                <p class="text-xs text-gray-500 mb-1">${item.size}</p>
                
                <!-- Size and Quantity Controls -->
                <div class="flex items-center gap-1">
                    <!-- Size Dropdown -->
                    <select class="item-size-select text-xs border border-gray-300 rounded px-1 py-0.5 focus:ring-1 focus:ring-orange-500" data-index="${index}">
                        ${window.__ALL_ITEMS_DATA.find(i => i.id === item.item_id).sizes.map(size => `
                            <option value="${size.id}" ${size.id === item.size_id ? 'selected' : ''}>${size.size}</option>
                        `).join('')}
                    </select>
                    
                    <!-- Quantity Input -->
                    <input type="number" min="1" value="${item.quantity}" class="item-qty text-xs border border-gray-300 rounded px-1 py-0.5 w-12 focus:ring-1 focus:ring-orange-500" data-index="${index}">
                </div>
            </div>
            
            <div class="text-right flex-shrink-0">
                <p class="text-xs font-bold text-orange-600">${formatCurrency(item.total)}</p>
                <button class="remove-item text-xs text-red-500 hover:text-red-700 mt-1 font-semibold" data-index="${index}">
                    ✕
                </button>
            </div>
        </div>
    `).join('');

    // Size change handlers
    document.querySelectorAll('.item-size-select').forEach(select => {
        select.addEventListener('change', (e) => {
            const index = parseInt(e.target.dataset.index);
            const newSizeId = parseInt(e.target.value);
            const itemData = window.__ALL_ITEMS_DATA.find(i => i.id === cart[index].item_id);
            const newSize = itemData.sizes.find(s => s.id === newSizeId);

            cart[index].size_id = newSizeId;
            cart[index].size = newSize.size;
            cart[index].price = newSize.price;
            cart[index].total = cart[index].price * cart[index].quantity;

            updateCartUI();
            updatePaymentUI();
        });
    });

    // Quantity change handlers
    document.querySelectorAll('.item-qty').forEach(input => {
        input.addEventListener('change', (e) => {
            const index = parseInt(e.target.dataset.index);
            const newQty = Math.max(1, parseInt(e.target.value) || 1);
            cart[index].quantity = newQty;
            cart[index].total = cart[index].price * newQty;
            updateCartUI();
            updatePaymentUI();
        });
    });

    // Remove item handlers
    document.querySelectorAll('.remove-item').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const index = parseInt(e.target.dataset.index);
            cart.splice(index, 1);
            updateCartUI();
            updatePaymentUI();
        });
    });

    // Update totals and checkout button
    updateTotals();
    checkoutBtn.disabled = cart.length === 0 || !selectedSaleType;
}

function updateTotals() {
    const subtotal = cart.reduce((sum, item) => sum + item.total, 0);
    const deliveryCharge = selectedSaleType === 'delivery' ? parseFloat(deliveryChargeInput.value || 0) : 0;
    const total = subtotal + deliveryCharge;
    subtotalEl.textContent = formatCurrency(subtotal);
    totalEl.textContent = formatCurrency(total);
}

// ===== ORDER TYPE SELECTION =====
saleTypeButtons.forEach(button => {
    button.addEventListener('click', () => {
        saleTypeButtons.forEach(btn => {
            btn.classList.remove('border-gray-600', 'border-2', 'bg-gray-100', 'text-gray-900');
            btn.classList.add('border-gray-300', 'border-2');
        });

        button.classList.remove('border-gray-300');
        button.classList.add('border-gray-600', 'border-2', 'bg-gray-100', 'text-gray-900');

        selectedSaleType = button.dataset.type;

        // Show/hide delivery section
        if (selectedSaleType === 'delivery') {
            deliverySection.classList.remove('hidden');
        } else {
            deliverySection.classList.add('hidden');
            customerNameInput.value = '';
            customerPhoneInput.value = '';
            customerAddressInput.value = '';
            deliveryChargeInput.value = '';
        }

        updateTotals();
        checkoutBtn.disabled = cart.length === 0;
    });
});

// ===== PAYMENT MANAGEMENT =====
function updatePaymentUI() {
    const orderTotalBase = cart.reduce((sum, item) => sum + item.total, 0);
    const deliveryCharge = selectedSaleType === 'delivery' ? parseFloat(deliveryChargeInput.value || 0) : 0;
    const orderTotal = orderTotalBase + deliveryCharge;
    const paymentTotal = payments.reduce((sum, p) => sum + p.amount, 0);
    const remaining = Math.max(0, orderTotal - paymentTotal);

    // Update payments list
    paymentsList.innerHTML = payments.map((payment, index) => `
        <div class="flex justify-between items-center text-xs bg-white border border-gray-200 p-1 rounded">
            <span>
                <span class="font-semibold">${payment.method.charAt(0).toUpperCase() + payment.method.slice(1)}</span>
                <span class="text-gray-500 ml-1">Rs. ${Math.round(payment.amount)}</span>
            </span>
            <button class="remove-payment text-red-500 hover:text-red-700 font-bold" data-index="${index}">✕</button>
        </div>
    `).join('');

    if (payments.length === 0) {
        paymentsList.innerHTML = '<p class="text-xs text-gray-400 text-center py-1">No payments</p>';
    }

    // Remove payment handlers
    document.querySelectorAll('.remove-payment').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const index = parseInt(e.target.dataset.index);
            payments.splice(index, 1);
            updatePaymentUI();
        });
    });

    remainingAmountEl.textContent = formatCurrency(remaining);
}

addPaymentRowBtn.addEventListener('click', () => {
    const amount = parseFloat(paymentAmountInput.value);
    const method = paymentMethodSelect.value;

    if (!amount || amount <= 0) {
        showToast('Please enter a valid amount', 'error');
        return;
    }

    const orderTotalBase = cart.reduce((sum, item) => sum + item.total, 0);
    const deliveryCharge = selectedSaleType === 'delivery' ? parseFloat(deliveryChargeInput.value || 0) : 0;
    const orderTotal = orderTotalBase + deliveryCharge;
    const paymentTotal = payments.reduce((sum, p) => sum + p.amount, 0);

    if (paymentTotal + amount > orderTotal) {
        showToast('Payment exceeds total amount', 'error');
        return;
    }

    payments.push({
        method: method,
        amount: amount,
    });

    paymentAmountInput.value = '';
    updatePaymentUI();
});

// ===== DELIVERY CHARGE =====
deliveryChargeInput.addEventListener('change', () => {
    updateTotals();
});

deliveryChargeInput.addEventListener('input', () => {
    updateTotals();
});

// ===== CLEAR CART =====
clearCartBtn.addEventListener('click', () => {
    if (cart.length === 0) return;
    if (!confirm('Are you sure you want to clear the cart?')) return;

    cart.length = 0;
    payments.length = 0;
    saleTypeButtons.forEach(btn => {
        btn.classList.remove('border-gray-600', 'border-2', 'bg-gray-100', 'text-gray-900');
        btn.classList.add('border-gray-300', 'border-2');
    });
    selectedSaleType = null;
    updateCartUI();
    updatePaymentUI();
    showToast('Cart cleared');
});

// ===== CHECKOUT =====
checkoutBtn.addEventListener('click', async () => {
    // Validation
    if (cart.length === 0) {
        showToast('Cart is empty', 'error');
        return;
    }

    if (!selectedSaleType) {
        showToast('Please select an order type', 'error');
        return;
    }

    if (selectedSaleType === 'delivery') {
        if (!customerNameInput.value.trim()) {
            showToast('Please enter customer name', 'error');
            return;
        }
        if (!customerPhoneInput.value.trim()) {
            showToast('Please enter phone number', 'error');
            return;
        }
        if (!customerAddressInput.value.trim()) {
            showToast('Please enter delivery address', 'error');
            return;
        }
    }

    const orderTotal = cart.reduce((sum, item) => sum + item.total, 0);
    const deliveryCharge = selectedSaleType === 'delivery' ? parseFloat(deliveryChargeInput.value || 0) : 0;
    const totalWithDelivery = orderTotal + deliveryCharge;
    const paymentTotal = payments.reduce((sum, p) => sum + p.amount, 0);

    // Optional: Allow checkout without full payment if needed
    // For now, allow checkout with any payment status
    if (paymentTotal > totalWithDelivery) {
        showToast('Payment total exceeds order amount', 'error');
        return;
    }

    checkoutBtn.disabled = true;

    try {
        const response = await fetch('/api/sales', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.CSRF_TOKEN,
            },
            body: JSON.stringify({
                items: cart.map(item => ({
                    item_id: item.item_id,
                    size_id: item.size_id,
                    quantity: item.quantity,
                    price: item.price,
                })),
                sale_type: selectedSaleType,
                customer_name: selectedSaleType === 'delivery' ? customerNameInput.value : null,
                customer_phone: selectedSaleType === 'delivery' ? customerPhoneInput.value : null,
                customer_address: selectedSaleType === 'delivery' ? customerAddressInput.value : null,
                delivery_charge: deliveryCharge,
                payments: payments.length > 0 ? payments : null,
                total_amount: totalWithDelivery,
            }),
        });

        const data = await response.json();

        if (!response.ok) {
            showToast(data.message || 'Checkout failed', 'error');
            checkoutBtn.disabled = false;
            return;
        }

        // Generate and show receipt
        generateReceipt(data, cart, payments, totalWithDelivery, deliveryCharge);
        
        // Clear cart and reset
        cart.length = 0;
        payments.length = 0;
        saleTypeButtons.forEach(btn => {
            btn.classList.remove('border-gray-600', 'border-2', 'bg-gray-100', 'text-gray-900');
            btn.classList.add('border-gray-300', 'border-2');
        });
        selectedSaleType = null;
        customerNameInput.value = '';
        customerPhoneInput.value = '';
        customerAddressInput.value = '';
        deliveryChargeInput.value = '';
        deliverySection.classList.add('hidden');
        
        updateCartUI();
        updatePaymentUI();
        showToast('Order completed successfully');

    } catch (error) {
        console.error('Checkout error:', error);
        showToast('Error processing checkout', 'error');
    } finally {
        checkoutBtn.disabled = false;
    }
});

// ===== RECEIPT GENERATION =====
function generateReceipt(sale, cartItems, paymentList, total, deliveryCharge = 0) {
    const subtotal = cartItems.reduce((sum, item) => sum + (item.price * (item.quantity || 1)), 0);
    const paid = paymentList.reduce((sum, p) => sum + p.amount, 0);
    const remaining = Math.max(0, total - paid);
    
    // Helper to escape HTML
    const escapeHtml = (text) => {
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return String(text || '').replace(/[&<>"']/g, m => map[m]);
    };

    // Store sale data for later use in printing
    window.currentReceiptSale = { sale, cartItems, paymentList, total, deliveryCharge, subtotal, paid, remaining, escapeHtml };

    // Thermal receipt format optimized for 80mm POS printer (CUSTOMER COPY)
    const receiptHTML = `
        <div style="font-family:'Courier New', 'Courier', monospace;font-size:11px;line-height:1.4;width:80mm;margin:0;padding:0;">
            <!-- HEADER -->
            <div style="text-align:center;border-bottom:2px solid #000;padding:8px 0;margin-bottom:8px;">
                <div style="font-weight:700;font-size:18px;letter-spacing:2px;margin:0;padding:0;">ABQ</div>
                <div style="font-size:9px;margin-top:2px;margin:0;padding:0;">RESTAURANT</div>
                <div style="font-size:9px;margin:0;padding:0;">--- CUSTOMER COPY ---</div>
            </div>
            
            <!-- INVOICE NUMBER -->
            <div style="text-align:center;font-weight:700;font-size:13px;letter-spacing:2px;margin-bottom:8px;margin:0;padding:0;">
                ${sale.invoice_number}
            </div>
            
            <!-- DETAILS -->
            <div style="font-size:10px;margin-bottom:8px;border-bottom:1px dashed #000;padding-bottom:6px;margin:0;padding:0 0 6px 0;">
                <div style="margin:2px 0;">Date: ${new Date(sale.created_at).toLocaleString('en-US', {month: '2-digit', day: '2-digit', year: '2-digit', hour: '2-digit', minute: '2-digit'})}</div>
                <div style="margin:2px 0;">Type: ${sale.type.replace(/_/g, ' ').toUpperCase()}</div>
                <div style="margin:2px 0;">Cust: ${sale.customer_name ? escapeHtml(sale.customer_name.substring(0, 22)) : 'Walk-in'}</div>
            </div>
            
            <!-- ITEMS HEADER -->
            <div style="font-size:10px;font-weight:700;margin-bottom:4px;border-bottom:1px solid #000;padding-bottom:2px;margin:0;padding:2px 0;">
                <div style="display:grid;grid-template-columns:1.5fr 0.5fr 1fr;gap:4px;margin:0;padding:0;">
                    <span>Item</span>
                    <span style="text-align:center;">Qty</span>
                    <span style="text-align:right;">Total</span>
                </div>
            </div>
            
            <!-- ITEMS -->
            <div style="margin-bottom:6px;padding-bottom:6px;border-bottom:1px dashed #000;font-size:10px;margin:0;padding:0 0 6px 0;">
                ${cartItems.map(item => {
                    const qty = item.quantity || 1;
                    const unitPrice = parseFloat(item.price || 0);
                    const lineTotal = unitPrice * qty;
                    return `<div style="display:grid;grid-template-columns:1.5fr 0.5fr 1fr;gap:4px;margin-bottom:2px;margin:0;padding:0;">
                        <span>${escapeHtml(item.name.substring(0, 18))}</span>
                        <span style="text-align:center;">${qty}</span>
                        <span style="text-align:right;font-weight:700;">${Math.round(lineTotal)}</span>
                    </div>`;
                }).join('')}
            </div>
            
            <!-- TOTALS -->
            <div style="border-bottom:2px solid #000;padding-bottom:6px;margin-bottom:8px;font-size:11px;font-weight:700;margin:0;padding:0 0 6px 0;">
                <div style="display:flex;justify-content:space-between;margin-bottom:3px;margin:0;padding:0;">
                    <span>Subtotal:</span>
                    <span>Rs. ${Math.round(subtotal)}</span>
                </div>
                ${deliveryCharge > 0 ? `<div style="display:flex;justify-content:space-between;margin-bottom:3px;margin:0;padding:0;">
                    <span>Delivery:</span>
                    <span>Rs. ${Math.round(deliveryCharge)}</span>
                </div>` : ''}
                <div style="display:flex;justify-content:space-between;margin-bottom:3px;margin:0;padding:0;">
                    <span>TOTAL:</span>
                    <span>Rs. ${Math.round(total)}</span>
                </div>
                <div style="display:flex;justify-content:space-between;margin-bottom:3px;margin:0;padding:0;">
                    <span>PAID:</span>
                    <span>Rs. ${Math.round(paid)}</span>
                </div>
                <div style="display:flex;justify-content:space-between;color:${remaining > 0 ? '#000' : '#000'};margin:0;padding:0;">
                    <span>DUE:</span>
                    <span style="text-decoration:${remaining > 0 ? 'underline' : 'none'};">Rs. ${Math.round(remaining)}</span>
                </div>
            </div>
            
            <!-- KITCHEN TOKEN -->
            <div style="text-align:center;margin-top:8px;padding-top:8px;border-top:2px solid #000;margin:0;padding:8px 0 0 0;">
                <div style="font-size:10px;margin-bottom:4px;font-weight:700;margin:0;padding:0;">--- KITCHEN TOKEN ---</div>
                <div style="font-weight:700;font-size:16px;letter-spacing:2px;margin:6px 0;border:1px solid #000;padding:4px;margin:6px 0;">
                    ${sale.invoice_number}
                </div>
                <div style="font-size:9px;margin-bottom:4px;margin:0;padding:0;">
                    ${new Date(sale.created_at).toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit'})}
                </div>
                <div style="border-top:1px dashed #000;border-bottom:1px dashed #000;padding:6px 0;font-size:10px;text-align:left;margin-bottom:6px;margin:6px 0;">
                    ${cartItems.map(item => {
                        const qty = item.quantity || 1;
                        return `<div style="margin:2px 0;">• ${escapeHtml(item.name.substring(0, 26))} (${qty})</div>`;
                    }).join('')}
                </div>
                <div style="font-size:9px;margin-top:4px;">
                    Thank you!
                </div>
            </div>
            
            <!-- FOOTER -->
            <div style="text-align:center;margin-top:8px;padding-top:8px;border-top:2px solid #000;font-size:9px;margin:0;padding:8px 0 0 0;">
                <div style="letter-spacing:1px;font-weight:700;">ABQ RESTAURANT</div>
                <div style="margin-top:4px;margin:4px 0 0 0;">--- END OF RECEIPT ---</div>
            </div>
        </div>
    `;

    printableReceipt.innerHTML = receiptHTML;
    // Don't show modal - go directly to printing
    printReceipts();
}

// ===== RECEIPT PRINTING FUNCTION =====
function printReceipts() {
    if (!window.currentReceiptSale) return;

    const { sale, cartItems, escapeHtml, subtotal, paid, total, deliveryCharge } = window.currentReceiptSale;

    // STEP 1: Print Customer Receipt
    const customerReceipt = `
        <div style="font-family:'Courier New', 'Courier', monospace;font-size:11px;line-height:1.4;width:80mm;margin:0;padding:0;">
            <div style="text-align:center;border-bottom:2px solid #000;padding:8px 0;margin-bottom:8px;">
                <div style="font-weight:700;font-size:18px;letter-spacing:2px;">ABQ</div>
                <div style="font-size:9px;">RESTAURANT</div>
                <div style="font-size:9px;">--- CUSTOMER COPY ---</div>
            </div>
            
            <div style="text-align:center;font-weight:700;font-size:13px;letter-spacing:2px;margin-bottom:8px;">
                ${sale.invoice_number}
            </div>
            
            <div style="font-size:10px;margin-bottom:8px;border-bottom:1px dashed #000;padding-bottom:6px;">
                <div style="margin:2px 0;">Date: ${new Date(sale.created_at).toLocaleString('en-US', {month: '2-digit', day: '2-digit', year: '2-digit', hour: '2-digit', minute: '2-digit'})}</div>
                <div style="margin:2px 0;">Type: ${sale.type.replace(/_/g, ' ').toUpperCase()}</div>
                <div style="margin:2px 0;">Cust: ${sale.customer_name ? escapeHtml(sale.customer_name.substring(0, 22)) : 'Walk-in'}</div>
            </div>
            
            <div style="font-size:10px;font-weight:700;margin-bottom:4px;border-bottom:1px solid #000;padding-bottom:2px;">
                <div style="display:grid;grid-template-columns:1.5fr 0.5fr 1fr;gap:4px;">
                    <span>Item</span>
                    <span style="text-align:center;">Qty</span>
                    <span style="text-align:right;">Total</span>
                </div>
            </div>
            
            <div style="margin-bottom:6px;padding-bottom:6px;border-bottom:1px dashed #000;font-size:10px;">
                ${cartItems.map(item => {
                    const qty = item.quantity || 1;
                    const unitPrice = parseFloat(item.price || 0);
                    const lineTotal = unitPrice * qty;
                    return `<div style="display:grid;grid-template-columns:1.5fr 0.5fr 1fr;gap:4px;margin-bottom:2px;">
                        <span>${escapeHtml(item.name.substring(0, 18))}</span>
                        <span style="text-align:center;">${qty}</span>
                        <span style="text-align:right;font-weight:700;">${Math.round(lineTotal)}</span>
                    </div>`;
                }).join('')}
            </div>
            
            <div style="border-bottom:2px solid #000;padding-bottom:6px;margin-bottom:8px;font-size:11px;font-weight:700;">
                <div style="display:flex;justify-content:space-between;margin-bottom:3px;">
                    <span>Subtotal:</span>
                    <span>Rs. ${Math.round(subtotal)}</span>
                </div>
                ${deliveryCharge > 0 ? `<div style="display:flex;justify-content:space-between;margin-bottom:3px;">
                    <span>Delivery:</span>
                    <span>Rs. ${Math.round(deliveryCharge)}</span>
                </div>` : ''}
                <div style="display:flex;justify-content:space-between;margin-bottom:3px;">
                    <span>TOTAL:</span>
                    <span>Rs. ${Math.round(total)}</span>
                </div>
                <div style="display:flex;justify-content:space-between;margin-bottom:3px;">
                    <span>PAID:</span>
                    <span>Rs. ${Math.round(paid)}</span>
                </div>
            </div>
            
            <div style="text-align:center;margin-top:8px;padding-top:8px;border-top:2px solid #000;font-size:9px;">
                <div style="letter-spacing:1px;font-weight:700;">ABQ RESTAURANT</div>
                <div style="margin-top:4px;">Thank you!</div>
            </div>
        </div>
    `;

    // STEP 2: Kitchen Token (to be printed after delay)
    const kitchenToken = `
        <div style="font-family:'Courier New', 'Courier', monospace;font-size:11px;line-height:1.4;width:80mm;margin:0;padding:0;">
            <div style="text-align:center;border-bottom:2px solid #000;padding:8px 0;margin-bottom:8px;">
                <div style="font-weight:700;font-size:14px;letter-spacing:2px;">ABQ</div>
                <div style="font-size:9px;">KITCHEN TOKEN</div>
            </div>
            
            <div style="text-align:center;font-weight:700;font-size:16px;letter-spacing:3px;margin-bottom:8px;border:2px solid #000;padding:8px;">
                ${sale.invoice_number}
            </div>
            
            <div style="text-align:center;font-size:10px;margin-bottom:8px;">
                ${new Date(sale.created_at).toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit'})}
            </div>
            
            <div style="border-top:1px dashed #000;border-bottom:1px dashed #000;padding:8px 0;font-size:10px;text-align:left;margin-bottom:8px;">
                <div style="font-weight:700;margin-bottom:4px;">ORDER:</div>
                ${cartItems.map(item => {
                    const qty = item.quantity || 1;
                    return `<div style="margin:3px 0;">• ${escapeHtml(item.name.substring(0, 26))} (${qty})</div>`;
                }).join('')}
            </div>
            
            <div style="text-align:center;font-size:9px;border-top:2px solid #000;padding-top:8px;">
                <div style="letter-spacing:1px;font-weight:700;">ABQ RESTAURANT</div>
            </div>
        </div>
    `;

    // Combine customer receipt and kitchen token into a single print job
    const combinedHtml = `
        <!doctype html>
        <html>
        <head>
        <meta charset="utf-8" />
        <title>Print - ${sale.invoice_number}</title>
        <style>
            @media print {
                .page-break { page-break-after: always; break-after: page; }
            }
            body { margin: 0; padding: 8px; }
        </style>
        </head>
        <body>
            ${customerReceipt}
            <div class="page-break"></div>
            ${kitchenToken}
        </body>
        </html>
    `;

    const printWindow = window.open('', '', 'width=600,height=800');
    if (!printWindow) {
        showToast('Popup blocked. Allow popups to print receipts.', 'error');
        return;
    }
    printWindow.document.write(combinedHtml);
    printWindow.document.close();
    // Allow some time for rendering before triggering print
    setTimeout(() => {
        printWindow.focus();
        printWindow.print();
        // Close after printing
        setTimeout(() => printWindow.close(), 500);
    }, 300);

    // Close receipt modal and show success message
    setTimeout(() => {
        showToast('Order printed to thermal printer', 'success');
    }, 3000);
}

// ===== RECEIPT MODAL HANDLERS =====
function closeReceipt() {
    receiptModal.classList.add('hidden');
    receiptModal.classList.remove('flex');
}

closeReceiptModal.addEventListener('click', closeReceipt);
closeReceiptBtn.addEventListener('click', closeReceipt);

printReceiptBtn.addEventListener('click', printReceipts);

// Initialize
updateCartUI();
updatePaymentUI();
