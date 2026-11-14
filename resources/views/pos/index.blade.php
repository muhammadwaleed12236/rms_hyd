@extends('layouts.app')

@section('page-title', 'Point of Sale')

@section('content')
<style>
/* Strong print rules: use ad-hoc wrapper for printing and remove margins */
@media print {
  @page { margin: 0; size: 80mm auto; }

  html, body { height:100% !important; margin:0 !important; padding:0 !important; background:#fff !important; }

  /* hide everything except our ad-hoc print wrapper */
  body * { visibility: hidden !important; }
  #adHocPrintContainerWrapper,
  #adHocPrintContainerWrapper * { visibility: visible !important; }

  /* wrapper fills printable area and is anchored to the top-left (start of page) */
  #adHocPrintContainerWrapper {
    position: fixed !important;
    left: 0 !important;
    top: 0 !important;
    width: 80mm !important;
    max-width: 80mm !important;
    height: auto !important;
    overflow: visible !important;
    margin: 0 !important;
    padding: 0 !important;
    background: #fff !important;
    box-sizing: border-box !important;
    -webkit-print-color-adjust: exact !important;
  }

  /* Only receipt page (no token) — less top padding to reduce top space */
  .receipt-page { display: block !important; margin: 0 !important; padding: 2px 6px 8px 6px !important; page-break-after: always !important; }

  /* remove any default margins in headings inside receipt */
  #adHocPrintContainer .receipt-page div { margin: 0 !important; }

  .no-print { display: none !important; }
}
@media print {
  body.for-printing #adHocPrintContainerWrapper { visibility: visible !important; pointer-events: auto !important; }
}
</style>


<div class="min-h-screen bg-gray-50 flex flex-col md:flex-row">
  <!-- Middle Panel: Products Grid -->
  <div class="flex-1 p-6 overflow-y-auto pb-40">
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Menu</h1>
        <p class="text-gray-600 mt-1 text-sm">Tap / Click items to add to cart. Use categories or search to find items fast.</p>
      </div>
      <div class="flex items-center gap-3">
        <div class="hidden md:block text-sm text-gray-500">Showing <span id="visibleCount">0</span> items</div>
      </div>
    </div>

    <!-- Order Type + Categories + Search Row -->
    <div class="mb-4 bg-white p-4 rounded-md shadow-sm border border-gray-200">
      <div class="grid md:grid-cols-3 gap-3 items-start">
        <div class="md:col-span-1">
          <label class="block text-xs font-semibold text-gray-700 uppercase mb-2">Order Type</label>
          <div class="flex gap-2">
            <button class="sale-type-btn px-3 py-2 rounded-md border text-xs font-semibold" data-type="dine_in">🍽️ Dine In</button>
            <button class="sale-type-btn px-3 py-2 rounded-md border text-xs font-semibold" data-type="take_away">🛍️ Takeaway</button>
            <button class="sale-type-btn px-3 py-2 rounded-md border text-xs font-semibold" data-type="delivery">🚚 Delivery</button>
          </div>

          <div id="deliverySection" class="hidden mt-4 pt-3 border-t border-gray-100">
            <div class="grid grid-cols-2 gap-2">
              <input id="customerName" type="text" placeholder="Customer name" class="border px-2 py-1 text-sm rounded">
              <input id="customerPhone" type="tel" placeholder="03XXXXXXXXX" class="border px-2 py-1 text-sm rounded">
              <input id="deliveryCharge" type="number" placeholder="Delivery Rs." class="border px-2 py-1 text-sm rounded">
              <textarea id="customerAddress" rows="1" placeholder="Address" class="border px-2 py-1 text-sm rounded"></textarea>
            </div>
          </div>
        </div>

        <div class="md:col-span-1">
          <label class="block text-xs font-semibold text-gray-700 uppercase mb-2">Categories</label>
          <div id="categoriesRow" class="flex gap-2 overflow-x-auto pb-1">
            @foreach($categories as $cat)
              <button class="category-pill px-3 py-1 rounded-full border text-sm whitespace-nowrap" data-category-id="{{ $cat->id }}">{{ $cat->name }}</button>
            @endforeach
            <button class="category-pill px-3 py-1 rounded-full border text-sm whitespace-nowrap" data-category-id="all">All</button>
          </div>
        </div>

        <div class="md:col-span-1">
          <label class="block text-xs font-semibold text-gray-700 uppercase mb-2">Search</label>
          <div class="relative">
            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="fa-solid fa-magnifying-glass"></i></div>
            <input id="posSearchInput" type="search" placeholder="Search items by name or subcategory..." class="w-full pl-10 pr-3 py-2 bg-white shadow-sm border border-gray-200 rounded-md text-sm focus:ring-2 focus:ring-gray-200">
          </div>
          <p id="noResultsMessage" class="text-sm text-gray-500 mt-2 hidden">No items match your search.</p>
        </div>

      </div>
    </div>

    <!-- Items Grid -->
    <div id="categorizedItems" class="space-y-6">
      @forelse($categories as $category)
        @php $categoryItems = $items->filter(fn($item) => $item->subcategory->category_id === $category->id); @endphp
        @if($categoryItems->count() > 0)
          <div class="category-section">
            <h2 class="text-2xl font-semibold text-gray-900 mb-3">{{ $category->name }}</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
              @foreach($categoryItems as $item)
                <div class="product-card group bg-white rounded-lg shadow-sm transition-all duration-200 cursor-pointer overflow-hidden border border-gray-200 flex flex-col h-full p-2" data-item-id="{{ $item->id }}" data-category="{{ $category->id }}" data-item-name="{{ $item->name }}">
                  <div class="aspect-video bg-gray-100 overflow-hidden rounded-md flex items-center justify-center">
                    @if($item->image)
                      <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                    @else
                      <div class="text-4xl opacity-30">🍽️</div>
                    @endif
                  </div>
                  <div class="mt-2 flex-1 flex flex-col justify-between">
                    <div>
                      <h3 class="font-semibold text-gray-900 text-sm line-clamp-2">{{ $item->name }}</h3>
                      <p class="text-xs text-gray-500 mt-1">{{ $item->subcategory->name ?? '' }}</p>
                    </div>

                    @if($item->sizes->count() > 0)
                      <div class="mt-3 pt-2 border-t border-gray-100 flex items-center justify-between">
                        <div class="text-sm font-bold">Rs. {{ number_format($item->sizes->first()->price, 0) }}</div>
                        <button class="add-to-cart-btn bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs" data-item-id="{{ $item->id }}">Add</button>
                      </div>
                    @else
                      <div class="mt-3 text-sm text-gray-500">No price</div>
                    @endif
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        @endif
      @empty
        <div class="text-center py-12">
          <p class="text-gray-500 text-lg">No categories available</p>
        </div>
      @endforelse
    </div>
  </div>

  <!-- Right Panel: CART (fixed) -->
