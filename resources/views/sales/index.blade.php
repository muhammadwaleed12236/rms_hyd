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

    /* .cart-item { animation: slideIn 0.3s ease-out; } */
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

<!-- Modal: Receipt (Invoice + Token Combined) -->
<div id="receiptModal" class="fixed inset-0 z-50 items-center justify-center bg-black/30 hidden">
    <div class="bg-white rounded-lg shadow-lg p-4 w-full max-w-sm mx-4 max-h-screen overflow-y-auto">
        <button id="closeReceiptModal" class="float-right text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
        <h3 class="text-lg font-bold mb-3 text-gray-800">Receipt</h3>
        
        <!-- Compact Receipt (like thermal printer) -->
        <div id="printableReceipt" class="bg-gray-50 p-3 border border-gray-300 rounded font-mono text-xs overflow-y-auto max-h-96"></div>

        <div class="flex gap-2 mt-4">
            <button id="printReceiptBtn" class="flex-1 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded transition-all">
                🖨️ Print
            </button>
            <button id="closeReceiptBtn" class="flex-1 px-3 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded font-semibold transition-all cursor-pointer">
                Done
            </button>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="fixed bottom-4 right-4 hidden px-4 py-3 rounded-lg text-white text-sm shadow-lg z-50"></div>

@push('scripts')
    <script>
        // Inject server-side data for the sales page into window scope
        window.__ALL_ITEMS_DATA = {!! json_encode($__itemsForJs ?? ($items->map(function($item) {
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
        })->values()->all())) !!};
        window.CSRF_TOKEN = '{{ csrf_token() }}';
    </script>
    @vite('resources/js/sales.js')
@endpush

@endsection