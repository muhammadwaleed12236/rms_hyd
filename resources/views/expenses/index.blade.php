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

    .expense-row {
        animation: slideIn 0.3s ease-out;
    }

    .expense-row.removing {
        animation: slideOut 0.2s ease-out forwards;
    }

    .toast {
        animation: slideIn 0.3s ease-out;
    }

    .toast.hide {
        animation: slideOut 0.3s ease-out forwards;
    }
</style>

<div class="max-w-4xl mx-auto space-y-6 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-3xl font-bold text-gray-800">Expenses</h2>
            <p class="text-gray-600 mt-1">Track and manage your business expenses</p>
        </div>
        <button id="openAddModal" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition-all cursor-pointer shadow-sm hover:shadow-md">
            + Add Expense
        </button>
    </div>

    <!-- Stats -->
    <div class="grid md:grid-cols-3 gap-4">
        <div class="bg-gradient-to-br from-red-500 to-red-600 text-white rounded-xl shadow-lg p-6">
            <p class="text-sm opacity-80">Total Expenses</p>
            <h3 class="text-3xl font-extrabold mt-2" id="totalExpenses">Rs. 0.00</h3>
        </div>
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl shadow-lg p-6">
            <p class="text-sm opacity-80">Number of Expenses</p>
            <h3 class="text-3xl font-extrabold mt-2" id="expenseCount">0</h3>
        </div>
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl shadow-lg p-6">
            <p class="text-sm opacity-80">Average Expense</p>
            <h3 class="text-3xl font-extrabold mt-2" id="avgExpense">Rs. 0.00</h3>
        </div>
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="hidden text-center py-12 bg-gray-50 rounded-lg">
        <div class="text-gray-400 text-6xl mb-4">💰</div>
        <p class="text-gray-500 text-lg">No expenses recorded yet</p>
        <p class="text-gray-400 text-sm">Add your first expense to get started</p>
    </div>

    <!-- Expenses Table -->
    <div id="expensesTable" class="hidden bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Title</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Amount</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Date</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody id="expensesList" class="divide-y divide-gray-200">
                    <!-- Expenses will be inserted here -->
                </tbody>
            </table>
        </div>
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

            <h3 id="modalTitle" class="text-xl font-semibold mb-4 text-gray-800">Add Expense</h3>

            <form id="expenseForm" class="space-y-4">
                <div>
                    <label for="titleInput" class="block text-sm font-medium text-gray-700 mb-2">Expense Title</label>
                    <input 
                        type="text" 
                        id="titleInput" 
                        placeholder="Enter expense title"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-all"
                        required>
                    <div id="titleError" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>

                <div>
                    <label for="amountInput" class="block text-sm font-medium text-gray-700 mb-2">Amount (Rs.)</label>
                    <input 
                        type="number" 
                        id="amountInput" 
                        placeholder="Enter amount"
                        step="0.01"
                        min="0"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-all"
                        required>
                    <div id="amountError" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>

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
    const titleInput = document.getElementById('titleInput');
    const amountInput = document.getElementById('amountInput');
    const expenseForm = document.getElementById('expenseForm');
    const saveBtn = document.getElementById('saveBtn');
    const closeBtn = document.getElementById('closeBtn');
    const closeModal = document.getElementById('closeModal');
    const titleError = document.getElementById('titleError');
    const amountError = document.getElementById('amountError');
    const saveBtnText = document.getElementById('saveBtnText');
    const saveBtnSpinner = document.getElementById('saveBtnSpinner');
    const expensesList = document.getElementById('expensesList');
    const emptyState = document.getElementById('emptyState');
    const expensesTable = document.getElementById('expensesTable');

    let editMode = false;
    let editId = null;
    let isSubmitting = false;
    let expenses = @json($expenses);

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

    const updateStats = () => {
        const total = expenses.reduce((sum, e) => sum + parseFloat(e.amount), 0);
        const count = expenses.length;
        const avg = count > 0 ? total / count : 0;

        document.getElementById('totalExpenses').textContent = `Rs. ${total.toFixed(2)}`;
        document.getElementById('expenseCount').textContent = count;
        document.getElementById('avgExpense').textContent = `Rs. ${avg.toFixed(2)}`;
    };

    const updateTable = () => {
        expensesList.innerHTML = '';
        
        if (expenses.length === 0) {
            expensesTable.classList.add('hidden');
            emptyState.classList.remove('hidden');
        } else {
            expensesTable.classList.remove('hidden');
            emptyState.classList.add('hidden');

            expenses.forEach(expense => {
                const row = document.createElement('tr');
                row.className = 'expense-row hover:bg-gray-50 transition-all';
                row.innerHTML = `
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-800">${escapeHtml(expense.title)}</div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="font-semibold text-gray-900">Rs. ${parseFloat(expense.amount).toFixed(2)}</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        ${new Date(expense.created_at).toLocaleDateString()}
                    </td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <button 
                            class="edit-btn text-blue-600 hover:text-blue-800 transition-all cursor-pointer font-semibold text-sm" 
                            data-id="${expense.id}"
                            data-title="${escapeHtml(expense.title)}"
                            data-amount="${expense.amount}">
                            Edit
                        </button>
                        <button 
                            class="delete-btn text-red-600 hover:text-red-800 transition-all cursor-pointer font-semibold text-sm" 
                            data-id="${expense.id}">
                            Delete
                        </button>
                    </td>
                `;
                expensesList.appendChild(row);

                const editBtn = row.querySelector('.edit-btn');
                editBtn.addEventListener('click', (e) => {
                    editMode = true;
                    editId = e.target.dataset.id;
                    titleInput.value = e.target.dataset.title;
                    amountInput.value = e.target.dataset.amount;
                    modalTitle.textContent = 'Edit Expense';
                    modal.classList.remove('hidden');
                    titleInput.focus();
                });

                const deleteBtn = row.querySelector('.delete-btn');
                deleteBtn.addEventListener('click', async (e) => {
                    const id = e.target.dataset.id;
                    const expense = expenses.find(exp => exp.id == id);
                    
                    if (!confirm(`Delete "${expense.title}"?`)) return;

                    try {
                        const response = await fetch(`/expenses/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            },
                        });

                        if (!response.ok) throw new Error('Failed to delete');

                        row.classList.add('removing');
                        setTimeout(() => {
                            expenses = expenses.filter(exp => exp.id != id);
                            updateTable();
                            updateStats();
                            showToast('Expense deleted successfully!', 'success');
                        }, 200);
                    } catch (error) {
                        showToast(error.message, 'error');
                    }
                });
            });
        }

        updateStats();
    };

    const escapeHtml = (text) => {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    };

    const openModal = (title = 'Add Expense', isEdit = false) => {
        editMode = isEdit;
        modalTitle.textContent = title;
        if (!isEdit) {
            titleInput.value = '';
            amountInput.value = '';
        }
        titleError.classList.add('hidden');
        amountError.classList.add('hidden');
        modal.classList.remove('hidden');
        titleInput.focus();
    };

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
        openModal('Add Expense', false);
    });

    closeBtn.addEventListener('click', closeModalWithAnimation);
    closeModal.addEventListener('click', closeModalWithAnimation);
    modalBackdrop.addEventListener('click', closeModalWithAnimation);

    const validateInput = () => {
        const title = titleInput.value.trim();
        const amount = amountInput.value;
        let isValid = true;

        if (!title) {
            titleError.textContent = 'Please enter an expense title.';
            titleError.classList.remove('hidden');
            titleInput.classList.add('border-red-500');
            isValid = false;
        } else {
            titleError.classList.add('hidden');
            titleInput.classList.remove('border-red-500');
        }

        if (!amount || parseFloat(amount) < 0) {
            amountError.textContent = 'Please enter a valid amount.';
            amountError.classList.remove('hidden');
            amountInput.classList.add('border-red-500');
            isValid = false;
        } else {
            amountError.classList.add('hidden');
            amountInput.classList.remove('border-red-500');
        }

        return isValid;
    };

    titleInput.addEventListener('input', () => {
        if (!titleError.classList.contains('hidden')) validateInput();
    });

    amountInput.addEventListener('change', () => {
        if (!amountError.classList.contains('hidden')) validateInput();
    });

    saveBtn.addEventListener('click', async () => {
        if (isSubmitting) return;
        if (!validateInput()) return;

        isSubmitting = true;
        saveBtnText.classList.add('hidden');
        saveBtnSpinner.classList.remove('hidden');
        saveBtn.disabled = true;

        const title = titleInput.value.trim();
        const amount = parseFloat(amountInput.value);
        const url = editMode ? `/expenses/${editId}` : '/expenses';
        const method = editMode ? 'PUT' : 'POST';

        try {
            const response = await fetch(url, {
                method,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ title, amount }),
            });

            if (!response.ok) {
                const data = await response.json();
                throw new Error(data.message || 'Something went wrong!');
            }

            const data = await response.json();
            closeModalWithAnimation();

            if (editMode) {
                const index = expenses.findIndex(exp => exp.id == editId);
                if (index !== -1) {
                    expenses[index] = data;
                }
                showToast('Expense updated successfully!', 'success');
            } else {
                expenses.unshift(data);
                showToast('Expense added successfully!', 'success');
            }

            updateTable();
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

    expenseForm.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && !isSubmitting) {
            e.preventDefault();
            saveBtn.click();
        }
    });

    updateTable();
});
</script>
@endsection