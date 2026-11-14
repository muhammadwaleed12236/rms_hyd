// Sales page JS moved from Blade into this module.
// Relies on `window.__ALL_ITEMS_DATA` and `window.CSRF_TOKEN` being set by the Blade view.

document.addEventListener('DOMContentLoaded', () => {
    // State
    let cart = [];
    let currentSaleType = '';
    let payments = [];

    const allItemsData = window.__ALL_ITEMS_DATA || [];
    const csrfToken = window.CSRF_TOKEN || (document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '');

    // Elements
    const itemSearch = document.getElementById('itemSearch');
    const itemsGrid = document.getElementById('itemsGrid');
    const categoryTabs = document.querySelectorAll('.category-tab');
    const cartBadge = document.getElementById('cartBadge');
    const cartItems = document.getElementById('cartItems');
    const subtotalEl = document.getElementById('subtotal');
    const totalEl = document.getElementById('total');
    const paymentsList = document.getElementById('paymentsList');
    const remainingAmount = document.getElementById('remainingAmount');
    const clearCartBtn = document.getElementById('clearCartBtn');
    const checkoutBtn = document.getElementById('checkoutBtn');
    const toast = document.getElementById('toast');

    // Modals
    const invoiceModal = document.getElementById('invoiceModal');
    const closeInvoiceModal = document.getElementById('closeInvoiceModal');
    const paymentModal = document.getElementById('paymentModal');
    const closePaymentModal = document.getElementById('closePaymentModal');
    const successModal = document.getElementById('successModal');
    const receiptModal = document.getElementById('receiptModal');
    const printableReceipt = document.getElementById('printableReceipt');
    const closeReceiptModalBtn = document.getElementById('closeReceiptModal');
    const closeReceiptBtn = document.getElementById('closeReceiptBtn');
    const printReceiptBtn = document.getElementById('printReceiptBtn');

    // Delivery Section
    const saleTypeBtns = document.querySelectorAll('.sale-type-btn');
    const deliverySection = document.getElementById('deliverySection');
    const customerName = document.getElementById('customerName');
    const customerPhone = document.getElementById('customerPhone');
    const customerAddress = document.getElementById('customerAddress');

    // Payment elements
    const paymentMethod = document.getElementById('paymentMethod');
    const paymentAmount = document.getElementById('paymentAmount');
    const addPaymentRowBtn = document.getElementById('addPaymentRowBtn');
    const paymentForm = document.getElementById('paymentForm');

    // Helper: Show toast
    const showToast = (message, type = 'success') => {
        if (!toast) return;
        toast.textContent = message;
        toast.style.backgroundColor = type === 'success' ? '#10b981' : '#ef4444';
        toast.classList.remove('hidden', 'hide');
        setTimeout(() => {
            toast.classList.add('hide');
            setTimeout(() => toast.classList.add('hidden'), 300);
        }, 3000);
    };

    // Helper: Escape HTML
    const escapeHtml = (text) => {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    };

    // Search items
    itemSearch?.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase();
        document.querySelectorAll('.item-card').forEach(card => {
            const name = (card.dataset.itemName || '').toLowerCase();
            card.style.display = name.includes(query) ? '' : 'none';
        });
    });

    // Category filtering for POS view
    categoryTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            categoryTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            
            const selectedCategory = tab.dataset.category;
            document.querySelectorAll('.item-card').forEach(card => {
                const cardCategory = card.dataset.category;
                if (selectedCategory === 'all' || cardCategory == selectedCategory) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    // Update cart badge
    const updateCartBadge = () => {
        if (cartBadge) {
            cartBadge.textContent = cart.length;
        }
    };

    // Item card click - add item directly to cart with default size (first size)
    document.addEventListener('click', (e) => {
        const itemCard = e.target.closest('.item-card');
        if (itemCard) {
            const itemId = parseInt(itemCard.dataset.itemId);
            const item = allItemsData.find(i => i.id === itemId);
            if (!item) return;

            if (!item.sizes || item.sizes.length === 0) {
                showToast('This item has no sizes configured', 'error');
                return;
            }

            const defaultSize = item.sizes[0];
            const cartItem = {
                item_id: item.id,
                name: item.name,
                size: defaultSize.size,
                size_id: defaultSize.id,
                price: parseFloat(defaultSize.price),
                quantity: 1,
                total: parseFloat(defaultSize.price) * 1,
            };

            cart.push(cartItem);
            updateCartUI();
            showToast('Item added to cart', 'success');
        }
    });

    // Update cart UI
    const updateCartUI = () => {
        const subtotal = cart.reduce((sum, item) => sum + item.total, 0);
        subtotalEl && (subtotalEl.textContent = `Rs. ${subtotal.toLocaleString()}`);
        totalEl && (totalEl.textContent = `Rs. ${subtotal.toLocaleString()}`);
        if (checkoutBtn) checkoutBtn.disabled = cart.length === 0;
        updateCartBadge();

        if (!cartItems) return;
        cartItems.innerHTML = '';
        if (cart.length === 0) {
            cartItems.innerHTML = '<div class="text-center py-4 text-gray-500 text-sm">No items</div>';
        } else {
            cart.forEach((item, idx) => {
                const div = document.createElement('div');
                div.className = 'cart-item bg-gray-50 p-2 rounded-lg flex justify-between items-center text-xs gap-2';

                // Build size options for this item
                const itemMeta = allItemsData.find(i => i.id === item.item_id) || { sizes: [] };
                const sizeOptionsHtml = (itemMeta.sizes || []).map(s => `<option value="${s.id}" data-price="${s.price}" ${s.id == item.size_id ? 'selected' : ''}>${escapeHtml(s.size)} — Rs. ${parseFloat(s.price).toLocaleString()}</option>`).join('');

                div.innerHTML = `
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-800 truncate">${escapeHtml(item.name)}</p>
                        <div class="flex items-center gap-2 mt-1">
                            <select class="cart-size-select border border-gray-200 rounded px-2 py-1 text-xs" data-idx="${idx}">
                                ${sizeOptionsHtml}
                            </select>
                            <input type="number" class="cart-qty-input w-16 border border-gray-200 rounded px-2 py-1 text-xs" min="1" value="${item.quantity}" data-idx="${idx}">
                        </div>
                    </div>
                    <div class="text-right ml-2">
                        <p class="font-bold text-gray-900">Rs. ${item.total.toLocaleString()}</p>
                        <div class="mt-1 flex justify-end gap-2">
                            <button class="remove-cart-btn text-red-600 hover:text-red-800 font-bold cursor-pointer" data-idx="${idx}">×</button>
                        </div>
                    </div>
                `;

                // Remove handler
                div.querySelector('.remove-cart-btn').addEventListener('click', () => {
                    div.classList.add('removing');
                    setTimeout(() => {
                        cart.splice(idx, 1);
                        updateCartUI();
                    }, 200);
                });

                // Size change handler
                const sizeSelect = div.querySelector('.cart-size-select');
                sizeSelect.addEventListener('change', (e) => {
                    const selId = parseInt(e.target.value);
                    const sel = (itemMeta.sizes || []).find(s => s.id === selId);
                    if (!sel) return;
                    cart[idx].size = sel.size;
                    cart[idx].size_id = sel.id;
                    cart[idx].price = parseFloat(sel.price);
                    cart[idx].total = cart[idx].price * cart[idx].quantity;
                    updateCartUI();
                });

                // Quantity change handler
                const qtyInput = div.querySelector('.cart-qty-input');
                qtyInput.addEventListener('change', (e) => {
                    let val = parseInt(e.target.value) || 1;
                    if (val < 1) val = 1;
                    cart[idx].quantity = val;
                    cart[idx].total = cart[idx].price * cart[idx].quantity;
                    updateCartUI();
                });

                cartItems.appendChild(div);
            });
        }

        updateRemaining();
    };

    // Sale type selection
    saleTypeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            saleTypeBtns.forEach(b => b.classList.remove('border-blue-500', 'bg-blue-50'));
            btn.classList.add('border-blue-500', 'bg-blue-50');
            currentSaleType = btn.dataset.type;

            if (currentSaleType === 'delivery') {
                deliverySection && deliverySection.classList.remove('hidden');
            } else {
                deliverySection && deliverySection.classList.add('hidden');
                if (customerName) customerName.value = '';
                if (customerPhone) customerPhone.value = '';
                if (customerAddress) customerAddress.value = '';
            }
        });
    });

    // Payment row management
    addPaymentRowBtn?.addEventListener('click', () => {
        const amount = parseFloat(paymentAmount.value) || 0;
        const method = paymentMethod.value;

        if (amount <= 0) {
            showToast('Enter amount > 0', 'error');
            return;
        }

        const total = cart.reduce((sum, item) => sum + item.total, 0);
        const paidSoFar = payments.reduce((sum, p) => sum + parseFloat(p.amount), 0);
        const remaining = total - paidSoFar - amount;

        if (remaining < 0) {
            showToast(`Amount exceeds remaining (max: Rs. ${Math.max(0, total - paidSoFar).toLocaleString()})`, 'error');
            return;
        }

        payments.push({ method, amount });
        paymentAmount.value = '';
        updatePaymentsUI();
        showToast('Payment added', 'success');
    });

    const updatePaymentsUI = () => {
        if (!paymentsList) return;
        paymentsList.innerHTML = '';
        payments.forEach((p, idx) => {
            const div = document.createElement('div');
            div.className = 'bg-blue-50 p-2 rounded-lg flex justify-between items-center text-xs';
            div.innerHTML = `
                <div>
                    <p class="font-semibold text-gray-800">${p.method}</p>
                    <p class="text-gray-600">Rs. ${parseFloat(p.amount).toLocaleString()}</p>
                </div>
                <button class="text-red-600 hover:text-red-800 font-bold cursor-pointer" data-idx="${idx}">×</button>
            `;
            div.querySelector('button').addEventListener('click', () => {
                payments.splice(idx, 1);
                updatePaymentsUI();
                updateRemaining();
            });
            paymentsList.appendChild(div);
        });
        updateRemaining();
    };

    const updateRemaining = () => {
        const total = cart.reduce((sum, item) => sum + item.total, 0);
        const paid = payments.reduce((sum, p) => sum + parseFloat(p.amount), 0);
        const remaining = Math.max(0, total - paid);
        remainingAmount && (remainingAmount.textContent = `Rs. ${remaining.toLocaleString()}`);
    };

    // Clear cart
    clearCartBtn?.addEventListener('click', () => {
        if (confirm('Clear cart?')) {
            cart = [];
            payments = [];
            currentSaleType = '';
            saleTypeBtns.forEach(b => b.classList.remove('border-blue-500', 'bg-blue-50'));
            deliverySection && deliverySection.classList.add('hidden');
            updateCartUI();
            updatePaymentsUI();
            showToast('Cart cleared', 'success');
        }
    });

    // Checkout
    checkoutBtn?.addEventListener('click', async () => {
        if (cart.length === 0) {
            showToast('Add items to cart', 'error');
            return;
        }

        if (!currentSaleType) {
            showToast('Select order type', 'error');
            return;
        }

        if (currentSaleType === 'delivery') {
            if (!customerName.value.trim()) {
                showToast('Enter customer name', 'error');
                return;
            }
            if (!customerPhone.value.trim()) {
                showToast('Enter phone', 'error');
                return;
            }
            if (!customerAddress.value.trim()) {
                showToast('Enter address', 'error');
                return;
            }
        }

        const total = cart.reduce((sum, item) => sum + item.total, 0);
        const paid = payments.reduce((sum, p) => sum + parseFloat(p.amount), 0);

        const payload = {
            type: currentSaleType,
            total: total,
            cart: cart,
            customer_name: currentSaleType === 'delivery' ? customerName.value : null,
            customer_phone: currentSaleType === 'delivery' ? customerPhone.value : null,
            customer_address: currentSaleType === 'delivery' ? customerAddress.value : null,
            payments: payments.length > 0 ? payments : null
        };

        checkoutBtn.disabled = true;

        try {
            const response = await fetch('/sales', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            });

            if (!response.ok) {
                const data = await response.json();
                throw new Error(data.message || 'Failed to submit sale');
            }

            const sale = await response.json();

            // Render printable receipts (invoice + token) and open receipt modal
            renderReceipts(sale);
            openReceiptModal();

            // Reset cart and inputs (cart still cleared after rendering)
            cart = [];
            payments = [];
            currentSaleType = '';
            saleTypeBtns.forEach(b => b.classList.remove('border-blue-500', 'bg-blue-50'));
            deliverySection && deliverySection.classList.add('hidden');
            updateCartUI();
            updatePaymentsUI();

        } catch (error) {
            showToast(error.message, 'error');
        } finally {
            checkoutBtn.disabled = false;
        }
    });

    // Success modal - New Sale button
    document.getElementById('newSaleBtn')?.addEventListener('click', () => {
        if (successModal) {
            successModal.classList.add('hidden');
            successModal.classList.remove('flex');
        }
        location.reload();
    });

    // Sales table - show invoice
    document.getElementById('salesTable')?.addEventListener('click', (e) => {
        const row = e.target.closest('.sale-row');
        if (row && !e.target.classList.contains('add-payment-btn')) {
            const sale = JSON.parse(row.getAttribute('data-sale'));
            showInvoiceDetails(sale);
        }
    });

    const showInvoiceDetails = (sale) => {
        let paid = 0;
        if (sale.payments && Array.isArray(sale.payments)) {
            paid = sale.payments.reduce((s, p) => s + parseFloat(p.amount || 0), 0);
        }
        const remaining = parseFloat(sale.total) - paid;

        let itemsHtml = '';
        if (sale.cart && Array.isArray(sale.cart)) {
            itemsHtml = `<table class='min-w-full text-xs mb-3 border-collapse'><thead><tr class='border-b'><th class='text-left py-1'>Item</th><th class='text-right'>Qty</th><th class='text-right'>Price</th></tr></thead><tbody>`;
            sale.cart.forEach(item => {
                let name = item.name || item.item_name || item.title || (item.item && item.item.name) || 'Unknown';
                const qty = (item.quantity !== undefined && item.quantity !== null) ? item.quantity : (item.qty !== undefined && item.qty !== null ? item.qty : 1);
                const unitPrice = (item.price !== undefined && item.price !== null) ? parseFloat(item.price) : (item.total && qty ? parseFloat(item.total) / qty : 0);

                if (name === 'Unknown') {
                    const possibleId = item.item_id || item.itemId || item.id || (item.item && item.item.id);
                    if (possibleId) {
                        const found = allItemsData.find(ai => ai.id === possibleId || ai.id === parseInt(possibleId));
                        if (found && found.name) {
                            name = found.name;
                        }
                    }
                }

                itemsHtml += `<tr class='border-b text-xs'>` +
                    `<td class='py-1'>${escapeHtml(String(name))}</td>` +
                    `<td class='text-right'>${qty}</td>` +
                    `<td class='text-right'>Rs. ${parseFloat(unitPrice || 0).toLocaleString()}</td>` +
                `</tr>`;
            });
            itemsHtml += `</tbody></table>`;
        }

        let paymentsHtml = '';
        if (sale.payments && sale.payments.length) {
            paymentsHtml = `<div class='bg-blue-50 p-2 rounded text-xs'><span class='font-semibold'>Payments:</span><ul class='mt-1 space-y-1'>`;
            sale.payments.forEach(p => {
                paymentsHtml += `<li>• ${p.method}: Rs. ${parseFloat(p.amount).toLocaleString()}</li>`;
            });
            paymentsHtml += `</ul></div>`;
        }

        document.getElementById('invoiceContent').innerHTML = `
            <div class='space-y-2 text-sm'>
                <div><span class='font-semibold'>Invoice:</span> <span class='font-mono'>${sale.invoice_number}</span></div>
                <div><span class='font-semibold'>Date:</span> ${sale.created_at ? new Date(sale.created_at).toLocaleString() : ''}</div>
                <div><span class='font-semibold'>Type:</span> ${sale.type ? sale.type.replace('_', ' ').toUpperCase() : 'N/A'}</div>
                <div><span class='font-semibold'>Customer:</span> ${sale.customer_name || 'N/A'}</div>
                <div class='pt-2 border-t'>${itemsHtml}</div>
                <div class='space-y-1 text-xs'>
                    <div class='flex justify-between'><span>Total:</span><span class='font-bold'>Rs. ${parseFloat(sale.total).toLocaleString()}</span></div>
                    <div class='flex justify-between'><span>Paid:</span><span>Rs. ${paid.toLocaleString()}</span></div>
                    <div class='flex justify-between'><span>Remaining:</span><span class='font-bold ${remaining > 0 ? 'text-red-600' : 'text-green-600'}'>Rs. ${remaining.toLocaleString()}</span></div>
                </div>
                ${paymentsHtml}
            </div>
        `;

        // Add View Receipt button inside invoice modal
        const viewBtn = document.createElement('button');
        viewBtn.className = 'mt-4 px-3 py-2 bg-blue-600 text-white rounded text-sm';
        viewBtn.textContent = 'View Receipt';
        viewBtn.addEventListener('click', () => {
            renderReceipts(sale);
            openReceiptModal();
        });
        document.getElementById('invoiceContent').appendChild(viewBtn);

        invoiceModal && invoiceModal.classList.remove('hidden');
        invoiceModal && invoiceModal.classList.add('flex');
    };
    
    closeInvoiceModal?.addEventListener('click', () => {
        if (invoiceModal) {
            invoiceModal.classList.add('hidden');
            invoiceModal.classList.remove('flex');
        }
    });

    invoiceModal?.addEventListener('click', (e) => {
        if (e.target === invoiceModal && closeInvoiceModal) closeInvoiceModal.click();
    });

    // Add payment for existing sale
    document.addEventListener('click', async (e) => {
        if (e.target.classList.contains('add-payment-btn')) {
            const saleId = e.target.getAttribute('data-sale-id');
            const total = parseFloat(e.target.getAttribute('data-total'));
            const remaining = parseFloat(e.target.getAttribute('data-remaining'));

            window.currentSaleId = saleId;
            window.currentRemaining = remaining;

            const pmRem = document.getElementById('paymentModalRemaining');
            const pmAmountInput = document.getElementById('paymentAmountInput');
            if (pmRem) pmRem.textContent = `Remaining: Rs. ${remaining.toLocaleString()}`;
            if (pmAmountInput) {
                pmAmountInput.value = remaining.toFixed(2);
                pmAmountInput.max = remaining.toFixed(2);
            }

            paymentModal && paymentModal.classList.remove('hidden');
            paymentModal && paymentModal.classList.add('flex');
        }
    });

    closePaymentModal?.addEventListener('click', () => {
        if (paymentModal) {
            paymentModal.classList.add('hidden');
            paymentModal.classList.remove('flex');
        }
    });

    paymentModal?.addEventListener('click', (e) => {
        if (e.target === paymentModal && closePaymentModal) closePaymentModal.click();
    });

    paymentForm?.addEventListener('submit', async (e) => {
        e.preventDefault();

        const method = document.getElementById('paymentMethodSelect').value;
        const amount = parseFloat(document.getElementById('paymentAmountInput').value);

        if (amount <= 0) {
            showToast('Enter valid amount', 'error');
            return;
        }

        const submitBtn = paymentForm.querySelector('button[type="submit"]');
        submitBtn.disabled = true;

        try {
            const response = await fetch('/payments', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    sale_id: window.currentSaleId,
                    method: method,
                    amount: amount,
                    transaction_id: document.getElementById('transactionIdInput').value || null,
                }),
            });

            if (!response.ok) throw new Error('Failed to save payment');

            showToast('Payment recorded!', 'success');
            setTimeout(() => location.reload(), 1200);
        } catch (error) {
            showToast(error.message, 'error');
            submitBtn.disabled = false;
        }
    });

    // Receipt rendering and printing helpers
    const openReceiptModal = () => {
        receiptModal && receiptModal.classList.remove('hidden');
        receiptModal && receiptModal.classList.add('flex');
    };

    const closeReceiptModal = () => {
        receiptModal && receiptModal.classList.add('hidden');
        receiptModal && receiptModal.classList.remove('flex');
    };

    closeReceiptModalBtn?.addEventListener('click', closeReceiptModal);
    closeReceiptBtn?.addEventListener('click', () => {
        closeReceiptModal();
        // Refresh sales table by reloading just the table section
        setTimeout(() => {
            location.reload();
        }, 300);
    });
    receiptModal?.addEventListener('click', (e) => { if (e.target === receiptModal) closeReceiptModal(); });

    const renderReceipts = (sale) => {
        // Build compact items list (like thermal printer)
        let itemsList = '';
        if (sale.cart && Array.isArray(sale.cart)) {
            sale.cart.forEach(item => {
                // Try multiple ways to get the item name
                let name = item.name || item.item_name || item.title || (item.item && item.item.name) || 'Unknown';
                
                // If still unknown, try to look it up from allItemsData using item_id
                if (name === 'Unknown') {
                    const possibleId = item.item_id || item.itemId || item.id || (item.item && item.item.id);
                    if (possibleId) {
                        const found = allItemsData.find(ai => ai.id === possibleId || ai.id === parseInt(possibleId));
                        if (found && found.name) {
                            name = found.name;
                        }
                    }
                }
                
                const qty = (item.quantity !== undefined && item.quantity !== null) ? item.quantity : 1;
                const unitPrice = (item.price !== undefined && item.price !== null) ? parseFloat(item.price) : 0;
                const lineTotal = unitPrice * qty;
                // Format: "Item Name             Q×P  Total"
                itemsList += `${escapeHtml(name)} x${qty} = Rs. ${lineTotal.toLocaleString()}\n`;
            });
        }

        let paid = 0;
        if (sale.payments && Array.isArray(sale.payments)) paid = sale.payments.reduce((s, p) => s + parseFloat(p.amount || 0), 0);
        const remaining = parseFloat(sale.total) - paid;

        // Helper function to get item name with fallback logic
        const getItemName = (item) => {
            let name = item.name || item.item_name || item.title || (item.item && item.item.name) || 'Unknown';
            
            if (name === 'Unknown') {
                const possibleId = item.item_id || item.itemId || item.id || (item.item && item.item.id);
                if (possibleId) {
                    const found = allItemsData.find(ai => ai.id === possibleId || ai.id === parseInt(possibleId));
                    if (found && found.name) {
                        name = found.name;
                    }
                }
            }
            return name;
        };

        // Compact thermal receipt format (like 80mm paper)
            if (!printableReceipt) {
                console.warn('printableReceipt element not found, skipping render');
                return;
            }

            printableReceipt.innerHTML = `
            <div style="font-family:'Courier New',monospace;font-size:12px;line-height:1.6;width:100%;max-width:300px;margin:0 auto;padding:8px;">
                <!-- HEADER -->
                <div style="text-align:center;border-bottom:2px solid #000;padding-bottom:8px;margin-bottom:8px;">
                    <div style="font-weight:700;font-size:16px;letter-spacing:2px;">ABQ</div>
                    <div style="font-size:10px;margin-top:2px;">--- INVOICE ---</div>
                </div>
                
                <!-- INVOICE NUMBER -->
                <div style="text-align:center;font-weight:700;font-size:14px;letter-spacing:2px;margin-bottom:8px;">
                    ${sale.invoice_number}
                </div>
                
                <!-- DETAILS -->
                <div style="font-size:11px;margin-bottom:8px;border-bottom:1px dashed #000;padding-bottom:8px;">
                    <div>Date: ${sale.created_at ? new Date(sale.created_at).toLocaleString('en-US', {month: '2-digit', day: '2-digit', year: '2-digit', hour: '2-digit', minute: '2-digit'}) : 'N/A'}</div>
                    <div>Type: ${sale.type ? sale.type.replace(/_/g, ' ').toUpperCase() : 'N/A'}</div>
                    <div>Customer: ${sale.customer_name ? escapeHtml(sale.customer_name.substring(0, 25)) : 'Walk-in'}</div>
                </div>
                
                <!-- ITEMS -->
                <div style="margin-bottom:8px;border-bottom:1px dashed #000;padding-bottom:8px;font-size:11px;">
                    ${(sale.cart||[]).map(it => {
                        const itemName = getItemName(it);
                        const qty = it.quantity || 1;
                        const unitPrice = (it.price !== undefined && it.price !== null) ? parseFloat(it.price) : 0;
                        const lineTotal = unitPrice * qty;
                        return `<div style="display:flex;justify-content:space-between;margin-bottom:2px;">
                            <span>${escapeHtml(itemName.substring(0, 20))}</span>
                            <span>${qty}x${unitPrice}</span>
                            <span style="text-align:right;font-weight:700;">${lineTotal}</span>
                        </div>`;
                    }).join('')}
                </div>
                
                <!-- TOTALS -->
                <div style="border-bottom:2px solid #000;padding-bottom:6px;margin-bottom:8px;font-size:12px;font-weight:700;">
                    <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                        <span>TOTAL:</span>
                        <span>Rs. ${parseFloat(sale.total).toLocaleString()}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                        <span>PAID:</span>
                        <span>Rs. ${paid.toLocaleString()}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;color:${remaining > 0 ? '#d32f2f' : '#388e3c'};">
                        <span>DUE:</span>
                        <span>Rs. ${remaining.toLocaleString()}</span>
                    </div>
                </div>
                
                <!-- KITCHEN TOKEN -->
                <div style="text-align:center;margin-top:8px;padding-top:8px;border-top:2px solid #000;">
                    <div style="font-size:11px;margin-bottom:6px;font-weight:700;">--- KITCHEN TOKEN ---</div>
                    <div style="font-weight:700;font-size:18px;letter-spacing:3px;margin:6px 0;border:1px solid #000;padding:4px;">
                        ${sale.invoice_number}
                    </div>
                    <div style="font-size:10px;margin-bottom:6px;">
                        ${sale.created_at ? new Date(sale.created_at).toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit'}) : 'N/A'}
                    </div>
                    <div style="border-top:1px dashed #000;border-bottom:1px dashed #000;padding:6px 0;font-size:11px;text-align:left;margin-bottom:6px;">
                        ${(sale.cart||[]).map(it => {
                            const itemName = getItemName(it);
                            const qty = it.quantity || 1;
                            return `<div>• ${escapeHtml(itemName.substring(0, 28))} (${qty})</div>`;
                        }).join('')}
                    </div>
                </div>
                
                <div style="text-align:center;margin-top:8px;font-size:10px;">
                    Thank you!
                </div>
            </div>
        `;
    };

    const printHtml = (htmlContent, title = 'Receipt') => {
        const w = window.open('', '_blank', 'width=600,height=800');
        if (!w) { showToast('Popup blocked. Allow popups to print receipts.', 'error'); return; }
        w.document.write(`<!doctype html><html><head><title>${escapeHtml(title)}</title><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><style>body{font-family:system-ui,-apple-system,Segoe UI,Roboto,'Helvetica Neue',Arial;padding:0;margin:0;}@media print{body{padding:0;margin:0;}}</style></head><body>${htmlContent}</body></html>`);
        w.document.close();
        w.focus();
        setTimeout(() => { w.print(); setTimeout(()=>w.close(), 300); }, 300);
    };

    printReceiptBtn?.addEventListener('click', () => {
        if (!printableReceipt) {
            showToast('Nothing to print', 'error');
            return;
        }
        printHtml(printableReceipt.innerHTML, 'Receipt');
    });

    updateCartUI();
});