</div>

<!-- Bottom Cart Bar (compact + expanded) -->
<div id="bottomCartBar" class="fixed left-0 right-0 bottom-0 z-40 pointer-events-none">
  <div id="compactCartBar" class="mx-auto max-w-6xl px-4 py-3 bg-white border-t border-gray-200 shadow-lg rounded-t-lg flex items-center justify-between gap-3 pointer-events-auto" style="display:none;">
    <div class="flex items-center gap-3">
      <button id="openCartBtn" class="flex items-center gap-2 bg-gray-800 text-white px-3 py-1 rounded text-sm">🛒 Cart <span id="compactCartCount" class="ml-2 bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs">0</span></button>
      <div class="text-sm text-gray-700">
        <div id="compactCartSummary">No items</div>
        <div id="compactCartTotal" class="font-bold">Rs. 0</div>
      </div>
    </div>
    <div class="flex items-center gap-2">
      <!-- Changed label to Print (direct save + print) -->
      <button id="checkoutBottomBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm" disabled>Print</button>
      <button id="clearBottomCartBtn" class="bg-gray-200 hover:bg-gray-300 px-3 py-1 rounded text-sm">Clear</button>
    </div>
  </div>

  <div id="expandedCartPanel" class="fixed left-0 right-0 bottom-0 z-50 bg-white border-t border-gray-200 shadow-xl p-4 transform translate-y-full transition-transform duration-300" style="max-height:60vh; overflow-y:auto;">
    <div class="flex items-center justify-between mb-3">
      <h3 class="text-lg font-bold">Order Cart</h3>
      <div class="flex items-center gap-2">
        <button id="closeExpandedCart" class="px-2 py-1 rounded bg-gray-100 hover:bg-gray-200">Close</button>
      </div>
    </div>

    <div id="expandedCartItems" class="space-y-2">
      <div class="text-center text-gray-500">Cart is empty</div>
    </div>

    <div class="mt-3 border-t pt-3">
      <!-- Payments area -->
      <label class="text-xs font-bold text-gray-700 uppercase">Payments</label>
      <div id="paymentsList" class="space-y-1 max-h-32 overflow-y-auto mb-2"></div>

      <div class="bg-gray-50 p-2 rounded-lg space-y-2 mb-3">
        <div class="grid grid-cols-3 gap-1">
          <select id="paymentMethod" class="border border-gray-300 rounded px-2 py-1 text-xs">
            <option value="cash">💵 Cash</option>
            <option value="easypaisa">📱 Easypaisa</option>
            <option value="jazzcash">📞 JazzCash</option>
            <option value="bank">🏦 Bank</option>
          </select>
          <input type="number" id="paymentAmount" placeholder="Amount" step="0.01" min="0" class="border border-gray-300 rounded-lg px-2 py-1 text-xs">
          <button id="addPaymentRowBtn" class="bg-green-600 hover:bg-green-700 text-white text-xs font-bold rounded-lg">+</button>
        </div>
        <div class="text-xs text-gray-600 flex justify-between">
          <span>Remaining:</span>
          <span id="remainingAmount">Rs. 0</span>
        </div>
      </div>

      <div class="flex justify-between">
        <span class="text-sm text-gray-600">Subtotal</span>
        <span id="expandedSubtotal" class="font-bold text-gray-900">Rs. 0</span>
      </div>
      <div class="flex justify-between mt-2">
        <span class="text-sm text-gray-600">Total</span>
        <span id="expandedTotal" class="font-bold text-gray-900">Rs. 0</span>
      </div>

      <div class="mt-3 flex gap-2">
        <!-- Changed to Print -->
        <button id="expandedCheckoutBtn" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded text-sm" disabled>Print</button>
        <button id="expandedClearBtn" class="flex-1 bg-gray-200 hover:bg-gray-300 py-2 rounded text-sm">Clear</button>
      </div>
    </div>
  </div>
