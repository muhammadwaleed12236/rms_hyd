@extends('layouts.app')

@section('page-title', 'Point of Sale')

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

    .cart-item.removing { animation: slideOut 0.2s ease-out forwards; }
    .toast { animation: slideIn 0.3s ease-out; }
    .toast.hide { animation: slideOut 0.3s ease-out forwards; }
    
    .item-card {
        @apply transition-all duration-200 cursor-pointer hover:shadow-lg hover:-translate-y-1 active:scale-95;
    }

    .category-tab {
        @apply px-4 py-2 rounded-lg font-semibold text-sm transition-all whitespace-nowrap;
    }

    .category-tab.active {
        @apply bg-blue-600 text-white;
    }

    .category-tab:not(.active) {
        @apply bg-gray-200 text-gray-700 hover:bg-gray-300;
    }
</style>

<div class="h-screen flex flex-col bg-gray-50">
    <!-- Header Bar -->
    <div class="bg-white shadow-sm border-b border-gray-200 px-4 py-3 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Point of Sale</h1>
            <p class="text-xs text-gray-500">{{ now()->format('d M Y • H:i') }}</p>
        </div>
        <div class="text-right">
            <div class="text-2xl font-bold text-blue-600" id="cartBadge">0</div>
            <p class="text-xs text-gray-600">Items in cart</p>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="flex-1 overflow-hidden flex gap-4 p-4">
        <!-- Left: Items Grid -->
        <div class="flex-1 flex flex-col gap-3 min-w-0">
            <!-- Category Tabs (Horizontal Scroll) -->
            <div class="flex gap-2 overflow-x-auto pb-2 bg-white rounded-lg p-3 shadow-sm">
                <button class="category-tab active" data-category="all">
                    🔽 All Items
                </button>
                @foreach($categories as $category)
                <button class="category-tab" data-category="{{ $category->id }}">
                    {{ $category->name }}
                </button>
                @endforeach
            </div>

            <!-- Items Grid -->
            <div class="flex-1 overflow-y-auto bg-white rounded-lg shadow-sm p-4">
                <div id="itemsGrid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    @forelse($items as $item)
                    <div class="item-card group bg-white border-2 border-gray-200 rounded-xl p-3" data-category="{{ $item->subcategory->category_id ?? 'all' }}" data-item-id="{{ $item->id }}" data-item-name="{{ $item->name }}">
                        <!-- Item Image -->
                        <div class="aspect-square bg-gray-100 rounded-lg mb-3 flex items-center justify-center overflow-hidden relative">
                            @if($item->image)
                                <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                            @else
                                <div class="text-4xl">🍽️</div>
                            @endif
                        </div>

                        <!-- Item Info -->
                        <h3 class="font-bold text-gray-900 text-sm line-clamp-2">{{ $item->name }}</h3>
                        <p class="text-xs text-gray-500 mt-1">{{ $item->subcategory->name ?? 'N/A' }}</p>

                        <!-- Price -->
                        @if($item->sizes->count() > 0)
                        <div class="mt-2 pt-2 border-t border-gray-200">
                            <p class="text-blue-600 font-bold text-sm">
                                Rs. {{ number_format($item->sizes->first()->price, 0) }}
                            </p>
                            @if($item->sizes->count() > 1)
                                <p class="text-xs text-gray-500">+{{ $item->sizes->count() - 1 }} more size{{ $item->sizes->count() - 1 > 1 ? 's' : '' }}</p>
                            @endif
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="col-span-full text-center py-12 text-gray-500">
                        <p class="text-lg">📭 No items available</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right: Cart & Checkout (Fixed) -->
        <div class="w-96 flex flex-col gap-4 bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Cart Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-4 py-3">
                <h2 class="font-bold text-lg">Order Cart</h2>
            </div>

            <!-- Cart Items -->
            <div id="cartItems" class="flex-1 overflow-y-auto px-4 space-y-2">
                <div id="cartEmpty" class="flex items-center justify-center h-full text-gray-500">
                    <div class="text-center">
                        <p class="text-3xl mb-2">🛒</p>
                        <p>No items in cart</p>
                    </div>
                </div>
            </div>

            <!-- Totals Section -->
            <div class="border-t border-gray-200 px-4 py-3 space-y-2">
                <div class="flex justify-between text-gray-600">
                    <span>Subtotal:</span>
                    <span id="subtotal">Rs. 0</span>
                </div>
                <div class="flex justify-between text-lg font-bold text-gray-900 bg-blue-50 -mx-4 px-4 py-2">
                    <span>Total:</span>
                    <span id="total">Rs. 0</span>
                </div>
            </div>

            <!-- Sale Type Selection -->
            <div class="px-4 space-y-2">
                <label class="block text-xs font-bold text-gray-700 uppercase">Order Type</label>
                <div class="grid grid-cols-3 gap-2">
                    <button class="sale-type-btn border-2 border-gray-300 rounded-lg py-2 text-xs font-semibold hover:border-blue-500 hover:bg-blue-50 transition-all" data-type="dine_in">
                        🍽️ Dine In
                    </button>
                    <button class="sale-type-btn border-2 border-gray-300 rounded-lg py-2 text-xs font-semibold hover:border-blue-500 hover:bg-blue-50 transition-all" data-type="take_away">
                        🛍️ Takeaway
                    </button>
                    <button class="sale-type-btn border-2 border-gray-300 rounded-lg py-2 text-xs font-semibold hover:border-blue-500 hover:bg-blue-50 transition-all" data-type="delivery">
                        🚚 Delivery
                    </button>
                </div>
            </div>

            <!-- Delivery Fields (shown conditionally) -->
            <div id="deliverySection" class="hidden px-4 space-y-2 pt-2 border-t">
                <input type="text" id="customerName" placeholder="Customer Name" class="w-full border border-gray-300 rounded-lg px-2 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none">
                <input type="tel" id="customerPhone" placeholder="Phone Number" class="w-full border border-gray-300 rounded-lg px-2 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none">
                <textarea id="customerAddress" placeholder="Delivery Address" rows="2" class="w-full border border-gray-300 rounded-lg px-2 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none"></textarea>
            </div>

            <!-- Payment Section -->
            <div class="px-4 space-y-2 border-t pt-3">
                <label class="text-xs font-bold text-gray-700 uppercase">Payments</label>
                
                <!-- Payment Items -->
                <div id="paymentsList" class="space-y-1 max-h-24 overflow-y-auto"></div>

                <!-- Add Payment Row -->
                <div class="bg-gray-50 p-2 rounded-lg space-y-2">
                    <div class="grid grid-cols-3 gap-1">
                        <select id="paymentMethod" class="border border-gray-300 rounded-lg px-2 py-1 text-xs focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="cash">💵 Cash</option>
                            <option value="easypaisa">📱 Easypaisa</option>
                            <option value="jazzcash">📞 JazzCash</option>
                            <option value="bank">🏦 Bank</option>
                        </select>
                        <input type="number" id="paymentAmount" placeholder="Amount" step="0.01" min="0" class="border border-gray-300 rounded-lg px-2 py-1 text-xs focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <button id="addPaymentRowBtn" class="bg-green-600 hover:bg-green-700 text-white text-xs font-bold rounded-lg transition-all">
                            +
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
            <div class="px-4 pb-4 flex gap-2">
                <button id="clearCartBtn" class="flex-1 px-3 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-bold transition-all cursor-pointer">
                    Clear
                </button>
                <button id="checkoutBtn" class="flex-1 px-3 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    Complete Sale
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Size Selection -->
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

<!-- Modal: Receipt -->
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
                'category_id' => $item->subcategory->category_id ?? null,
                'subcategory_id' => $item->subcategory_id,
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
