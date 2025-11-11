@extends('layouts.app')

@section('content')
<style>
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .badge-jazz {
        background-color: #f97316;
        color: white;
    }

    .badge-easy {
        background-color: #06b6d4;
        color: white;
    }

    .badge-bank {
        background-color: #8b5cf6;
        color: white;
    }

    .badge-cash {
        background-color: #10b981;
        color: white;
    }

    .payment-row {
        animation: slideIn 0.3s ease-out;
    }
</style>

<div class="max-w-7xl mx-auto space-y-6 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">Bank & Payment Summary</h2>
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="hidden text-center py-12">
        <div class="text-gray-400 text-6xl mb-4">💳</div>
        <p class="text-gray-500 text-lg">No sales yet</p>
    </div>

    <!-- Sales & Payments Table -->
    <div id="tableContainer" class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left font-semibold text-gray-800">Invoice #</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-800">Date</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-800">Type</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-800">Total</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-800">Payments Received</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-800">Remaining</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-800">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($sales as $sale)
                    <tr class="payment-row hover:bg-gray-50 transition-colors cursor-pointer" onclick="togglePaymentDetails(this)">
                        <td class="px-6 py-4 font-mono font-semibold text-gray-900">
                            {{ $sale->invoice_number }}
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                            {{ $sale->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-xs font-medium
                                @if($sale->type === 'dine_in') bg-blue-100 text-blue-800
                                @elseif($sale->type === 'takeaway') bg-green-100 text-green-800
                                @else bg-yellow-100 text-yellow-800
                                @endif">
                                {{ ucfirst(str_replace('_', ' ', $sale->type)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 font-semibold text-gray-900">
                            Rs. {{ number_format($sale->total, 2) }}
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $totalPaid = $sale->payments->sum('amount');
                            @endphp
                            <div class="space-y-1">
                                @forelse($sale->payments as $payment)
                                <div class="flex items-center gap-2">
                                    <span class="badge
                                        @if($payment->method === 'jazz_cash') badge-jazz
                                        @elseif($payment->method === 'easy_paisa') badge-easy
                                        @elseif($payment->method === 'bank_transfer') badge-bank
                                        @else badge-cash
                                        @endif">
                                        {{ ucfirst(str_replace('_', ' ', $payment->method)) }}
                                    </span>
                                    <span class="font-semibold text-gray-900">
                                        Rs. {{ number_format($payment->amount, 2) }}
                                    </span>
                                </div>
                                @empty
                                <span class="text-gray-400 text-xs">No payments yet</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $remaining = $sale->total - $totalPaid;
                            @endphp
                            <span class="font-semibold
                                @if($remaining <= 0) text-green-600
                                @elseif($remaining < $sale->total / 2) text-yellow-600
                                @else text-red-600
                                @endif">
                                Rs. {{ number_format($remaining, 2) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $remaining = $sale->total - $totalPaid;
                            @endphp
                            @if($remaining <= 0)
                                <span class="inline-block px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Paid
                                </span>
                            @elseif($totalPaid > 0)
                                <span class="inline-block px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Partial
                                </span>
                            @else
                                <span class="inline-block px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Pending
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            No sales found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($sales->hasPages())
    <div class="flex justify-center">
        {{ $sales->links() }}
    </div>
    @endif

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <p class="text-sm text-gray-600">Total Sales</p>
            @php
                // Prefer controller-provided value; fall back to querying the model so the card still works
                $totalSales = isset($totalSalesAmount) ? (float) $totalSalesAmount : (float) \App\Models\Sale::sum('total');
            @endphp
            <p class="text-2xl font-bold text-gray-900">
                Rs. {{ number_format($totalSales, 2) }}
            </p>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <p class="text-sm text-gray-600">Total Collected</p>
            <p class="text-2xl font-bold text-green-600">
                Rs. {{ number_format($totalCollected ?? 0, 2) }}
            </p>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <p class="text-sm text-gray-600">Pending</p>
            <p class="text-2xl font-bold text-red-600">
                Rs. {{ number_format($totalPending ?? 0, 2) }}
            </p>
        </div>
    </div>
</div>

<script>
    function togglePaymentDetails(row) {
        row.classList.toggle('bg-blue-50');
    }
</script>
@endsection
