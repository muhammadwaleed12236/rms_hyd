@extends('layouts.app')

@section('content')
<style>
    @keyframes slideIn {
        from { opacity: 0; transform: scale(0.95) translateY(-10px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }
    @keyframes slideOut {
        from { opacity: 1; transform: scale(1) translateY(0); }
        to { opacity: 0; transform: scale(0.95) translateY(-10px); }
    }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes fadeOut { from { opacity: 1; } to { opacity: 0; } }
    .item { animation: slideIn 0.4s ease-out; }
    .item.removing { animation: slideOut 0.3s ease-out forwards; }
    .modal-content { animation: slideIn 0.3s ease-out; }
    .modal-content.closing { animation: slideOut 0.3s ease-out; }
    .modal-backdrop { animation: fadeIn 0.2s ease-out; }
    .modal-backdrop.closing { animation: fadeOut 0.2s ease-out; }
    .toast { animation: slideIn 0.3s ease-out; }
    .toast.hide { animation: slideOut 0.3s ease-out forwards; }
</style>

<div class="max-w-6xl mx-auto space-y-6 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">Categories & Subcategories</h2>
    </div>

    <!-- Two Column Layout -->
    <div class="grid lg:grid-cols-2 gap-6">
        <!-- Categories Section -->
        <div>
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-700">Categories</h3>
                <button id="openAddCategoryModal" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs transition-all">+ Add</button>
            </div>
            <div id="categoryList" class="space-y-2">
                @forelse($categories as $category)
                <div class="item bg-white shadow-sm border border-gray-200 p-3 rounded-lg flex justify-between items-center hover:shadow-md transition-all">
                    <span class="font-medium text-gray-800 text-sm">{{ $category->name }}</span>
                    <div class="space-x-2">
                        <button class="text-blue-600 hover:text-blue-800 edit-category text-xs" data-id="{{ $category->id }}" data-name="{{ $category->name }}">Edit</button>
                        <button class="text-red-600 hover:text-red-800 delete-category text-xs" data-id="{{ $category->id }}">Delete</button>
                    </div>
                </div>
                @empty
                <div class="text-center text-gray-400 py-8">No categories yet</div>
                @endforelse
            </div>
        </div>

        <!-- Subcategories Section -->
        <div>
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-700">Subcategories</h3>
                <button id="openAddSubcategoryModal" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs transition-all">+ Add</button>
            </div>
            <div id="subcategoryList" class="space-y-2">
                @forelse($subcategories as $subcategory)
                <div class="item bg-white shadow-sm border border-gray-200 p-3 rounded-lg hover:shadow-md transition-all">
                    <div class="flex justify-between items-start mb-1">
                        <span class="font-medium text-gray-800 text-sm">{{ $subcategory->name }}</span>
                        <div class="space-x-2">
                            <button class="text-blue-600 hover:text-blue-800 edit-subcategory text-xs" data-id="{{ $subcategory->id }}" data-name="{{ $subcategory->name }}" data-category="{{ $subcategory->category_id }}">Edit</button>
                            <button class="text-red-600 hover:text-red-800 delete-subcategory text-xs" data-id="{{ $subcategory->id }}">Delete</button>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500">{{ $subcategory->category->name }}</div>
                </div>
                @empty
                <div class="text-center text-gray-400 py-8">No subcategories yet</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Category Modal -->
<div id="categoryModal" class="fixed inset-0 hidden z-50">
    <div id="categoryBackdrop" class="absolute inset-0 bg-black/10 backdrop-blur-sm"></div>
    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
        <div id="categoryModalContent" class="modal-content bg-white rounded-xl shadow-2xl w-full max-w-md p-6 pointer-events-auto">
            <button id="closeCategoryModal" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">&times;</button>
            <h3 id="categoryModalTitle" class="text-xl font-semibold mb-4">Add Category</h3>
            <form id="categoryForm" class="space-y-4">
                <input type="text" id="categoryNameInput" placeholder="Category name" class="w-full border rounded px-3 py-2" required>
                <div id="categoryError" class="text-red-500 text-sm hidden"></div>
                <div class="flex justify-end space-x-2">
                    <button type="button" id="closeCategoryBtn" class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300">Cancel</button>
                    <button type="button" id="saveCategoryBtn" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Subcategory Modal -->
<div id="subcategoryModal" class="fixed inset-0 hidden z-50">
    <div id="subcategoryBackdrop" class="absolute inset-0 bg-black/10 backdrop-blur-sm"></div>
    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
        <div id="subcategoryModalContent" class="modal-content bg-white rounded-xl shadow-2xl w-full max-w-md p-6 pointer-events-auto">
            <button id="closeSubcategoryModal" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">&times;</button>
            <h3 id="subcategoryModalTitle" class="text-xl font-semibold mb-4">Add Subcategory</h3>
            <form id="subcategoryForm" class="space-y-4">
                <input type="text" id="subcategoryNameInput" placeholder="Subcategory name" class="w-full border rounded px-3 py-2" required>
                <select id="subcategoryCategoryInput" class="w-full border rounded px-3 py-2" required>
                    <option value="">Select category</option>
                </select>
                <div id="subcategoryError" class="text-red-500 text-sm hidden"></div>
                <div class="flex justify-end space-x-2">
                    <button type="button" id="closeSubcategoryBtn" class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300">Cancel</button>
                    <button type="button" id="saveSubcategoryBtn" class="px-4 py-2 rounded bg-green-600 text-white hover:bg-green-700">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toast -->
<div id="toast" class="fixed bottom-4 right-4 hidden px-4 py-3 rounded-lg text-white text-sm shadow-lg z-50"></div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const categoryModal = document.getElementById('categoryModal');
    const subcategoryModal = document.getElementById('subcategoryModal');
    const categoryBackdrop = document.getElementById('categoryBackdrop');
    const subcategoryBackdrop = document.getElementById('subcategoryBackdrop');
    const categoryModalContent = document.getElementById('categoryModalContent');
    const subcategoryModalContent = document.getElementById('subcategoryModalContent');

    let editCategoryId = null;
    let editSubcategoryId = null;

    // Toast helper
    const showToast = (msg, type = 'success') => {
        const toast = document.getElementById('toast');
        toast.textContent = msg;
        toast.style.backgroundColor = type === 'success' ? '#10b981' : '#ef4444';
        toast.classList.remove('hidden', 'hide');
        setTimeout(() => { toast.classList.add('hide'); }, 3000);
    };

    // Close modals
    const closeModalWithAnimation = (modal, backdrop, content) => {
        content.classList.add('closing');
        backdrop.classList.add('closing');
        setTimeout(() => {
            modal.classList.add('hidden');
            content.classList.remove('closing');
            backdrop.classList.remove('closing');
        }, 300);
    };

    // Load categories for subcategory select
    const loadCategoriesForSelect = async () => {
        try {
            const res = await fetch('/api/categories');
            const categories = await res.json();
            const select = document.getElementById('subcategoryCategoryInput');
            select.innerHTML = '<option value="">Select category</option>';
            categories.forEach(cat => {
                const opt = document.createElement('option');
                opt.value = cat.id;
                opt.textContent = cat.name;
                select.appendChild(opt);
            });
        } catch (e) { console.error(e); }
    };
    loadCategoriesForSelect();

    // Category modal events
    document.getElementById('openAddCategoryModal').addEventListener('click', () => {
        editCategoryId = null;
        document.getElementById('categoryModalTitle').textContent = 'Add Category';
        document.getElementById('categoryNameInput').value = '';
        categoryModal.classList.remove('hidden');
    });

    document.getElementById('closeCategoryModal').addEventListener('click', () => {
        closeModalWithAnimation(categoryModal, categoryBackdrop, categoryModalContent);
    });

    document.getElementById('closeCategoryBtn').addEventListener('click', () => {
        closeModalWithAnimation(categoryModal, categoryBackdrop, categoryModalContent);
    });

    categoryBackdrop.addEventListener('click', () => {
        closeModalWithAnimation(categoryModal, categoryBackdrop, categoryModalContent);
    });

    // Subcategory modal events
    document.getElementById('openAddSubcategoryModal').addEventListener('click', () => {
        editSubcategoryId = null;
        document.getElementById('subcategoryModalTitle').textContent = 'Add Subcategory';
        document.getElementById('subcategoryNameInput').value = '';
        document.getElementById('subcategoryCategoryInput').value = '';
        subcategoryModal.classList.remove('hidden');
    });

    document.getElementById('closeSubcategoryModal').addEventListener('click', () => {
        closeModalWithAnimation(subcategoryModal, subcategoryBackdrop, subcategoryModalContent);
    });

    document.getElementById('closeSubcategoryBtn').addEventListener('click', () => {
        closeModalWithAnimation(subcategoryModal, subcategoryBackdrop, subcategoryModalContent);
    });

    subcategoryBackdrop.addEventListener('click', () => {
        closeModalWithAnimation(subcategoryModal, subcategoryBackdrop, subcategoryModalContent);
    });

    // Save category
    document.getElementById('saveCategoryBtn').addEventListener('click', async () => {
        const name = document.getElementById('categoryNameInput').value.trim();
        if (!name) {
            document.getElementById('categoryError').textContent = 'Name required';
            document.getElementById('categoryError').classList.remove('hidden');
            return;
        }

        try {
            const method = editCategoryId ? 'PUT' : 'POST';
            const url = editCategoryId ? `/categories/${editCategoryId}` : '/categories';
            const res = await fetch(url, {
                method,
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                body: JSON.stringify({ name })
            });

            if (!res.ok) throw new Error('Failed');

            const data = await res.json();
            closeModalWithAnimation(categoryModal, categoryBackdrop, categoryModalContent);

            if (editCategoryId) {
                const item = document.querySelector(`.edit-category[data-id="${editCategoryId}"]`).closest('.item');
                item.querySelector('span').textContent = name;
                item.querySelector('.edit-category').dataset.name = name;
                showToast('Category updated');
            } else {
                const item = document.createElement('div');
                item.className = 'item bg-white shadow-sm border border-gray-200 p-3 rounded-lg flex justify-between items-center hover:shadow-md transition-all';
                item.innerHTML = `<span class="font-medium text-gray-800 text-sm">${escapeHtml(name)}</span>
                    <div class="space-x-2">
                        <button class="text-blue-600 hover:text-blue-800 edit-category text-xs" data-id="${data.id}" data-name="${escapeHtml(name)}">Edit</button>
                        <button class="text-red-600 hover:text-red-800 delete-category text-xs" data-id="${data.id}">Delete</button>
                    </div>`;
                document.getElementById('categoryList').appendChild(item);
                attachCategoryListeners(item);
                loadCategoriesForSelect();
                showToast('Category added');
            }
        } catch (e) { showToast(e.message, 'error'); }
    });

    // Save subcategory
    document.getElementById('saveSubcategoryBtn').addEventListener('click', async () => {
        const name = document.getElementById('subcategoryNameInput').value.trim();
        const categoryId = document.getElementById('subcategoryCategoryInput').value;
        if (!name || !categoryId) {
            document.getElementById('subcategoryError').textContent = 'All fields required';
            document.getElementById('subcategoryError').classList.remove('hidden');
            return;
        }

        try {
            const method = editSubcategoryId ? 'PUT' : 'POST';
            const url = editSubcategoryId ? `/subcategories/${editSubcategoryId}` : '/subcategories';
            const res = await fetch(url, {
                method,
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                body: JSON.stringify({ name, category_id: categoryId })
            });

            if (!res.ok) throw new Error('Failed');

            const data = await res.json();
            closeModalWithAnimation(subcategoryModal, subcategoryBackdrop, subcategoryModalContent);

            if (editSubcategoryId) {
                const item = document.querySelector(`.edit-subcategory[data-id="${editSubcategoryId}"]`).closest('.item');
                item.querySelector('span').textContent = name;
                item.querySelector('.text-xs.text-gray-500').textContent = Array.from(document.getElementById('subcategoryCategoryInput').options).find(o => o.value == categoryId)?.textContent || '';
                item.querySelector('.edit-subcategory').dataset.name = name;
                item.querySelector('.edit-subcategory').dataset.category = categoryId;
                showToast('Subcategory updated');
            } else {
                const categoryName = Array.from(document.getElementById('subcategoryCategoryInput').options).find(o => o.value == categoryId)?.textContent || '';
                const item = document.createElement('div');
                item.className = 'item bg-white shadow-sm border border-gray-200 p-3 rounded-lg hover:shadow-md transition-all';
                item.innerHTML = `<div class="flex justify-between items-start mb-1 w-full">
                        <span class="font-medium text-gray-800 text-sm">${escapeHtml(name)}</span>
                        <div class="space-x-2">
                            <button class="text-blue-600 hover:text-blue-800 edit-subcategory text-xs" data-id="${data.id}" data-name="${escapeHtml(name)}" data-category="${categoryId}">Edit</button>
                            <button class="text-red-600 hover:text-red-800 delete-subcategory text-xs" data-id="${data.id}">Delete</button>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500">${escapeHtml(categoryName)}</div>`;
                document.getElementById('subcategoryList').appendChild(item);
                attachSubcategoryListeners(item);
                showToast('Subcategory added');
            }
        } catch (e) { showToast(e.message, 'error'); }
    });

    // Edit/Delete handlers
    const attachCategoryListeners = (container) => {
        container.querySelector('.edit-category')?.addEventListener('click', (e) => {
            editCategoryId = e.target.dataset.id;
            document.getElementById('categoryNameInput').value = e.target.dataset.name;
            document.getElementById('categoryModalTitle').textContent = 'Edit Category';
            categoryModal.classList.remove('hidden');
        });

        container.querySelector('.delete-category')?.addEventListener('click', async (e) => {
            const id = e.target.dataset.id;
            if (!confirm('Delete this category?')) return;
            try {
                const res = await fetch(`/categories/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
                if (!res.ok) throw new Error('Failed');
                e.target.closest('.item').classList.add('removing');
                setTimeout(() => e.target.closest('.item').remove(), 300);
                showToast('Category deleted');
            } catch (e) { showToast(e.message, 'error'); }
        });
    };

    const attachSubcategoryListeners = (container) => {
        container.querySelector('.edit-subcategory')?.addEventListener('click', (e) => {
            editSubcategoryId = e.target.dataset.id;
            document.getElementById('subcategoryNameInput').value = e.target.dataset.name;
            document.getElementById('subcategoryCategoryInput').value = e.target.dataset.category;
            document.getElementById('subcategoryModalTitle').textContent = 'Edit Subcategory';
            subcategoryModal.classList.remove('hidden');
        });

        container.querySelector('.delete-subcategory')?.addEventListener('click', async (e) => {
            const id = e.target.dataset.id;
            if (!confirm('Delete this subcategory?')) return;
            try {
                const res = await fetch(`/subcategories/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
                if (!res.ok) throw new Error('Failed');
                e.target.closest('.item').classList.add('removing');
                setTimeout(() => e.target.closest('.item').remove(), 300);
                showToast('Subcategory deleted');
            } catch (e) { showToast(e.message, 'error'); }
        });
    };

    // Attach to existing items
    document.querySelectorAll('.edit-category, .delete-category').forEach(btn => attachCategoryListeners(btn.closest('.item')));
    document.querySelectorAll('.edit-subcategory, .delete-subcategory').forEach(btn => attachSubcategoryListeners(btn.closest('.item')));

    // Enter key to submit forms
    document.getElementById('categoryForm').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('saveCategoryBtn').click();
        }
    });

    document.getElementById('subcategoryForm').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('saveSubcategoryBtn').click();
        }
    });

    const escapeHtml = (text) => {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    };
});
</script>
@endsection
