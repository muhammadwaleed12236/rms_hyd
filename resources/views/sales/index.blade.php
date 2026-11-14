@extends('layouts.app')

@section('page-title', 'Sales History')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Sales History</h1>
            <p class="text-gray-600 mt-1">View and manage all transactions</p>
        </div>
        <a href="{{ route('pos.index') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-gray-800 hover:bg-gray-900 text-white font-semibold rounded-lg transition-all">
            New Sale
        </a>
    </div>

    <!-- Filters Section -->
    <div class="flex flex-col sm:flex-row gap-4">
        <!-- Search Bar -->
        <div class="flex-1">
            <input type="text" id="searchInput" placeholder="Search by invoice, customer name..." 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-400 focus:border-transparent outline-none">
        </div>

        <!-- Filter Dropdown -->
        <div class="min-w-[180px]">
            <select id="filterDate" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-400 focus:border-transparent outline-none">
                <option value="all">All Time</option>
                <option value="today">Today</option>
                <option value="week">This Week</option>
                <option value="month">This Month</option>
            </select>
        </div>
    </div>

    <!-- Sales Table -->
    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-100 border-b border-gray-200">
                        <th class="px-6 py-4 text-left font-bold text-gray-900">Invoice</th>
                        <th class="px-6 py-4 text-left font-bold text-gray-900">Date & Time</th>
                        <th class="px-6 py-4 text-left font-bold text-gray-900">Customer</th>
                        <th class="px-6 py-4 text-center font-bold text-gray-900">Items</th>
                        <th class="px-6 py-4 text-right font-bold text-gray-900">Total Amount</th>
                        <th class="px-6 py-4 text-center font-bold text-gray-900">Payment</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sales as $sale)
                        <tr data-sale-date="{{ $sale->created_at }}" class="border-b">
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-2">
                                    <button class="view-details-btn text-sm text-blue-600 hover:underline" data-sale="{{ json_encode($sale) }}">View</button>
                                    <span class="font-mono text-xs">{{ $sale->invoice_number }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-3">{{ $sale->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-6 py-3">{{ $sale->customer_name ?? '—' }}</td>
                            <td class="px-6 py-3 text-center">{{ is_array($sale->cart) ? count($sale->cart) : 0 }}</td>
                            <td class="px-6 py-3 text-right">Rs. {{ number_format($sale->total, 2) }}</td>
                            <td class="px-6 py-3 text-center">{{ $sale->payments->sum('amount') >= $sale->total ? 'Paid' : 'Partial' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-4">
            {{ $sales->links() }}
        </div>
    </div>

    <!-- Details Modal -->
    <div id="saleDetailsModal" class="hidden fixed inset-0 items-center justify-center bg-black bg-opacity-40 z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl max-h-96 overflow-y-auto p-4 mx-4">
            <div class="flex items-start justify-between mb-4">
                <h3 class="text-lg font-semibold">Sale Details</h3>
                <div class="flex items-center gap-2">
                    <button id="printDetailsBtn" class="px-3 py-1 bg-gray-800 text-white rounded text-sm">Print</button>
                    <button id="closeDetailsModal" class="px-3 py-1 bg-gray-200 rounded text-sm">Close</button>
                </div>
            </div>
            <div id="saleDetailsContent" class="mt-3 text-sm"></div>
            
            <!-- Add Payment Section (shown only if sale is not fully paid) -->
            <div id="addPaymentSection" class="hidden mt-4 pt-4 border-t border-gray-200">
                <h4 class="font-semibold text-sm mb-3">Add Payment</h4>
                <div class="flex items-end gap-2">
                    <div class="flex-1">
                        <label class="text-xs text-gray-600 block mb-1">Method</label>
                        <select id="paymentMethod" class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                            <option value="cash">Cash</option>
                            <option value="easypaisa">Easypaisa</option>
                            <option value="jazzcash">JazzCash</option>
                            <option value="bank">Bank Transfer</option>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="text-xs text-gray-600 block mb-1">Amount (Rs.)</label>
                        <input type="number" id="paymentAmount" min="0" step="1" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" placeholder="0">
                    </div>
                    <button id="addPaymentBtn" class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-sm font-semibold">Add</button>
                </div>
                <div id="paymentError" class="text-red-600 text-xs mt-2 hidden"></div>
            </div>
        </div>
    </div>

   

@endsection