</div>

<!-- Hidden ad-hoc print wrapper will be injected dynamically by JS at print time -->
<!-- Note: token modal removed as requested -->

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
/* Blade-injected data */
window.POS_INFO = window.POS_INFO || {!! json_encode([ 'shop_name' => 'Akram Birynai and Qorma House', 'branch' => 'Main AutoBhan road Hyderabad','phone' => 'Phone: +92 300 0611808' ]) !!};
window.__ALL_ITEMS_DATA = window.__ALL_ITEMS_DATA || {!! json_encode($items->map(function($item) {
  return [
    'id' => $item->id,
    'name' => $item->name,
    'image' => $item->image ? asset('storage/' . $item->image) : null,
    'category_id' => $item->subcategory->category_id ?? null,
    'subcategory_id' => $item->subcategory_id,
    'subcategory_name' => $item->subcategory->name ?? '',
    'sizes' => $item->sizes->map(function($size) { return [ 'id' => $size->id, 'size' => $size->size, 'price' => (float) $size->price ]; })->values()->all(),
  ];
})->values()->all()) !!};
window.CSRF_TOKEN = window.CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

document.addEventListener('DOMContentLoaded', function() {
  const qs = s => document.querySelector(s);
  const qsa = s => Array.from(document.querySelectorAll(s));
  const items = window.__ALL_ITEMS_DATA || [];

  /* DOM refs */
  const compactCartBar = qs('#compactCartBar');
  const compactCartCount = qs('#compactCartCount');
  const compactCartSummary = qs('#compactCartSummary');
  const compactCartTotal = qs('#compactCartTotal');
  const openCartBtn = qs('#openCartBtn');
  const expandedPanel = qs('#expandedCartPanel');
  const closeExpandedCart = qs('#closeExpandedCart');
  const expandedCartItems = qs('#expandedCartItems');
  const expandedSubtotal = qs('#expandedSubtotal');
  const expandedTotal = qs('#expandedTotal');
  const expandedCheckoutBtn = qs('#expandedCheckoutBtn');
  const expandedClearBtn = qs('#expandedClearBtn');
  const checkoutBottomBtn = qs('#checkoutBottomBtn');
  const clearBottomCartBtn = qs('#clearBottomCartBtn');
  const paymentsList = qs('#paymentsList');
  const paymentMethod = qs('#paymentMethod');
  const paymentAmount = qs('#paymentAmount');
  const addPaymentRowBtn = qs('#addPaymentRowBtn');
  const remainingAmountEl = qs('#remainingAmount');
  const posSearchInput = qs('#posSearchInput');
  const categoryPills = qsa('.category-pill');
  const saleTypeBtns = qsa('.sale-type-btn');
  const deliverySection = qs('#deliverySection');

  /* state */
  let cart = [];
  let payments = [];
  let lastSale = null;

  /* helpers */
  const fmt = new Intl.NumberFormat('en-US',{ maximumFractionDigits:0 });
  const formatNumber = v => fmt.format(Math.round(Number(v) || 0));
  function escapeHtml(str){ return String(str||'').replace(/[&<>"']/g, m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }

  function showCompactBarIfNeeded(){ if (!compactCartBar) return; compactCartBar.style.display = cart.length ? 'flex' : 'none'; }
  function ping(el){ if(!el) return; el.classList.add('animate-ping'); setTimeout(()=>el.classList.remove('animate-ping'),600); }

  /* categories & search */
  let activeCategory = 'all';
  function filterAndShowItems(){
    const q = (posSearchInput?.value || '').toLowerCase();
    let visible = 0;
    qsa('.category-section').forEach(section=>{
      const cards = Array.from(section.querySelectorAll('.product-card'));
      let any = false;
      cards.forEach(card=>{
        const name = (card.dataset.itemName||'').toLowerCase();
        const cat = card.dataset.category;
        const matchCat = activeCategory === 'all' || String(activeCategory) === String(cat);
        const matchSearch = name.includes(q);
        if (matchCat && matchSearch){ card.style.display=''; any = true; visible++; } else card.style.display = 'none';
      });
      section.style.display = any ? '' : 'none';
    });
    const visibleCount = qs('#visibleCount'); if (visibleCount) visibleCount.textContent = visible;
    const noRes = qs('#noResultsMessage'); if (noRes) noRes.style.display = visible === 0 ? 'block' : 'none';
  }
  categoryPills.forEach(btn=>btn.addEventListener('click', ()=>{
    categoryPills.forEach(b=>b.classList.remove('bg-gray-800','text-white'));
    btn.classList.add('bg-gray-800','text-white');
    activeCategory = btn.dataset.categoryId;
    filterAndShowItems();
  }));
  posSearchInput?.addEventListener('input', filterAndShowItems);

  /* sale type */
  saleTypeBtns.forEach(b=>b.addEventListener('click', ()=>{
    saleTypeBtns.forEach(x=>x.classList.remove('bg-gray-800','text-white'));
    b.classList.add('bg-gray-800','text-white');
    const t = b.dataset.type;
    if (t === 'delivery') deliverySection?.classList.remove('hidden'); else deliverySection?.classList.add('hidden');
  }));
  (function(){ const d = Array.from(saleTypeBtns).find(b=>b.dataset.type==='take_away'); if(d) d.classList.add('bg-gray-800','text-white'); })();

  /* size modal (floating) */
  const sizeModal = document.createElement('div');
  sizeModal.id = 'sizeModal';
  sizeModal.style.position = 'fixed';
  sizeModal.style.right = '92px';
  sizeModal.style.bottom = '16px';
  sizeModal.style.zIndex = 60;
  sizeModal.style.display = 'none';
  sizeModal.innerHTML = `
    <div class="bg-white border rounded shadow-lg p-3">
      <div class="flex justify-between items-start mb-2">
        <div>
          <div id="sizeModalTitle" class="font-semibold text-sm"></div>
          <div id="sizeModalSub" class="text-xs text-gray-500"></div>
        </div>
        <button id="sizeModalClose" class="text-gray-500 hover:text-gray-700">&times;</button>
      </div>
      <div id="sizeOptions" class="space-y-2 mb-2"></div>
      <div class="flex gap-2 items-center">
        <input id="sizeQty" type="number" min="1" value="1" class="border px-2 py-1 w-20 text-sm rounded" />
        <button id="sizeAddBtn" class="bg-green-600 text-white px-3 py-1 rounded text-sm">Add to cart</button>
      </div>
    </div>
  `;
  document.body.appendChild(sizeModal);

  const sizeModalTitle = qs('#sizeModalTitle');
  const sizeModalSub = qs('#sizeModalSub');
  const sizeOptions = qs('#sizeOptions');
  const sizeQty = qs('#sizeQty');
  const sizeAddBtnEl = qs('#sizeAddBtn');
  qs('#sizeModalClose')?.addEventListener('click', ()=>{ sizeModal.style.display = 'none'; currentModalItem = null; });

  let currentModalItem = null;
  function showSizeModal(item){
    currentModalItem = item;
    sizeModalTitle.textContent = item.name;
    sizeModalSub.textContent = item.subcategory_name || '';
    sizeOptions.innerHTML = '';
    sizeQty.value = 1;
    item.sizes.forEach((s, idx)=>{
      const div = document.createElement('div');
      div.className = 'flex items-center gap-2';
      div.innerHTML = `
        <label style="width:100%" class="flex items-center gap-2 cursor-pointer">
          <input type="radio" name="sizeChoice" value="${s.id}" ${idx===0? 'checked' : ''} />
          <div class="flex-1">
            <div class="font-semibold text-sm">${s.size}</div>
            <div class="text-xs text-gray-500">Rs. ${formatNumber(s.price)}</div>
          </div>
        </label>
      `;
      sizeOptions.appendChild(div);
    });
    sizeModal.style.display = 'block';
  }

  sizeAddBtnEl?.addEventListener('click', ()=> {
    if (!currentModalItem){ sizeModal.style.display='none'; return; }
    const sel = sizeOptions.querySelector('input[name="sizeChoice"]:checked');
    if (!sel) return Swal.fire({ icon:'error', title:'Select size' });
    const sizeId = sel.value;
    const sizeObj = currentModalItem.sizes.find(s=>String(s.id)===String(sizeId));
    const qty = Math.max(1, parseInt(sizeQty.value||1));
    addToCart({ item_id: currentModalItem.id, name: currentModalItem.name, size_id: sizeObj.id, size_label: sizeObj.size, price: Number(sizeObj.price), image: currentModalItem.image, qty });
    sizeModal.style.display = 'none'; currentModalItem = null;
  });

  /* add to cart */
  document.body.addEventListener('click', e=>{
    const addBtn = e.target.closest('.add-to-cart-btn');
    if (addBtn){
      const id = addBtn.dataset.itemId;
      const item = items.find(i=>String(i.id)===String(id));
      if (!item) return Swal.fire({ icon:'error', title:'Item missing' });
      if (item.sizes && item.sizes.length > 1){ showSizeModal(item); return; }
      const size = (item.sizes && item.sizes[0]) ? item.sizes[0] : { id:null, size:'Default', price:0 };
      addToCart({ item_id:item.id, name:item.name, size_id:size.id, size_label:size.size, price:Number(size.price), image:item.image, qty:1 });
      return;
    }

    const card = e.target.closest('.product-card');
    if (card && !e.target.closest('.add-to-cart-btn')){
      const id = card.dataset.itemId;
      const item = items.find(i=>String(i.id)===String(id));
      if (!item) return;
      if (item.sizes && item.sizes.length > 1){ showSizeModal(item); return; }
      const size = (item.sizes && item.sizes[0]) ? item.sizes[0] : { id:null, size:'Default', price:0 };
      addToCart({ item_id:item.id, name:item.name, size_id:size.id, size_label:size.size, price:Number(size.price), image:item.image, qty:1 });
    }
  });

  function addToCart(obj){
    const found = cart.find(ci => String(ci.item_id) === String(obj.item_id) && String(ci.size_id) === String(obj.size_id));
    if (found) found.qty += obj.qty; else cart.push({ item_id: obj.item_id, size_id: obj.size_id, size_label: obj.size_label, name: obj.name, price: obj.price, image: obj.image, qty: obj.qty });
    renderCartUI();
    ping(compactCartCount);
    if (typeof Swal !== 'undefined') Swal.fire({ toast:true, position:'top-end', icon:'success', title:'Added to cart', showConfirmButton:false, timer:700 });
  }

  /* render cart UI */
  function renderCartUI(){
    if (!expandedCartItems) return;
    expandedCartItems.innerHTML = '';
    if (cart.length === 0){
      expandedCartItems.innerHTML = '<div class="text-center text-gray-500">Cart is empty</div>';
    } else {
      cart.forEach((ci, idx)=>{
        const row = document.createElement('div');
        row.className = 'flex items-center justify-between p-2 bg-gray-50 rounded';
        row.dataset.idx = idx;
        row.innerHTML = `
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-white rounded overflow-hidden">${ci.image? `<img src="${ci.image}" class="w-full h-full object-cover">` : '🍽️'}</div>
            <div>
              <div class="text-sm font-semibold">${escapeHtml(ci.name)}</div>
              <div class="text-xs text-gray-500">${ci.size_label? escapeHtml(ci.size_label) + ' • ' : ''}Rs. ${formatNumber(ci.price)}</div>
            </div>
          </div>
          <div class="text-right">
            <div class="font-bold">Rs. <span class="line-total">${formatNumber(ci.price * ci.qty)}</span></div>
            <div class="flex gap-1 mt-1 justify-end items-center">
              <button class="decrease-btn text-xs px-2 py-1 bg-gray-200 rounded" data-action="decrease">-</button>
              <div class="px-2 py-1 text-xs cart-qty" data-idx="${idx}">${ci.qty}</div>
              <button class="increase-btn text-xs px-2 py-1 bg-gray-200 rounded" data-action="increase">+</button>
              <button class="remove-btn text-xs px-2 py-1 bg-red-600 text-white rounded" data-action="remove">x</button>
            </div>
          </div>
        `;
        expandedCartItems.appendChild(row);
      });
    }
    updateTotalsAndUI();
  }

  /* totals & payments UI */
  function updateTotalsAndUI(){
    const totalQty = cart.reduce((s,c)=>s+c.qty,0);
    const subtotal = cart.reduce((s,c)=>s + (Number(c.price) * c.qty), 0);
    if (compactCartCount) compactCartCount.textContent = totalQty;
    if (compactCartSummary) compactCartSummary.textContent = cart.length ? `${cart.length} items` : 'No items';
    if (compactCartTotal) compactCartTotal.textContent = `Rs. ${formatNumber(subtotal)}`;
    if (expandedSubtotal) expandedSubtotal.textContent = `Rs. ${formatNumber(subtotal)}`;
    if (expandedTotal) expandedTotal.textContent = `Rs. ${formatNumber(subtotal)}`;
    showCompactBarIfNeeded();
    renderPayments();
  }

  /* cart controls (delegated) */
  expandedCartItems?.addEventListener('click', function(e){
    const btn = e.target.closest('button'); if (!btn) return; const action = btn.dataset.action;
    const row = btn.closest('div[data-idx]'); if (!row) return; const idx = Number(row.dataset.idx); if (isNaN(idx)) return;
    if (action === 'increase'){ if (cart[idx]) { cart[idx].qty++; const qtyEl = row.querySelector('.cart-qty'); if (qtyEl) qtyEl.textContent = cart[idx].qty; const lineTotalEl = row.querySelector('.line-total'); if (lineTotalEl) lineTotalEl.textContent = formatNumber(cart[idx].price * cart[idx].qty); updateTotalsAndUI(); } return; }
    if (action === 'decrease'){ if (cart[idx]) { cart[idx].qty--; if (cart[idx].qty <= 0) { cart.splice(idx, 1); renderCartUI(); } else { const qtyEl = row.querySelector('.cart-qty'); if (qtyEl) qtyEl.textContent = cart[idx].qty; const lineTotalEl = row.querySelector('.line-total'); if (lineTotalEl) lineTotalEl.textContent = formatNumber(cart[idx].price * cart[idx].qty); updateTotalsAndUI(); } } return; }
    if (action === 'remove'){ if (cart[idx]) { cart.splice(idx, 1); renderCartUI(); } return; }
  });

  /* payments */
  const BANKS = ['ABC Bank','Habib Bank','MCB','UBL','Allied Bank','Meezan Bank'];
  function renderPayments(){
    if (!paymentsList) return; paymentsList.innerHTML = '';
    if (!payments.length){ paymentsList.innerHTML = '<div class="text-xs text-gray-500">No payments added</div>'; updateRemainingAndCheckoutState(); return; }
    payments.forEach(p=>{
      const div = document.createElement('div'); div.className = 'flex items-center justify-between bg-gray-50 p-2 rounded text-sm';
      div.innerHTML = `<div>${escapeHtml(p.method)} — Rs. ${formatNumber(p.amount)}</div><div><button class="remove-payment-btn text-xs px-2 py-1 bg-red-600 text-white rounded" data-id="${p.id}">Remove</button></div>`;
      paymentsList.appendChild(div);
    });
    qsa('.remove-payment-btn').forEach(b=> b.addEventListener('click', e=>{ const id = Number(e.currentTarget.dataset.id); payments = payments.filter(x=>x.id !== id); renderPayments(); }));
    updateRemainingAndCheckoutState();
  }

  addPaymentRowBtn?.addEventListener('click', async ()=>{
    const method = paymentMethod?.value || 'cash';
    let amount = parseFloat(paymentAmount?.value || 0);
    if (!amount || amount <= 0) return Swal.fire({ icon:'error', title:'Invalid amount' });
    if (method === 'bank'){
      const { value: bank } = await Swal.fire({ title: 'Select bank', input: 'select', inputOptions: BANKS.reduce((o,b)=> (o[b]=b, o), {}), inputPlaceholder: 'Select a bank', showCancelButton: true });
      if (!bank) return; payments.push({ id: Date.now(), method: `Bank - ${bank}`, amount });
    } else {
      payments.push({ id: Date.now(), method: method.charAt(0).toUpperCase() + method.slice(1), amount });
    }
    paymentAmount.value = '';
    renderPayments(); ping(compactCartCount);
  });

  function updateRemainingAndCheckoutState(){
    const subtotal = cart.reduce((s,c)=>s + (Number(c.price)*c.qty),0);
    const paid = payments.reduce((s,p)=>s + p.amount,0);
    const remaining = Math.max(0, subtotal - paid);
    if (remainingAmountEl) remainingAmountEl.textContent = `Rs. ${formatNumber(remaining)}`;
    const allowCashImmediate = cart.length > 0 && payments.length === 0;
    const paidEnough = paid >= subtotal && subtotal > 0;
    const shouldEnable = (allowCashImmediate || paidEnough);
    if (expandedCheckoutBtn) expandedCheckoutBtn.disabled = !shouldEnable;
    if (checkoutBottomBtn) checkoutBottomBtn.disabled = !shouldEnable;
  }

  /* clear */
  function clearCart(){
    Swal.fire({ title: 'Clear cart?', text: 'This will remove all items from the cart.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes, clear' }).then(res=>{ if (res.isConfirmed){ cart = []; payments = []; renderCartUI(); renderPayments(); Swal.fire({ icon:'success', title:'Cart cleared', timer:900, showConfirmButton:false }); } }); 
  }
  clearBottomCartBtn?.addEventListener('click', clearCart);
  expandedClearBtn?.addEventListener('click', clearCart);

  /* build full printable receipt (only receipt - token removed) */
  function buildReceiptHtmlFromSale(sale){
    const rows = (sale.cart ?? sale.cart_items ?? sale.items ?? []).map(ci=>{
      const name = ci.name || ci.item_name || ci.product_name || '';
      const qty = ci.quantity ?? ci.qty ?? 1;
      const size = ci.size ?? ci.size_label ?? '';
      const rate = Number(ci.price ?? 0);
      const total = Number(ci.total ?? (rate * qty));
      return { name, qty, size, rate, total };
    });

    const subtotal = Number(sale.subtotal ?? sale.total ?? sale.total_amount ?? rows.reduce((s,r)=>s + r.total, 0));
    const deliveryCharge = Number(sale.delivery_charge ?? sale.delivery ?? 0) || 0;
    const totalAmount = subtotal + (isNaN(deliveryCharge)?0:deliveryCharge);

    const now = new Date();
    const dateTimeStr = now.toLocaleString();

    let html = '';
    html += '<div style="font-size:12px;margin:0 0 6px 0;padding-left:6px;padding-right:6px;">';
    // shop name with side gap (padding-left/right)
    html += '<div style="text-align:center; background-color:black; color:#fff;font-size:15px; font-weight:900; padding:6px 6px;">' + escapeHtml((window.POS_INFO && (window.POS_INFO.shop_name || '')) || '') + '</div>';
    html += '<div style="text-align:center;font-size:11px;color:#000; font-size:13px; margin:4px 0 0 0; font-weight:900;">' + escapeHtml((window.POS_INFO && window.POS_INFO.branch) || '') + '</div>';
    html += '<div style="text-align:center; font-size:13px; margin:2px 0 0 0;">' + escapeHtml((window.POS_INFO && (window.POS_INFO.phone || '')) || '') + '</div>';
    html += '</div>';

    html += '<div style="font-size:12px;margin:0 0 6px 0;padding-left:6px;padding-right:6px;">';
    html += '<div style="display:flex;justify-content:space-between;padding:2px 0;margin:0;">' + '<div>Type:</div>' + '<div>' + escapeHtml(String(sale.sale_type || sale.type || '')) + '</div>' + '</div>';
    html += '<div style="display:flex;justify-content:space-between;padding:2px 0;margin:0;">' + '<div>Date:</div>' + '<div>' + escapeHtml(dateTimeStr) + '</div>' + '</div>';
    if (sale.id) html += '<div style="display:flex;justify-content:space-between;padding:2px 0;margin:0;">' + '<div>Sale#</div>' + '<div>' + escapeHtml(String(sale.id)) + '</div>' + '</div>';
    html += '</div>';

    const isDelivery = String((sale.sale_type || sale.type || '')).toLowerCase().includes('delivery') || (sale.customer_name || sale.customer_phone || sale.customer_address);
    if (isDelivery){
      html += '<div style="font-size:12px;border-top:1px dashed #ddd;padding-top:6px;margin-bottom:6px;padding-left:6px;padding-right:6px;">';
      html += '<div style="font-weight:700;margin-bottom:4px;">Customer details</div>';
      if (sale.customer_name) html += '<div><strong>Name:</strong> ' + escapeHtml(sale.customer_name) + '</div>';
      if (sale.customer_phone) html += '<div><strong>Phone:</strong> ' + escapeHtml(sale.customer_phone) + '</div>';
      if (sale.customer_address) html += '<div style="word-break:break-word;"><strong>Address:</strong> ' + escapeHtml(sale.customer_address) + '</div>';
      if (deliveryCharge && deliveryCharge > 0) html += '<div><strong>Delivery charge:</strong> Rs. ' + formatNumber(deliveryCharge) + '</div>';
      html += '</div>';
    }

    html += '<div style="font-size:13px;border-top:1px dashed #ddd;padding-top:6px;margin:0;padding-left:6px;padding-right:6px;">';
    html += `
<div style=" background-color:black; color:#fff; display:flex;justify-content:space-between;padding:3px 0;font-weight:700;border-bottom:1px solid #ccc;margin-bottom:4px;">
    <div style="flex:1;text-align:left;">Item</div>
    <div style="width:40px;text-align:center;">Qty</div>
    <div style="width:50px;text-align:right;">Rate</div>
    <div style="width:60px;text-align:right;">Total</div>
</div>
`;
    rows.forEach(r=>{
      html += '<div style="display:flex;justify-content:space-between;padding:3px 0;margin:0;">';
        html += '<div style="flex:1;text-align:left;">' + escapeHtml(r.name) + (r.size ? (' (' + escapeHtml(r.size) + ')') : '') + '</div>';
        html += '<div style="width:40px;text-align:center;">' + r.qty + '</div>';
        html += '<div style="width:50px;text-align:right;">' + formatNumber(r.rate) + '</div>';
        html += '<div style="width:60px;text-align:right;">' + formatNumber(r.total) + '</div>';
      html += '</div>';
    });
    html += '</div>';

    html += '<div style="border-top:1px dashed #ddd;margin-top:6px;padding-top:6px;font-weight:700;font-size:13px;padding-left:6px;padding-right:6px;">';
    html += '<div style="display:flex;justify-content:space-between;"><div>Subtotal</div><div>Rs. ' + formatNumber(subtotal) + '</div></div>';
    if (deliveryCharge && deliveryCharge > 0){
      html += '<div style="display:flex;justify-content:space-between;"><div>Delivery</div><div>Rs. ' + formatNumber(deliveryCharge) + '</div></div>';
    }
    html += '<div style="display:flex;justify-content:space-between;margin-top:6px;"><div>Total</div><div>Rs. ' + formatNumber(totalAmount) + '</div></div>';
    html += '</div>';

    if (sale.payments && sale.payments.length){
      html += '<div style="margin-top:6px;font-size:12px;padding-left:6px;padding-right:6px;">Payments:</div>';
      sale.payments.forEach(p=>{
        html += '<div style="display:flex;justify-content:space-between;font-size:12px;padding-left:6px;padding-right:6px;">';
        html += '<div>' + escapeHtml(p.method || p.payment_method || '') + '</div>';
        html += '<div>Rs. ' + formatNumber(Number(p.amount || 0)) + '</div>';
        html += '</div>';
      });
    }

    html += '<div style="margin-top:8px;font-size:12px;text-align:center;border-top:1px dashed #eee;padding-top:8px;padding-left:6px;padding-right:6px;">';
    html += '<div style="margin-top:6px;">Thank you</div>';
    html += '<div style="font-size:11px;margin-top:5px;">Powered by: Prowave Software Solution</div>';
    html += '<div style="font-size:11px;">+92 317 3836 223 • +92 317 3859 647</div>';
    html += '</div>';

    return html;
  }

  /* build printable HTML wrapper (only receipt) */
  function buildPrintableHtmlForAdhoc(sale){
    const receiptHtml = buildReceiptHtmlFromSale(sale);

    return `
      <div id="adHocPrintContainer" style="background:#fff;color:#000;padding:0;margin:0;font-family:monospace;font-size:13px;text-align:left;display:block;width:80mm;">
        <div class="receipt-page" style="margin:0;padding:6px 6px 8px 6px;page-break-after:always;">
          ${receiptHtml}
        </div>
      </div>
    `;
  }

  function printSaleDirectly(sale){
    try {
      // cleanup previous wrapper
      const prev = document.getElementById('adHocPrintContainerWrapper');
      if (prev) prev.remove();

      const html = buildPrintableHtmlForAdhoc(sale);

      // create wrapper - PIN to top-left to avoid browser centering
      const wrapper = document.createElement('div');
wrapper.id = 'adHocPrintContainerWrapper';

wrapper.style.position = 'absolute';
wrapper.style.left = '0';
wrapper.style.top = '-8px';            // <-- stronger negative top to pull content up
wrapper.style.width = '80mm';
wrapper.style.maxWidth = '80mm';
wrapper.style.height = 'auto';
wrapper.style.zIndex = '2147483647';
wrapper.style.background = '#fff';
wrapper.style.margin = '0';
wrapper.style.padding = '0';
wrapper.style.overflow = 'visible';
wrapper.style.display = 'block';
wrapper.style.boxSizing = 'border-box';
// small transform nudge as well (keeps compatibility with some browsers)
wrapper.style.transform = 'translateY(-2px)';
wrapper.style.webkitTransform = 'translateY(-2px)';
wrapper.innerHTML = html;

      document.body.appendChild(wrapper);

      // mark body for print CSS targeting
      document.body.classList.add('for-printing');

      // Force viewport to top and ensure printing starts from wrapper top
      setTimeout(()=> {
        try {
          window.scrollTo(0,0);
          wrapper.scrollIntoView({ behavior: 'auto', block: 'start' });
          wrapper.setAttribute('tabindex', '-1');
          wrapper.focus && wrapper.focus();
        } catch(e){ /* ignore */ }

        // small extra reflow delay then print
        setTimeout(()=>{
          window.print();

          // cleanup after print
          setTimeout(()=> {
            try { wrapper.remove(); } catch(e){}
            document.body.classList.remove('for-printing');
          }, 600);
        }, 140);
      }, 80);
    } catch(err){
      console.error('Direct print failed', err);
      try { alert('Printing failed: ' + (err && err.message ? err.message : 'Unknown error')); } catch(e){}
    }
  }

  /* Exposed auto-print handler */
  window.onSaleSuccessAutoPrint = function(sale){
    lastSale = sale || {};
    printSaleDirectly(lastSale);
  };

  /* Checkout: send sale to server and trigger auto-print on success */
  async function doCheckout(){
    if (!cart.length) return Swal.fire({ icon:'info', title:'Cart empty' });
    const selectedSaleBtn = Array.from(saleTypeBtns).find(b=>b.classList.contains('bg-gray-800')) || saleTypeBtns[0];
    const sale_type = selectedSaleBtn ? selectedSaleBtn.dataset.type : 'take_away';

    if (sale_type === 'delivery'){
      const name = qs('#customerName')?.value?.trim();
      const phone = qs('#customerPhone')?.value?.trim();
      const addr = qs('#customerAddress')?.value?.trim();
      if (!name || !phone || !addr) return Swal.fire({ icon:'error', title:'Delivery details required', text:'Please fill customer name, phone and address.' });
    }

    const itemsPayload = cart.map(ci => ({ item_id: ci.item_id, size_id: ci.size_id, quantity: ci.qty, price: ci.price }));
    const totalAmount = itemsPayload.reduce((s,i)=> s + (i.price * i.quantity), 0);
    const paymentsPayload = payments.map(p => ({ method: p.method, amount: p.amount }));

    try {
      if (expandedCheckoutBtn) expandedCheckoutBtn.disabled = true;
      if (checkoutBottomBtn) checkoutBottomBtn.disabled = true;

      const payload = {
        items: itemsPayload,
        sale_type,
        customer_name: qs('#customerName')?.value || null,
        customer_phone: qs('#customerPhone')?.value || null,
        customer_address: qs('#customerAddress')?.value || null,
        delivery_charge: parseFloat(qs('#deliveryCharge')?.value || 0) || 0,
        payments: paymentsPayload,
        total_amount: totalAmount
      };

      const res = await fetch('/api/sales', {
        method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.CSRF_TOKEN, 'Accept': 'application/json' }, body: JSON.stringify(payload)
      });

      const text = await res.text();
      let parsed = null;
      try { parsed = JSON.parse(text); } catch(e){ parsed = null; }

      if (!res.ok) {
        const serverMsg = parsed && (parsed.message || parsed.error || parsed.errors) ? (parsed.message || JSON.stringify(parsed)) : null;
        const excerpt = text && text.length ? text.slice(0, 400).replace(/\s+/g,' ') : '';
        const friendly = serverMsg ? serverMsg : `Server returned ${res.status}. Response (truncated): ${excerpt}`;
        throw new Error(friendly);
      }

      const sale = parsed || null;
      if (!sale) throw new Error('Server did not return valid JSON. Check /api/sales response (see console).');

      if (typeof Swal !== 'undefined') {
        Swal.fire({ icon:'success', title:'Sale saved', text: `Sale #${sale.id || ''}`, timer:900, showConfirmButton:false });
      }
      lastSale = sale;

      // Trigger auto-print (receipt only)
      window.onSaleSuccessAutoPrint && window.onSaleSuccessAutoPrint(sale);

      // reset cart & payments
      cart = []; payments = [];
      renderCartUI(); renderPayments();

    } catch (err) {
      console.error(err);
      Swal.fire({ icon:'error', title:'Checkout failed', text: err.message || 'Server error' });
      if (expandedCheckoutBtn) expandedCheckoutBtn.disabled = false;
      if (checkoutBottomBtn) checkoutBottomBtn.disabled = false;
    }
  }

  /* Bind new print buttons (both expanded and bottom) to doCheckout */
  expandedCheckoutBtn?.addEventListener('click', doCheckout);
  checkoutBottomBtn?.addEventListener('click', doCheckout);

  /* open/close cart */
  openCartBtn?.addEventListener('click', ()=> { if (expandedPanel) { expandedPanel.style.transform = 'translateY(0)'; expandedPanel.style.pointerEvents = 'auto'; } });
  closeExpandedCart?.addEventListener('click', ()=> { if (expandedPanel) { expandedPanel.style.transform = 'translateY(100%)'; expandedPanel.style.pointerEvents = 'none'; } });

  /* init */
  filterAndShowItems(); renderCartUI(); renderPayments();
}); // DOMContentLoaded
</script>
@endpush
