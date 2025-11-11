@extends('layouts.app')

@section('content')
<style>
    @keyframes slideIn {
        from { opacity: 0; transform: translateX(-20px); }
        to { opacity: 1; transform: translateX(0); }
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideOut {
        from { opacity: 1; transform: translateX(0); }
        to { opacity: 0; transform: translateX(-20px); }
    }

    .cart-item { animation: slideIn 0.3s ease-out; }
    .cart-item.removing { animation: slideOut 0.2s ease-out forwards; }
    .toast { animation: slideIn 0.3s ease-out; }
    .toast.hide { animation: slideOut 0.3s ease-out forwards; }
    
    .item-hover { @apply hover:bg-blue-50 cursor-pointer transition-colors; }
</style>

<div class="max-w-7xl mx-auto space-y-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-3xl font-bold text-gray-800">Point of Sale</h2>
            <p class="text-gray-600 mt-1">Quick checkout workflow</p>
        </div>
    </div>

    <div class="grid lg:grid-cols-4 gap-4">
        <!-- Left: Items List (with search) -->
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 h-fit">
                <div class="mb-4">
                    <input 
                        type="text" 
                        id="itemSearch" 
                        placeholder="🔍 Search items..." 
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none">
                </div>
                
                <!-- Items List -->
                <div id="itemsList" class="space-y-2 max-h-screen overflow-y-auto pr-2">
                    @forelse($items as $item)
                    <div class="item-card item-hover border border-gray-200 rounded-lg p-3 cursor-pointer transition-all" data-item-id="{{ $item->id }}" data-item-name="{{ $item->name }}">
                        <div class="flex gap-3">
                            @if($item->image)
                                <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="w-16 h-16 object-cover rounded-lg flex-shrink-0">
                            @else
                                <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center flex-shrink-0 text-gray-400">🍽️</div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <h4 class="font-semibold text-gray-800 text-sm truncate">{{ $item->name }}</h4>
                                <p class="text-xs text-gray-500">{{ $item->subcategory->name ?? 'N/A' }}</p>
                                <div class="mt-1 text-xs font-semibold text-blue-600">
                                    @if($item->sizes->count() > 0)
                                        {{ $item->sizes->first()->size }}: Rs. {{ number_format($item->sizes->first()->price, 0) }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-center text-gray-500 py-6">No items available</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right: Cart & Checkout -->
        <div class="lg:col-span-2 space-y-4">
            <!-- Cart Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Order Cart</h3>

                <!-- Cart Items -->
                <div id="cartItems" class="space-y-2 max-h-80 overflow-y-auto border-b pb-4 mb-4">
                    <div id="cartEmpty" class="text-center py-6 text-gray-500">
                        <p class="text-sm">👜 No items in cart</p>
                    </div>
                </div>

                <!-- Totals -->
                <div class="space-y-2 pb-4 border-b">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal:</span>
                        <span id="subtotal">Rs. 0</span>
                    </div>
                    <div class="flex justify-between text-lg font-bold text-gray-800 bg-blue-50 p-2 rounded">
                        <span>Total:</span>
                        <span id="total">Rs. 0</span>
                    </div>
                </div>

                <!-- Sale Type Selection -->
                <div class="mt-4 space-y-3">
                    <label class="block text-sm font-semibold text-gray-700">Order Type</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button class="sale-type-btn border-2 border-gray-300 rounded-lg p-2 text-center text-xs font-medium hover:border-blue-500 hover:bg-blue-50 transition-all" data-type="dine_in">
                            🍽️ Dine In
                        </button>
                        <button class="sale-type-btn border-2 border-gray-300 rounded-lg p-2 text-center text-xs font-medium hover:border-blue-500 hover:bg-blue-50 transition-all" data-type="take_away">
                            🛍️ Takeaway
                        </button>
                        <button class="sale-type-btn border-2 border-gray-300 rounded-lg p-2 text-center text-xs font-medium hover:border-blue-500 hover:bg-blue-50 transition-all" data-type="delivery">
                            🚚 Delivery
                        </button>
                    </div>
                </div>

                <!-- Delivery Fields (shown conditionally) -->
                <div id="deliverySection" class="hidden mt-4 space-y-3 pt-4 border-t">
                    <input type="text" id="customerName" placeholder="Customer Name" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none">
                    <input type="tel" id="customerPhone" placeholder="Phone Number" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none">
                    <textarea id="customerAddress" placeholder="Delivery Address" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none"></textarea>
                </div>

                <!-- Payment Section -->
                <div class="mt-4 space-y-3 pt-4 border-t">
                    <label class="block text-sm font-semibold text-gray-700">Payments</label>
                    
                    <!-- Payment Items -->
                    <div id="paymentsList" class="space-y-2 max-h-40 overflow-y-auto">
                    </div>

                    <!-- Add Payment Row -->
                    <div class="bg-gray-50 p-3 rounded-lg space-y-2">
                        <div class="grid grid-cols-3 gap-2">
                            <select id="paymentMethod" class="border border-gray-300 rounded-lg px-2 py-2 text-xs focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="cash">💵 Cash</option>
                                <option value="easypaisa">📱 Easypaisa</option>
                                <option value="jazzcash">📞 JazzCash</option>
                                <option value="bank">🏦 Bank</option>
                            </select>
                            <input type="number" id="paymentAmount" placeholder="Amount" step="0.01" min="0" class="border border-gray-300 rounded-lg px-2 py-2 text-xs focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <button id="addPaymentRowBtn" class="bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded-lg transition-all">
                                + Add
                            </button>
                        </div>
                        <div id="paymentInfo" class="text-xs text-gray-600">
                            <div class="flex justify-between">
                                <span>Remaining:</span>
                                <span id="remainingAmount">Rs. 0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-4 flex gap-2">
                    <button id="clearCartBtn" class="flex-1 px-3 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-semibold transition-all cursor-pointer">
                        Clear
                    </button>
                    <button id="checkoutBtn" class="flex-1 px-3 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                        Complete Sale
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Recent Sales Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mt-8">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Sales</h3>
        <table class="min-w-full text-sm" id="salesTable">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-3 py-2 text-left">Invoice #</th>
                    <th class="px-3 py-2 text-left">Date</th>
                    <th class="px-3 py-2 text-left">Type</th>
                    <th class="px-3 py-2 text-right">Total</th>
                    <th class="px-3 py-2 text-right">Paid</th>
                    <th class="px-3 py-2 text-right">Remaining</th>
                    <th class="px-3 py-2 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                @php
                    $paid = collect($sale->payments)->sum('amount');
                    $remaining = $sale->total - $paid;
                @endphp
                <tr class="border-b sale-row cursor-pointer hover:bg-blue-50 transition" data-sale='@json($sale)'>
                    <td class="px-3 py-2 font-mono">{{ $sale->invoice_number }}</td>
                    <td class="px-3 py-2">{{ $sale->created_at->format('Y-m-d H:i') }}</td>
                    <td class="px-3 py-2">{{ ucfirst($sale->type) }}</td>
                    <td class="px-3 py-2 text-right">Rs. {{ number_format($sale->total, 2) }}</td>
                    <td class="px-3 py-2 text-right">Rs. {{ number_format($paid, 2) }}</td>
                    <td class="px-3 py-2 text-right @if($remaining > 0) text-red-600 font-bold @else text-green-600 font-bold @endif">Rs. {{ number_format($remaining, 2) }}</td>
                    <td class="px-3 py-2 text-center">
                        <button class="add-payment-btn bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 transition-all text-xs font-semibold" data-sale-id="{{ $sale->id }}" data-total="{{ $sale->total }}" data-remaining="{{ $remaining }}">Add Payment</button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-4 text-gray-500">No sales found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Size Selection (shown when item clicked) -->
<div id="sizeModal" class="fixed inset-0 z-50 items-center justify-center bg-black/30 hidden">
    <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-sm mx-4">
        <h3 id="sizeModalTitle" class="text-lg font-bold mb-4 text-gray-800">Select Size & Quantity</h3>
        
        <div id="sizeOptions" class="space-y-2 mb-6">
            <!-- Sizes dynamically populated -->
        </div>

        <div class="space-y-3">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Quantity</label>
                <div class="flex gap-2 items-center">
                    <button id="decreaseQty" class="bg-gray-200 hover:bg-gray-300 text-gray-700 w-10 h-10 rounded-lg transition-all cursor-pointer font-semibold">−</button>
                    <input type="number" id="quantityInput" value="1" min="1" class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-center focus:ring-2 focus:ring-blue-500">
                    <button id="increaseQty" class="bg-gray-200 hover:bg-gray-300 text-gray-700 w-10 h-10 rounded-lg transition-all cursor-pointer font-semibold">+</button>
                </div>
            </div>

            <div class="bg-blue-50 p-3 rounded-lg">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-700">Unit Price:</span>
                    <span id="unitPrice" class="font-semibold text-gray-900">Rs. 0</span>
                </div>
                <div class="flex justify-between text-lg font-bold mt-2">
                    <span>Subtotal:</span>
                    <span id="lineTotal" class="text-blue-600">Rs. 0</span>
                </div>
            </div>

            <div class="flex gap-2">
                <button id="closeSizeModal" class="flex-1 px-3 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-semibold transition-all cursor-pointer">
                    Cancel
                </button>
                <button id="addToCartBtn" class="flex-1 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition-all cursor-pointer">
                    Add to Cart
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Invoice Details -->
<div id="invoiceModal" class="fixed inset-0 z-50 items-center justify-center bg-black/30 hidden">
    <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md mx-4 max-h-screen overflow-y-auto">
        <button id="closeInvoiceModal" class="float-right text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
        <h3 class="text-lg font-bold mb-4 text-gray-800">Invoice Details</h3>
        <div id="invoiceContent">
            <!-- Content dynamically populated -->
        </div>
    </div>
</div>

<!-- Modal: Payment -->
<div id="paymentModal" class="fixed inset-0 z-50 items-center justify-center bg-black/30 hidden">
    <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-sm mx-4">
        <h3 class="text-lg font-bold mb-4 text-gray-800">Record Payment</h3>
        
        <form id="paymentForm" class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Method</label>
                <select id="paymentMethodSelect" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                    <option value="cash">💵 Cash</option>
                    <option value="easypaisa">📱 Easypaisa</option>
                    <option value="jazzcash">📞 JazzCash</option>
                    <option value="bank">🏦 Bank</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Amount (Rs.)</label>
                <input type="number" id="paymentAmountInput" placeholder="0.00" step="0.01" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Transaction ID (Optional)</label>
                <input type="text" id="transactionIdInput" placeholder="e.g., TXN123456" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div id="paymentModalRemaining" class="text-sm text-gray-600">
                Remaining: <span class="font-bold">Rs. 0</span>
            </div>

            <div class="flex gap-2">
                <button type="button" id="closePaymentModal" class="flex-1 px-3 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-semibold transition-all cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition-all cursor-pointer">
                    Save Payment
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Success -->
<div id="successModal" class="fixed inset-0 z-50 items-center justify-center bg-black/30 hidden">
    <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-sm mx-4 text-center">
        <div class="text-4xl mb-3">✅</div>
        <h3 class="text-lg font-bold text-gray-800 mb-2">Sale Completed!</h3>
        <div class="bg-gray-50 p-3 rounded-lg mb-4">
            <p class="text-sm text-gray-600">Invoice #</p>
            <p id="successInvoiceNumber" class="text-2xl font-mono font-bold text-gray-900">INV-...</p>
        </div>
        <p id="successTotal" class="text-lg font-semibold text-gray-900 mb-4">Total: Rs. 0</p>
        <button id="newSaleBtn" class="w-full px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition-all cursor-pointer">
            New Sale
        </button>
    </div>
</div>
<!-- Toast Notification -->
<div id="toast" class="fixed bottom-4 right-4 hidden px-4 py-3 rounded-lg text-white text-sm shadow-lg z-50"></div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // State
    let cart = [];
    let currentSaleType = '';
    let payments = [];
    let selectedItemData = null;

    // Elements
    const itemSearch = document.getElementById('itemSearch');
    const itemsList = document.getElementById('itemsList');
    const cartItems = document.getElementById('cartItems');
    const cartEmpty = document.getElementById('cartEmpty');
    const subtotalEl = document.getElementById('subtotal');
    const totalEl = document.getElementById('total');
    const paymentsList = document.getElementById('paymentsList');
    const remainingAmount = document.getElementById('remainingAmount');
    const clearCartBtn = document.getElementById('clearCartBtn');
    const checkoutBtn = document.getElementById('checkoutBtn');
    const toast = document.getElementById('toast');

    // Modals
    const sizeModal = document.getElementById('sizeModal');
    const closeSizeModal = document.getElementById('closeSizeModal');
    const invoiceModal = document.getElementById('invoiceModal');
    const closeInvoiceModal = document.getElementById('closeInvoiceModal');
    const paymentModal = document.getElementById('paymentModal');
    const closePaymentModal = document.getElementById('closePaymentModal');
    const successModal = document.getElementById('successModal');

    // Size Modal elements
    const sizeModalTitle = document.getElementById('sizeModalTitle');
    const sizeOptions = document.getElementById('sizeOptions');
    const quantityInput = document.getElementById('quantityInput');
    const decreaseQty = document.getElementById('decreaseQty');
    const increaseQty = document.getElementById('increaseQty');
    const unitPrice = document.getElementById('unitPrice');
    const lineTotal = document.getElementById('lineTotal');
    const addToCartBtn = document.getElementById('addToCartBtn');

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

    // Get all items as data (precomputed in PHP to avoid Blade parsing issues)
    @php
        $__itemsForJs = $items->map(function($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'image' => $item->image ? asset('storage/' . $item->image) : null,
                'sizes' => $item->sizes->map(function($size) {
                    return [
                        'id' => $size->id,
                        'size' => $size->size,
                        'price' => $size->price,
                    ];
                })->values()->all(),
            ];
        })->values()->all();
    @endphp

    const allItemsData = {!! json_encode($__itemsForJs) !!};

    // Search items
    itemSearch.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase();
        document.querySelectorAll('.item-card').forEach(card => {
            const name = card.dataset.itemName.toLowerCase();
            card.style.display = name.includes(query) ? '' : 'none';
        });
    });

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

    // Modal-based size selection removed: items are added with default size and can be edited inline in cart

    // Update cart UI
    const updateCartUI = () => {
        const subtotal = cart.reduce((sum, item) => sum + item.total, 0);
        subtotalEl.textContent = `Rs. ${subtotal.toLocaleString()}`;
        totalEl.textContent = `Rs. ${subtotal.toLocaleString()}`;
        checkoutBtn.disabled = cart.length === 0;

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
                deliverySection.classList.remove('hidden');
            } else {
                deliverySection.classList.add('hidden');
                customerName.value = '';
                customerPhone.value = '';
                customerAddress.value = '';
            }
        });
    });

    // Payment row management
    addPaymentRowBtn.addEventListener('click', () => {
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
        remainingAmount.textContent = `Rs. ${remaining.toLocaleString()}`;
    };

    // Clear cart
    clearCartBtn.addEventListener('click', () => {
        if (confirm('Clear cart?')) {
            cart = [];
            payments = [];
            currentSaleType = '';
            saleTypeBtns.forEach(b => b.classList.remove('border-blue-500', 'bg-blue-50'));
            deliverySection.classList.add('hidden');
            updateCartUI();
            updatePaymentsUI();
            showToast('Cart cleared', 'success');
        }
    });

    // Checkout
    checkoutBtn.addEventListener('click', async () => {
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
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
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

            // Show success modal
            document.getElementById('successInvoiceNumber').textContent = sale.invoice_number;
            document.getElementById('successTotal').textContent = `Total: Rs. ${total.toLocaleString()}`;
            successModal.classList.remove('hidden');
            successModal.classList.add('flex');

            // Reset
            cart = [];
            payments = [];
            currentSaleType = '';
            saleTypeBtns.forEach(b => b.classList.remove('border-blue-500', 'bg-blue-50'));
            deliverySection.classList.add('hidden');
            updateCartUI();
            updatePaymentsUI();

        } catch (error) {
            showToast(error.message, 'error');
        } finally {
            checkoutBtn.disabled = false;
        }
    });

    // Success modal - New Sale button
    document.getElementById('newSaleBtn').addEventListener('click', () => {
        successModal.classList.add('hidden');
        successModal.classList.remove('flex');
        location.reload();
    });

    // Sales table - show invoice
    document.getElementById('salesTable').addEventListener('click', (e) => {
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
        const status = remaining > 0 ? (paid > 0 ? 'Partial' : 'Unpaid') : 'Paid';

        let itemsHtml = '';
        if (sale.cart && Array.isArray(sale.cart)) {
            itemsHtml = `<table class='min-w-full text-xs mb-3 border-collapse'><thead><tr class='border-b'><th class='text-left py-1'>Item</th><th class='text-right'>Qty</th><th class='text-right'>Price</th></tr></thead><tbody>`;
            sale.cart.forEach(item => {
                // Support multiple possible shapes for cart items saved in DB
                let name = item.name || item.item_name || item.title || (item.item && item.item.name) || 'Unknown';
                const qty = (item.quantity !== undefined && item.quantity !== null) ? item.quantity : (item.qty !== undefined && item.qty !== null ? item.qty : 1);
                // Prefer explicit unit price, otherwise derive from total/qty
                const unitPrice = (item.price !== undefined && item.price !== null) ? parseFloat(item.price) : (item.total && qty ? parseFloat(item.total) / qty : 0);
                const lineTotal = (item.total !== undefined && item.total !== null) ? parseFloat(item.total) : (unitPrice * qty);

                // If name is unknown, attempt to resolve from known item ids using allItemsData
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
        invoiceModal.classList.remove('hidden');
        invoiceModal.classList.add('flex');
    };

    closeInvoiceModal.addEventListener('click', () => {
        invoiceModal.classList.add('hidden');
        invoiceModal.classList.remove('flex');
    });

    invoiceModal.addEventListener('click', (e) => {
        if (e.target === invoiceModal) closeInvoiceModal.click();
    });

    // Add payment for existing sale
    document.addEventListener('click', async (e) => {
        if (e.target.classList.contains('add-payment-btn')) {
            const saleId = e.target.getAttribute('data-sale-id');
            const total = parseFloat(e.target.getAttribute('data-total'));
            const remaining = parseFloat(e.target.getAttribute('data-remaining'));

            window.currentSaleId = saleId;
            window.currentRemaining = remaining;

            document.getElementById('paymentModalRemaining').textContent = `Remaining: Rs. ${remaining.toLocaleString()}`;
            document.getElementById('paymentAmountInput').value = remaining.toFixed(2);
            document.getElementById('paymentAmountInput').max = remaining.toFixed(2);

            paymentModal.classList.remove('hidden');
            paymentModal.classList.add('flex');
        }
    });

    closePaymentModal.addEventListener('click', () => {
        paymentModal.classList.add('hidden');
        paymentModal.classList.remove('flex');
    });

    paymentModal.addEventListener('click', (e) => {
        if (e.target === paymentModal) closePaymentModal.click();
    });

    paymentForm.addEventListener('submit', async (e) => {
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
            const response = await fetch('/api/payments', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
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

    updateCartUI();
});
</script>
@endsection