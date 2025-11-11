@extends('layouts.app')

@section('content')
<style>
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: scale(0.95) translateY(-10px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideOut {
        from {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
        to {
            opacity: 0;
            transform: scale(0.95) translateY(-10px);
        }
    }

    .modal-backdrop {
        animation: fadeIn 0.2s ease-out;
    }

    .modal-backdrop.closing {
        animation: fadeOut 0.2s ease-out;
    }

    @keyframes fadeOut {
        from { opacity: 1; }
        to { opacity: 0; }
    }

    .modal-content {
        animation: slideIn 0.3s ease-out;
    }

    .modal-content.closing {
        animation: slideOut 0.3s ease-out;
    }

    .item-card {
        animation: slideIn 0.4s ease-out;
    }

    .item-card.removing {
        animation: slideOut 0.3s ease-out forwards;
    }

    .toast {
        animation: slideIn 0.3s ease-out;
    }

    .toast.hide {
        animation: slideOut 0.3s ease-out forwards;
    }

    .size-item {
        animation: slideIn 0.3s ease-out;
    }

    .size-item.removing {
        animation: slideOut 0.2s ease-out forwards;
    }
</style>

<div class="max-w-6xl mx-auto space-y-6 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">Items</h2>
        <button id="openAddModal" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition-all cursor-pointer shadow-sm hover:shadow-md">
            + Add Item
        </button>
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="hidden text-center py-12">
        <div class="text-gray-400 text-6xl mb-4">🍽️</div>
        <p class="text-gray-500 text-lg">No items yet</p>
        <p class="text-gray-400 text-sm">Create your first item to get started</p>
    </div>

    <!-- Items Grid - Compact Cards -->
    <div id="itemsList" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-2">
        @foreach($items as $item)
        <div class="item-card bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden hover:shadow-md transition-all cursor-default">
            <!-- Image -->
            <div class="relative h-20 bg-gray-100 overflow-hidden">
                @if($item->image)
                    <img src="{{ asset('storage/' . $item->image) }}" class="w-full h-full object-cover" alt="{{ $item->name }}">
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-300 text-xs">🖼️</div>
                @endif
            </div>

            <!-- Content -->
            <div class="p-2">
                <h3 class="text-xs font-semibold text-gray-800 line-clamp-1 item-name" title="{{ $item->name }}">{{ $item->name }}</h3>
                <p class="text-xs text-gray-500 line-clamp-1 item-subcategory" title="{{ $item->subcategory->name ?? 'N/A' }}">{{ $item->subcategory->name ?? 'N/A' }}</p>

                <!-- Sizes - Compact -->
                <div class="mt-1 space-y-0.5 item-sizes text-xs">
                    @foreach($item->sizes as $size)
                    <div class="size-item flex justify-between text-gray-700 bg-gray-50 p-1 rounded">
                        <span class="size-name font-medium truncate">{{ $size->size }}</span>
                        <span class="size-price text-gray-900 font-semibold whitespace-nowrap">Rs. {{ number_format($size->price, 0) }}</span>
                    </div>
                    @endforeach
                </div>

                <!-- Actions - Compact -->
                <div class="mt-2 flex gap-1">
                    <button 
                        class="flex-1 text-blue-600 hover:text-blue-800 edit-btn transition-all cursor-pointer text-xs py-1 hover:bg-blue-50 rounded" 
                        data-id="{{ $item->id }}"
                        title="Edit">
                        ✏️
                    </button>
                    <button 
                        class="flex-1 text-red-600 hover:text-red-800 delete-btn transition-all cursor-pointer text-xs py-1 hover:bg-red-50 rounded" 
                        data-id="{{ $item->id }}"
                        title="Delete">
                        🗑️
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div id="paginationContainer" class="mt-6 flex justify-center items-center gap-2">
        <button id="prevPageBtn" class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300 text-xs transition-all disabled:opacity-50">← Prev</button>
        <span id="pageInfo" class="text-xs text-gray-600">Page 1</span>
        <button id="nextPageBtn" class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300 text-xs transition-all disabled:opacity-50">Next →</button>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="modal" class="fixed inset-0 hidden z-50">
    <div id="modalBackdrop" class="absolute inset-0 bg-gray-500 bg-opacity-30 transition-opacity duration-200 cursor-pointer"></div>
    
    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
        <div id="modalContent" class="modal-content bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-screen overflow-y-auto p-6 pointer-events-auto">
            <button id="closeBtn" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors cursor-pointer z-10">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <h3 id="modalTitle" class="text-xl font-semibold mb-4 text-gray-800">Add Item</h3>

            <form id="itemForm" class="space-y-4">
                <!-- Item Name -->
                <div>
                    <label for="nameInput" class="block text-sm font-medium text-gray-700 mb-2">Item Name</label>
                    <input 
                        type="text" 
                        id="nameInput" 
                        placeholder="Enter item name"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-all"
                        required>
                    <div id="nameError" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>

                <!-- Subcategory -->
                <div>
                    <label for="subcategoryInput" class="block text-sm font-medium text-gray-700 mb-2">Subcategory</label>
                    <select 
                        id="subcategoryInput" 
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-all"
                        required>
                        <option value="">Select a subcategory</option>
                    </select>
                    <div id="subcategoryError" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>

                <!-- Image Upload -->
                <div>
                    <label for="imageInput" class="block text-sm font-medium text-gray-700 mb-2">Image</label>
                    <input 
                        type="file" 
                        id="imageInput" 
                        accept="image/*"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-all">
                    <div id="imagePreview" class="mt-2 hidden">
                        <img id="previewImg" src="" alt="Preview" class="w-32 h-32 object-cover rounded-lg">
                    </div>
                    <div id="imageError" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>

                <!-- Sizes Section -->
                <div class="border-t pt-4">
                    <div class="flex justify-between items-center mb-4">
                        <label class="block text-sm font-medium text-gray-700">Sizes & Prices</label>
                        <button 
                            type="button"
                            id="addSizeBtn" 
                            class="text-blue-600 hover:text-blue-800 text-sm transition-all cursor-pointer hover:font-semibold">
                            + Add Size
                        </button>
                    </div>

                    <div id="sizesContainer" class="space-y-3">
                        <div class="size-row flex gap-2 items-end">
                            <div class="flex-1">
                                <input 
                                    type="text" 
                                    placeholder="Size (e.g., Single, Double)"
                                    class="size-input w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-all"
                                    required>
                            </div>
                            <div class="flex-1">
                                <input 
                                    type="number" 
                                    placeholder="Price"
                                    step="0.01"
                                    min="0"
                                    class="price-input w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-all"
                                    required>
                            </div>
                            <button 
                                type="button"
                                class="remove-size-btn text-red-600 hover:text-red-800 transition-all cursor-pointer p-2 hover:bg-red-50 rounded-lg"
                                title="Remove size">
                                ✕
                            </button>
                        </div>
                    </div>
                    <div id="sizesError" class="text-red-500 text-sm mt-2 hidden"></div>
                </div>

                <!-- Buttons -->
                <div class="mt-6 flex justify-end gap-2 border-t pt-4">
                    <button 
                        type="button"
                        id="closeModal" 
                        class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-800 transition-all cursor-pointer">
                        Cancel
                    </button>
                    <button 
                        type="button"
                        id="saveBtn" 
                        class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-all cursor-pointer flex items-center gap-2">
                        <span id="saveBtnText">Save</span>
                        <span id="saveBtnSpinner" class="hidden">
                            <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="fixed bottom-4 right-4 hidden px-4 py-3 rounded-lg text-white text-sm shadow-lg z-50"></div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modal');
    const modalBackdrop = document.getElementById('modalBackdrop');
    const modalContent = document.getElementById('modalContent');
    const modalTitle = document.getElementById('modalTitle');
    const nameInput = document.getElementById('nameInput');
    const subcategoryInput = document.getElementById('subcategoryInput');
    const imageInput = document.getElementById('imageInput');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    const itemForm = document.getElementById('itemForm');
    const saveBtn = document.getElementById('saveBtn');
    const closeBtn = document.getElementById('closeBtn');
    const closeModal = document.getElementById('closeModal');
    const addSizeBtn = document.getElementById('addSizeBtn');
    const sizesContainer = document.getElementById('sizesContainer');
    const nameError = document.getElementById('nameError');
    const subcategoryError = document.getElementById('subcategoryError');
    const sizesError = document.getElementById('sizesError');
    const saveBtnText = document.getElementById('saveBtnText');
    const saveBtnSpinner = document.getElementById('saveBtnSpinner');
    // itemsList is already declared above in the DOM; do not re-declare here.
    const emptyState = document.getElementById('emptyState');

    let editMode = false;
    let editId = null;
    let isSubmitting = false;

    // Load subcategories
    const loadSubcategories = async () => {
        try {
            const response = await fetch('/api/subcategories');
            const subcategories = await response.json();
            subcategoryInput.innerHTML = '<option value="">Select a subcategory</option>';
            subcategories.forEach(subcategory => {
                const option = document.createElement('option');
                option.value = subcategory.id;
                option.textContent = `${subcategory.name} (${subcategory.category.name})`;
                subcategoryInput.appendChild(option);
            });
        } catch (error) {
            console.error('Error loading subcategories:', error);
        }
    };

    loadSubcategories();

    // Show toast
    const showToast = (message, type = 'success') => {
        const toast = document.getElementById('toast');
        toast.textContent = message;
        toast.className = `fixed bottom-4 right-4 px-4 py-3 rounded-lg text-white text-sm shadow-lg z-50 toast`;
        toast.style.backgroundColor = type === 'success' ? '#10b981' : '#ef4444';
        
        setTimeout(() => {
            toast.classList.add('hide');
            setTimeout(() => {
                toast.classList.add('hidden');
                toast.classList.remove('hide');
            }, 300);
        }, 3000);
    };

    // Check empty state
    const checkEmptyState = () => {
        const items = itemsList.querySelectorAll('.item-card');
        if (items.length === 0) {
            itemsList.classList.add('hidden');
            emptyState.classList.remove('hidden');
        } else {
            itemsList.classList.remove('hidden');
            emptyState.classList.add('hidden');
        }
    };

    checkEmptyState();

    // Add size row
    const addSizeRow = () => {
        const row = document.createElement('div');
        row.className = 'size-row flex gap-2 items-end';
        row.innerHTML = `
            <div class="flex-1">
                <input 
                    type="text" 
                    placeholder="Size (e.g., Single, Double)"
                    class="size-input w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-all"
                    required>
            </div>
            <div class="flex-1">
                <input 
                    type="number" 
                    placeholder="Price"
                    step="0.01"
                    min="0"
                    class="price-input w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-all"
                    required>
            </div>
            <button 
                type="button"
                class="remove-size-btn text-red-600 hover:text-red-800 transition-all cursor-pointer p-2 hover:bg-red-50 rounded-lg"
                title="Remove size">
                ✕
            </button>
        `;
        
        const removeBtn = row.querySelector('.remove-size-btn');
        removeBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const rows = sizesContainer.querySelectorAll('.size-row');
            if (rows.length > 1) {
                row.classList.add('removing');
                setTimeout(() => row.remove(), 200);
            } else {
                alert('You must have at least one size!');
            }
        });

        sizesContainer.appendChild(row);
    };

    addSizeBtn.addEventListener('click', (e) => {
        e.preventDefault();
        addSizeRow();
    });

    // Image preview and client-side validation
    const imageErrorEl = document.getElementById('imageError');
    imageInput.addEventListener('change', (e) => {
        const file = e.target.files && e.target.files[0];
        if (!file) {
            imagePreview.classList.add('hidden');
            imageErrorEl.classList.add('hidden');
            return;
        }

        // Basic client-side validation to avoid server 422 for predictable issues
        if (!file.type || !file.type.startsWith('image/')) {
            imageErrorEl.textContent = 'The file must be an image.';
            imageErrorEl.classList.remove('hidden');
            imagePreview.classList.add('hidden');
            imageInput.value = '';
            return;
        }

        const maxBytes = 2048 * 1024; // 2MB as per server validation
        if (file.size > maxBytes) {
            imageErrorEl.textContent = 'Image must be 2MB or smaller.';
            imageErrorEl.classList.remove('hidden');
            imagePreview.classList.add('hidden');
            imageInput.value = '';
            return;
        }

        imageErrorEl.classList.add('hidden');
        const reader = new FileReader();
        reader.onload = (event) => {
            previewImg.src = event.target.result;
            imagePreview.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    });

    // Open modal
    const openModal = (title = 'Add Item', isEdit = false) => {
        editMode = isEdit;
        modalTitle.textContent = title;
        if (!isEdit) {
            nameInput.value = '';
            subcategoryInput.value = '';
            imageInput.value = '';
            imagePreview.classList.add('hidden');
            sizesContainer.innerHTML = '';
            addSizeRow();
        }
        nameError.classList.add('hidden');
        subcategoryError.classList.add('hidden');
        sizesError.classList.add('hidden');
        modal.classList.remove('hidden');
        nameInput.focus();
    };

    // Close modal with animation
    const closeModalWithAnimation = () => {
        modalContent.classList.add('closing');
        modalBackdrop.classList.add('closing');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            modalContent.classList.remove('closing');
            modalBackdrop.classList.remove('closing');
        }, 300);
    };

    document.getElementById('openAddModal').addEventListener('click', () => {
        editMode = false;
        editId = null;
        openModal('Add Item', false);
    });

    closeBtn.addEventListener('click', closeModalWithAnimation);
    closeModal.addEventListener('click', closeModalWithAnimation);
    modalBackdrop.addEventListener('click', closeModalWithAnimation);

    // Validate input
    const validateInput = () => {
        const name = nameInput.value.trim();
        const subcategory = subcategoryInput.value;
        const sizeInputs = sizesContainer.querySelectorAll('.size-input');
        const priceInputs = sizesContainer.querySelectorAll('.price-input');

        let isValid = true;

        if (!name) {
            nameError.textContent = 'Please enter an item name.';
            nameError.classList.remove('hidden');
            nameInput.classList.add('border-red-500');
            isValid = false;
        } else {
            nameError.classList.add('hidden');
            nameInput.classList.remove('border-red-500');
        }

        if (!subcategory) {
            subcategoryError.textContent = 'Please select a subcategory.';
            subcategoryError.classList.remove('hidden');
            subcategoryInput.classList.add('border-red-500');
            isValid = false;
        } else {
            subcategoryError.classList.add('hidden');
            subcategoryInput.classList.remove('border-red-500');
        }

        let hasSizes = false;
        sizeInputs.forEach((input, index) => {
            if (input.value.trim() && priceInputs[index].value) {
                hasSizes = true;
            }
        });

        if (!hasSizes) {
            sizesError.textContent = 'Please add at least one size with price.';
            sizesError.classList.remove('hidden');
            isValid = false;
        } else {
            sizesError.classList.add('hidden');
        }

        return isValid;
    };

    nameInput.addEventListener('input', () => {
        if (!nameError.classList.contains('hidden')) validateInput();
    });

    subcategoryInput.addEventListener('change', () => {
        if (!subcategoryError.classList.contains('hidden')) validateInput();
    });

    // Save
    saveBtn.addEventListener('click', async () => {
        if (isSubmitting) return;
        if (!validateInput()) return;

        isSubmitting = true;
        saveBtnText.classList.add('hidden');
        saveBtnSpinner.classList.remove('hidden');
        saveBtn.disabled = true;

        const formData = new FormData();
        formData.append('name', nameInput.value.trim());
        formData.append('subcategory_id', subcategoryInput.value);

        if (imageInput.files && imageInput.files[0]) {
            formData.append('image', imageInput.files[0]);
        }

        // Build sizes as form fields so Laravel will interpret them as an array
        const sizes = [];
        const sizeInputs = sizesContainer.querySelectorAll('.size-input');
        const priceInputs = sizesContainer.querySelectorAll('.price-input');
        sizeInputs.forEach((input, index) => {
            const sizeVal = input.value.trim();
            const priceVal = priceInputs[index].value;
            if (sizeVal && priceVal !== undefined && priceVal !== '') {
                sizes.push({ size: sizeVal, price: parseFloat(priceVal) });
                formData.append(`sizes[${index}][size]`, sizeVal);
                formData.append(`sizes[${index}][price]`, priceVal);
            }
        });

        const url = editMode ? `/items/${editId}` : '/items';
        const method = 'POST'; // FormData with file always uses POST; Laravel will check _method for PUT

        // For PUT requests with FormData (which includes file), we need to add _method override
        if (editMode) {
            formData.append('_method', 'PUT');
        }

        try {
            const response = await fetch(url, {
                method,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData,
            });

            // If the response is not OK, try to parse JSON validation errors and show them next to fields.
            if (!response.ok) {
                let errMsg = 'Something went wrong!';
                try {
                    const data = await response.json();

                    // If Laravel validation returned errors, display them inline
                    if (data.errors) {
                        // Name
                        if (data.errors.name) {
                            nameError.textContent = data.errors.name.join(' ');
                            nameError.classList.remove('hidden');
                            nameInput.classList.add('border-red-500');
                        } else {
                            nameError.classList.add('hidden');
                            nameInput.classList.remove('border-red-500');
                        }

                        // Subcategory
                        if (data.errors.subcategory_id) {
                            subcategoryError.textContent = data.errors.subcategory_id.join(' ');
                            subcategoryError.classList.remove('hidden');
                            subcategoryInput.classList.add('border-red-500');
                        } else {
                            subcategoryError.classList.add('hidden');
                            subcategoryInput.classList.remove('border-red-500');
                        }

                        // Sizes
                        if (data.errors.sizes) {
                            sizesError.textContent = data.errors.sizes.join(' ');
                            sizesError.classList.remove('hidden');
                        } else {
                            sizesError.classList.add('hidden');
                        }

                        // Image
                        const imageErrorEl = document.getElementById('imageError');
                        if (data.errors.image) {
                            imageErrorEl.textContent = data.errors.image.join(' ');
                            imageErrorEl.classList.remove('hidden');
                        } else {
                            imageErrorEl.classList.add('hidden');
                        }

                        // Combine messages for toast
                        errMsg = Object.values(data.errors).flat().join(' ');
                    } else if (data.message) {
                        errMsg = data.message;
                    } else {
                        errMsg = JSON.stringify(data);
                    }
                } catch (parseErr) {
                    const raw = await response.text();
                    console.error('Raw non-JSON response:', raw);
                    errMsg = `Server returned non-JSON response (status ${response.status}). See console for details.`;
                }

                throw new Error(errMsg);
            }

            // Parse JSON, but guard against invalid JSON
            let data;
            try {
                data = await response.json();
            } catch (parseErr) {
                const raw = await response.text();
                console.error('Failed to parse JSON response, raw body:', raw);
                throw new Error('Invalid JSON response from server. Check server logs (laravel.log).');
            }

            closeModalWithAnimation();
            showToast(editMode ? 'Item updated successfully!' : 'Item added successfully!', 'success');

            setTimeout(() => {
                location.reload();
            }, 1000);
        } catch (error) {
            showToast(error.message, 'error');
            console.error('Error:', error);
        } finally {
            isSubmitting = false;
            saveBtnText.classList.remove('hidden');
            saveBtnSpinner.classList.add('hidden');
            saveBtn.disabled = false;
        }
    });

    // Escape HTML
    const escapeHtml = (text) => {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    };

    // Attach event listeners
    const attachEventListeners = (container) => {
        const editBtn = container.querySelector('.edit-btn');
        if (editBtn) {
            editBtn.addEventListener('click', async (e) => {
                const id = e.target.closest('.edit-btn').dataset.id;
                editMode = true;
                editId = id;

                try {
                    const response = await fetch(`/items/${id}`);
                    const item = await response.json();
                    
                    nameInput.value = item.name;
                    subcategoryInput.value = item.subcategory_id;
                    
                    sizesContainer.innerHTML = '';
                    item.sizes.forEach(size => {
                        const row = document.createElement('div');
                        row.className = 'size-row flex gap-2 items-end';
                        row.innerHTML = `
                            <div class="flex-1">
                                <input 
                                    type="text" 
                                    placeholder="Size"
                                    class="size-input w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-all"
                                    value="${escapeHtml(size.size)}"
                                    required>
                            </div>
                            <div class="flex-1">
                                <input 
                                    type="number" 
                                    placeholder="Price"
                                    step="0.01"
                                    min="0"
                                    class="price-input w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-all"
                                    value="${size.price}"
                                    required>
                            </div>
                            <button 
                                type="button"
                                class="remove-size-btn text-red-600 hover:text-red-800 transition-all cursor-pointer p-2 hover:bg-red-50 rounded-lg"
                                title="Remove size">
                                ✕
                            </button>
                        `;
                        
                        const removeBtn = row.querySelector('.remove-size-btn');
                        removeBtn.addEventListener('click', (e) => {
                            e.preventDefault();
                            const rows = sizesContainer.querySelectorAll('.size-row');
                            if (rows.length > 1) {
                                row.classList.add('removing');
                                setTimeout(() => row.remove(), 200);
                            }
                        });

                        sizesContainer.appendChild(row);
                    });

                    openModal('Edit Item', true);
                } catch (error) {
                    showToast('Failed to load item', 'error');
                }
            });
        }

        const deleteBtn = container.querySelector('.delete-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', async (e) => {
                const id = e.target.closest('.delete-btn').dataset.id;
                const card = e.target.closest('.item-card');
                const itemName = card.querySelector('.item-name').textContent;

                if (!confirm(`Are you sure you want to delete "${itemName}"?`)) return;

                try {
                    const response = await fetch(`/items/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                    });

                    if (!response.ok) {
                        throw new Error('Failed to delete item.');
                    }

                    card.classList.add('removing');
                    setTimeout(() => {
                        card.remove();
                        checkEmptyState();
                        showToast('Item deleted successfully!', 'success');
                    }, 300);
                } catch (error) {
                    showToast(error.message, 'error');
                }
            });
        }
    };

    // Pagination logic
    const ITEMS_PER_PAGE = 12; // 2 rows × 6 columns
    let currentPage = 1;
    let allItems = [];
    const prevPageBtn = document.getElementById('prevPageBtn');
    const nextPageBtn = document.getElementById('nextPageBtn');
    const pageInfo = document.getElementById('pageInfo');
    const paginationContainer = document.getElementById('paginationContainer');

    function initializePagination() {
        // Get all item cards (original DOM elements)
        allItems = Array.from(document.querySelectorAll('.item-card'));
        
        if (allItems.length === 0) {
            paginationContainer.classList.add('hidden');
            return;
        }

        // Show pagination only if more than one page
        if (allItems.length > ITEMS_PER_PAGE) {
            paginationContainer.classList.remove('hidden');
        } else {
            paginationContainer.classList.add('hidden');
        }

        currentPage = 1;
        showPage(1);
        updatePaginationButtons();
    }

    function showPage(pageNum) {
        const totalPages = Math.ceil(allItems.length / ITEMS_PER_PAGE);
        
        // Validate page number
        if (pageNum < 1) pageNum = 1;
        if (pageNum > totalPages) pageNum = totalPages;

        currentPage = pageNum;

        // Calculate start and end indices
        const startIdx = (pageNum - 1) * ITEMS_PER_PAGE;
        const endIdx = startIdx + ITEMS_PER_PAGE;

        // Hide all items
        allItems.forEach(item => item.classList.add('hidden'));

        // Show current page items
        allItems.slice(startIdx, endIdx).forEach(item => {
            item.classList.remove('hidden');
            // Re-attach event listeners for visible items
            attachEventListeners(item);
        });

        // Update page info
        pageInfo.textContent = `Page ${pageNum} of ${totalPages}`;

        updatePaginationButtons();
    }

    function updatePaginationButtons() {
        const totalPages = Math.ceil(allItems.length / ITEMS_PER_PAGE);
        
        // Disable prev button on first page
        prevPageBtn.disabled = currentPage === 1;
        
        // Disable next button on last page
        nextPageBtn.disabled = currentPage === totalPages || totalPages === 0;
    }

    // Event listeners for pagination buttons
    prevPageBtn.addEventListener('click', () => {
        if (currentPage > 1) {
            showPage(currentPage - 1);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });

    nextPageBtn.addEventListener('click', () => {
        const totalPages = Math.ceil(allItems.length / ITEMS_PER_PAGE);
        if (currentPage < totalPages) {
            showPage(currentPage + 1);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });

    document.querySelectorAll('.item-card').forEach(card => {
        attachEventListeners(card);
    });

    // Initialize pagination when page loads
    initializePagination();

    // Re-initialize pagination after item is deleted or added
    const originalShowToast = showToast;
    window.showToast = function(message, type) {
        originalShowToast(message, type);
        // Re-initialize pagination on item changes
        setTimeout(() => {
            initializePagination();
        }, 300);
    };

    itemForm.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && !isSubmitting) {
            e.preventDefault();
            saveBtn.click();
        }
    });
});
</script>
@endsection