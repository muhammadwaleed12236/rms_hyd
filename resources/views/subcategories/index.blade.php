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

    .item {
        animation: slideIn 0.4s ease-out;
    }

    .item.removing {
        animation: slideOut 0.3s ease-out forwards;
    }

    .toast {
        animation: slideIn 0.3s ease-out;
    }

    .toast.hide {
        animation: slideOut 0.3s ease-out forwards;
    }
</style>

<div class="max-w-3xl mx-auto space-y-6 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">Subcategories</h2>
        <button id="openAddModal" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition-all cursor-pointer shadow-sm hover:shadow-md">
            + Add Subcategory
        </button>
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="hidden text-center py-12">
        <div class="text-gray-400 text-6xl mb-4">📁</div>
        <p class="text-gray-500 text-lg">No subcategories yet</p>
        <p class="text-gray-400 text-sm">Create your first subcategory to get started</p>
    </div>

    <!-- Subcategories List -->
    <div id="itemList" class="space-y-3">
        @foreach($subcategories as $subcategory)
        <div class="item bg-white shadow-sm border border-gray-200 p-4 rounded-lg flex justify-between items-center hover:shadow-md transition-all cursor-default">
            <div>
                <div class="font-medium text-gray-800">{{ $subcategory->name }}</div>
                <div class="text-sm text-gray-500">{{ $subcategory->category->name }}</div>
            </div>
            <div class="space-x-2">
                <button 
                    class="text-blue-600 hover:text-blue-800 edit-btn transition-all cursor-pointer hover:font-semibold" 
                    data-id="{{ $subcategory->id }}" 
                    data-name="{{ $subcategory->name }}"
                    data-category="{{ $subcategory->category_id }}"
                    title="Edit subcategory">
                    Edit
                </button>
                <button 
                    class="text-red-600 hover:text-red-800 delete-btn transition-all cursor-pointer hover:font-semibold" 
                    data-id="{{ $subcategory->id }}"
                    title="Delete subcategory">
                    Delete
                </button>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="modal" class="fixed inset-0 hidden z-50">
    <div id="modalBackdrop" class="absolute inset-0 bg-gray-500 bg-opacity-30 transition-opacity duration-200 cursor-pointer"></div>
    
    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
        <div id="modalContent" class="modal-content bg-white rounded-xl shadow-2xl w-full max-w-md p-6 pointer-events-auto">
            <button id="closeBtn" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors cursor-pointer">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <h3 id="modalTitle" class="text-xl font-semibold mb-4 text-gray-800">Add Subcategory</h3>

            <form id="itemForm" class="space-y-4">
                <div>
                    <label for="nameInput" class="block text-sm font-medium text-gray-700 mb-2">Subcategory Name</label>
                    <input 
                        type="text" 
                        id="nameInput" 
                        placeholder="Enter subcategory name"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-all"
                        required>
                    <div id="nameError" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>

                <div>
                    <label for="categoryInput" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                    <select 
                        id="categoryInput" 
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-all"
                        required>
                        <option value="">Select a category</option>
                    </select>
                    <div id="categoryError" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>

                <div class="mt-6 flex justify-end space-x-2">
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
    const categoryInput = document.getElementById('categoryInput');
    const itemForm = document.getElementById('itemForm');
    const saveBtn = document.getElementById('saveBtn');
    const closeBtn = document.getElementById('closeBtn');
    const closeModal = document.getElementById('closeModal');
    const nameError = document.getElementById('nameError');
    const categoryError = document.getElementById('categoryError');
    const saveBtnText = document.getElementById('saveBtnText');
    const saveBtnSpinner = document.getElementById('saveBtnSpinner');
    const itemList = document.getElementById('itemList');
    const emptyState = document.getElementById('emptyState');

    let editMode = false;
    let editId = null;
    let isSubmitting = false;

    // Load categories
    const loadCategories = async () => {
        try {
            const response = await fetch('/api/categories');
            const categories = await response.json();
            categoryInput.innerHTML = '<option value="">Select a category</option>';
            categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name;
                categoryInput.appendChild(option);
            });
        } catch (error) {
            console.error('Error loading categories:', error);
        }
    };

    loadCategories();

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
        const items = itemList.querySelectorAll('.item');
        if (items.length === 0) {
            itemList.classList.add('hidden');
            emptyState.classList.remove('hidden');
        } else {
            itemList.classList.remove('hidden');
            emptyState.classList.add('hidden');
        }
    };

    checkEmptyState();

    // Open modal
    const openModal = (title = 'Add Subcategory', isEdit = false) => {
        editMode = isEdit;
        modalTitle.textContent = title;
        if (!isEdit) {
            nameInput.value = '';
            categoryInput.value = '';
        }
        nameError.classList.add('hidden');
        categoryError.classList.add('hidden');
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

    // Event listeners
    document.getElementById('openAddModal').addEventListener('click', () => {
        editMode = false;
        editId = null;
        openModal('Add Subcategory', false);
    });

    closeBtn.addEventListener('click', closeModalWithAnimation);
    closeModal.addEventListener('click', closeModalWithAnimation);
    modalBackdrop.addEventListener('click', closeModalWithAnimation);

    // Validate input
    const validateInput = () => {
        const name = nameInput.value.trim();
        const category = categoryInput.value;
        let isValid = true;

        if (!name) {
            nameError.textContent = 'Please enter a subcategory name.';
            nameError.classList.remove('hidden');
            nameInput.classList.add('border-red-500');
            isValid = false;
        } else {
            nameError.classList.add('hidden');
            nameInput.classList.remove('border-red-500');
        }

        if (!category) {
            categoryError.textContent = 'Please select a category.';
            categoryError.classList.remove('hidden');
            categoryInput.classList.add('border-red-500');
            isValid = false;
        } else {
            categoryError.classList.add('hidden');
            categoryInput.classList.remove('border-red-500');
        }

        return isValid;
    };

    nameInput.addEventListener('input', () => {
        if (!nameError.classList.contains('hidden')) {
            validateInput();
        }
    });

    categoryInput.addEventListener('change', () => {
        if (!categoryError.classList.contains('hidden')) {
            validateInput();
        }
    });

    // Save
    saveBtn.addEventListener('click', async () => {
        if (isSubmitting) return;
        if (!validateInput()) return;

        isSubmitting = true;
        saveBtnText.classList.add('hidden');
        saveBtnSpinner.classList.remove('hidden');
        saveBtn.disabled = true;

        const name = nameInput.value.trim();
        const categoryId = categoryInput.value;
        const url = editMode ? `/subcategories/${editId}` : '/subcategories';
        const method = editMode ? 'PUT' : 'POST';

        try {
            const response = await fetch(url, {
                method,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ name, category_id: categoryId }),
            });

            if (!response.ok) {
                const data = await response.json();
                throw new Error(data.message || 'Something went wrong!');
            }

            const data = await response.json();
            closeModalWithAnimation();

            if (editMode) {
                const item = document.querySelector(`[data-id="${editId}"]`).closest('.item');
                const nameSpan = item.querySelector('.font-medium');
                const categoryName = Array.from(categoryInput.options).find(o => o.value == categoryId)?.text || '';
                nameSpan.textContent = name;
                item.querySelector('.text-gray-500').textContent = categoryName;
                showToast('Subcategory updated successfully!', 'success');
            } else {
                const categoryName = Array.from(categoryInput.options).find(o => o.value == categoryId)?.text || '';
                const newItem = document.createElement('div');
                newItem.className = 'item bg-white shadow-sm border border-gray-200 p-4 rounded-lg flex justify-between items-center hover:shadow-md transition-all cursor-default';
                newItem.innerHTML = `
                    <div>
                        <div class="font-medium text-gray-800">${escapeHtml(name)}</div>
                        <div class="text-sm text-gray-500">${escapeHtml(categoryName)}</div>
                    </div>
                    <div class="space-x-2">
                        <button 
                            class="text-blue-600 hover:text-blue-800 edit-btn transition-all cursor-pointer hover:font-semibold" 
                            data-id="${data.id}" 
                            data-name="${escapeHtml(name)}"
                            data-category="${categoryId}"
                            title="Edit subcategory">
                            Edit
                        </button>
                        <button 
                            class="text-red-600 hover:text-red-800 delete-btn transition-all cursor-pointer hover:font-semibold" 
                            data-id="${data.id}"
                            title="Delete subcategory">
                            Delete
                        </button>
                    </div>
                `;
                
                itemList.appendChild(newItem);
                attachEventListeners(newItem);
                checkEmptyState();
                showToast('Subcategory added successfully!', 'success');
            }
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
            editBtn.addEventListener('click', (e) => {
                editMode = true;
                editId = e.target.dataset.id;
                nameInput.value = e.target.dataset.name;
                categoryInput.value = e.target.dataset.category;
                openModal('Edit Subcategory', true);
            });
        }

        const deleteBtn = container.querySelector('.delete-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', async (e) => {
                const id = e.target.dataset.id;
                const item = e.target.closest('.item');
                const itemName = item.querySelector('.font-medium').textContent;

                if (!confirm(`Are you sure you want to delete "${itemName}"?`)) return;

                try {
                    const response = await fetch(`/subcategories/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                        },
                    });

                    if (!response.ok) {
                        throw new Error('Failed to delete subcategory.');
                    }

                    item.classList.add('removing');
                    setTimeout(() => {
                        item.remove();
                        checkEmptyState();
                        showToast('Subcategory deleted successfully!', 'success');
                    }, 300);
                } catch (error) {
                    showToast(error.message, 'error');
                }
            });
        }
    };

    // Initialize event listeners
    document.querySelectorAll('.item').forEach(item => {
        attachEventListeners(item);
    });

    // Enter key
    itemForm.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && !isSubmitting) {
            e.preventDefault();
            saveBtn.click();
        }
    });
});
</script>
@endsection