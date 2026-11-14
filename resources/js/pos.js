document.addEventListener('DOMContentLoaded', () => {
    // State
    let cart = [];
    let currentSizeSelection = null;

    const allItemsData = window.__ALL_ITEMS_DATA || [];
    const csrfToken = window.CSRF_TOKEN;

    // DOM Elements
    const categoryButtons = document.querySelectorAll('.category-filter');
    const productCards = document.querySelectorAll('.product-card');
    const cartItemsContainer = document.getElementById('cartItems');
    const cartCountBadge = document.getElementById('cartCount');
    const subtotalEl = document.getElementById('subtotal');
    const taxEl = document.getElementById('tax');
    const totalEl = document.getElementById('total');
    const clearCartBtn = document.getElementById('clearCartBtn');
    const checkoutBtn = document.getElementById('checkoutBtn');
    const toast = document.getElementById('toast');

    // Modal Elements
    const sizeModal = document.getElementById('sizeModal');
    const sizeModalTitle = document.getElementById('sizeModalTitle');
    const sizeOptions = document.getElementById('sizeOptions');
    const quantityInput = document.getElementById('quantityInput');
    const decreaseQtyBtn = document.getElementById('decreaseQty');
    const increaseQtyBtn = document.getElementById('increaseQty');
    const unitPriceEl = document.getElementById('unitPrice');
    const lineTotalEl = document.getElementById('lineTotal');
    const addToCartBtn = document.getElementById('addToCartBtn');
    const cancelSizeModalBtn = document.getElementById('cancelSizeModal');

    // Helper: Show Toast
    const showToast = (message, type = 'success') => {
        toast.textContent = message;
        toast.style.backgroundColor = type === 'success' ? '#10b981' : '#ef4444';
        toast.classList.remove('hidden');
        setTimeout(() => toast.classList.add('hidden'), 3000);
    };

    // Category Filtering
    categoryButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            categoryButtons.forEach(b => b.classList.remove('active', 'bg-blue-100', 'text-blue-700'));
            btn.classList.add('active', 'bg-blue-100', 'text-blue-700');

            const selectedCategory = btn.dataset.category;
            productCards.forEach(card => {
                const cardCategory = card.dataset.category;
                card.style.display = selectedCategory === 'all' || cardCategory == selectedCategory ? '' : 'none';
            });
        });
    });

    // Product Card Click - Open Size Modal
    productCards.forEach(card => {
        card.addEventListener('click', () => {
            const itemId = parseInt(card.dataset.itemId);
            const item = allItemsData.find(i => i.id === itemId);

            if (!item) return;

            if (!item.sizes || item.sizes.length === 0) {
                showToast('This item has no sizes', 'error');
                return;
            }

            // Setup modal
            currentSizeSelection = { item, selectedSize: item.sizes[0] };
            sizeModalTitle.textContent = item.name;

            // Render size options
            sizeOptions.innerHTML = item.sizes.map(size => `
                <button type="button" class="size-option w-full p-3 border-2 rounded-lg text-left font-medium transition-all" 
                    data-size-id="${size.id}">
                    <span class="text-lg">${size.size}</span>
                    <span class="float-right text-blue-600 font-bold">Rs. ${parseFloat(size.price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
                </button>
            `).join('');

            // Setup first size as selected
            const firstSizeBtn = sizeOptions.querySelector('.size-option');
            firstSizeBtn?.classList.add('border-orange-500', 'bg-orange-50');

            // Size option selection
            sizeOptions.querySelectorAll('.size-option').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    sizeOptions.querySelectorAll('.size-option').forEach(b => b.classList.remove('border-orange-500', 'bg-orange-50'));
                    btn.classList.add('border-orange-500', 'bg-orange-50');

                    const sizeId = parseInt(btn.dataset.sizeId);
                    currentSizeSelection.selectedSize = item.sizes.find(s => s.id === sizeId);
                    updateModalDisplay();
                });
            });

            quantityInput.value = 1;
            updateModalDisplay();
            openSizeModal();
        });
    });

    // Update Modal Display
    const updateModalDisplay = () => {
        if (!currentSizeSelection) return;

        const price = parseFloat(currentSizeSelection.selectedSize.price);
        const qty = parseInt(quantityInput.value) || 1;

        unitPriceEl.textContent = `Rs. ${price.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        lineTotalEl.textContent = `Rs. ${(price * qty).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    };

    // Quantity Controls
    decreaseQtyBtn.addEventListener('click', () => {
        let val = parseInt(quantityInput.value) || 1;
        if (val > 1) quantityInput.value = val - 1;
        updateModalDisplay();
    });

    increaseQtyBtn.addEventListener('click', () => {
        let val = parseInt(quantityInput.value) || 1;
        quantityInput.value = val + 1;
        updateModalDisplay();
    });

    quantityInput.addEventListener('change', updateModalDisplay);

    // Add to Cart
    addToCartBtn.addEventListener('click', () => {
        if (!currentSizeSelection) return;

        const item = currentSizeSelection.item;
        const size = currentSizeSelection.selectedSize;
        const quantity = parseInt(quantityInput.value) || 1;
        const price = parseFloat(size.price);

        // Check if item with same size already exists
        const existingItem = cart.find(ci => ci.item_id === item.id && ci.size_id === size.id);

        if (existingItem) {
            existingItem.quantity += quantity;
            existingItem.total = existingItem.price * existingItem.quantity;
        } else {
            cart.push({
                item_id: item.id,
                name: item.name,
                size: size.size,
                size_id: size.id,
                price: price,
                quantity: quantity,
                total: price * quantity,
            });
        }

        updateCartUI();
        closeSizeModal();
        showToast('Item added to cart', 'success');
    });

    // Modal Controls
    const openSizeModal = () => {
        sizeModal.classList.remove('hidden');
    };

    const closeSizeModal = () => {
        sizeModal.classList.add('hidden');
    };

    cancelSizeModalBtn.addEventListener('click', closeSizeModal);
    sizeModal.addEventListener('click', (e) => {
        if (e.target === sizeModal) closeSizeModal();
    });

    // Update Cart UI
    const updateCartUI = () => {
        cartCountBadge.textContent = cart.length;

        if (cart.length === 0) {
            cartItemsContainer.innerHTML = `
                <div class="text-center py-8 text-gray-500">
                    <p class="text-3xl mb-2">🛒</p>
                    <p class="text-sm">Cart is empty</p>
                </div>
            `;
        } else {
            cartItemsContainer.innerHTML = cart.map((item, idx) => `
                <div class="bg-gray-50 rounded-lg p-3 space-y-2">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <p class="font-semibold text-gray-900 text-sm">${item.name}</p>
                            <p class="text-xs text-gray-500">${item.size}</p>
                        </div>
                        <button class="remove-item-btn text-red-600 hover:text-red-800 font-bold text-lg leading-none" data-idx="${idx}">×</button>
                    </div>
                    <div class="flex items-center gap-2">
                        <button class="qty-decrease px-2 py-1 bg-gray-200 hover:bg-gray-300 rounded text-xs font-bold" data-idx="${idx}">−</button>
                        <input type="number" class="qty-input w-12 border border-gray-300 rounded px-2 py-1 text-center text-xs" value="${item.quantity}" data-idx="${idx}">
                        <button class="qty-increase px-2 py-1 bg-gray-200 hover:bg-gray-300 rounded text-xs font-bold" data-idx="${idx}">+</button>
                        <span class="ml-auto font-bold text-gray-900">Rs. ${item.total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
                    </div>
                </div>
            `).join('');

            // Attach event listeners
            cartItemsContainer.querySelectorAll('.remove-item-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const idx = parseInt(btn.dataset.idx);
                    cart.splice(idx, 1);
                    updateCartUI();
                });
            });

            cartItemsContainer.querySelectorAll('.qty-decrease').forEach(btn => {
                btn.addEventListener('click', () => {
                    const idx = parseInt(btn.dataset.idx);
                    if (cart[idx].quantity > 1) {
                        cart[idx].quantity--;
                        cart[idx].total = cart[idx].price * cart[idx].quantity;
                        updateCartUI();
                    }
                });
            });

            cartItemsContainer.querySelectorAll('.qty-increase').forEach(btn => {
                btn.addEventListener('click', () => {
                    const idx = parseInt(btn.dataset.idx);
                    cart[idx].quantity++;
                    cart[idx].total = cart[idx].price * cart[idx].quantity;
                    updateCartUI();
                });
            });

            cartItemsContainer.querySelectorAll('.qty-input').forEach(input => {
                input.addEventListener('change', () => {
                    const idx = parseInt(input.dataset.idx);
                    let val = parseInt(input.value) || 1;
                    if (val < 1) val = 1;
                    cart[idx].quantity = val;
                    cart[idx].total = cart[idx].price * cart[idx].quantity;
                    updateCartUI();
                });
            });
        }

        // Update totals
        const subtotal = cart.reduce((sum, item) => sum + item.total, 0);
        const tax = subtotal * 0; // 0% tax for now
        const total = subtotal + tax;

        subtotalEl.textContent = `Rs. ${subtotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        taxEl.textContent = `Rs. ${tax.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        totalEl.textContent = `Rs. ${total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

        checkoutBtn.disabled = cart.length === 0;
    };

    // Clear Cart
    clearCartBtn.addEventListener('click', () => {
        if (confirm('Clear entire cart?')) {
            cart = [];
            updateCartUI();
            showToast('Cart cleared', 'success');
        }
    });

    // Checkout
    checkoutBtn.addEventListener('click', async () => {
        if (cart.length === 0) {
            showToast('Cart is empty', 'error');
            return;
        }

        const total = cart.reduce((sum, item) => sum + item.total, 0);

        const payload = {
            type: 'dine_in', // Default type
            total: total,
            cart: cart,
            customer_name: null,
            customer_phone: null,
            customer_address: null,
            payments: [],
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
                throw new Error(data.message || 'Sale failed');
            }

            const sale = await response.json();
            showToast('Sale completed!', 'success');
            
            // Reset cart
            cart = [];
            updateCartUI();

            // Redirect to sales page
            setTimeout(() => {
                window.location.href = '/sales';
            }, 1500);

        } catch (error) {
            showToast(error.message, 'error');
            checkoutBtn.disabled = false;
        }
    });

    // Initialize
    updateCartUI();
});
