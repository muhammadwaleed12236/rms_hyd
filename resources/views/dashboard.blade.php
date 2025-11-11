@extends('layouts.app')

@section('content')
<div class="space-y-10">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <h2 class="text-3xl font-bold text-gray-800">Dashboard</h2>
        <span class="text-sm text-gray-500">Updated {{ now()->format('d M Y, h:i A') }}</span>
    </div>

    <!-- Stats Overview -->
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Sales -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl shadow-lg p-6">
            <p class="text-sm opacity-80">Total Sales</p>
            <h3 class="text-3xl font-extrabold mt-2">{{ $totalSales }}</h3>
        </div>

        <!-- Total Revenue -->
        <div class="bg-gradient-to-br from-green-500 to-emerald-600 text-white rounded-xl shadow-lg p-6">
            <p class="text-sm opacity-80">Total Revenue</p>
            <h3 class="text-3xl font-extrabold mt-2">Rs. {{ number_format($totalRevenue, 2) }}</h3>
        </div>

        <!-- Total Expenses -->
        <div class="bg-gradient-to-br from-rose-500 to-red-600 text-white rounded-xl shadow-lg p-6">
            <p class="text-sm opacity-80">Total Expenses</p>
            <h3 class="text-3xl font-extrabold mt-2">Rs. {{ number_format($totalExpenses, 2) }}</h3>
        </div>

        <!-- Net Profit -->
        <div class="bg-gradient-to-br from-indigo-500 to-purple-600 text-white rounded-xl shadow-lg p-6">
            <p class="text-sm opacity-80">Net Profit</p>
            <h3 class="text-3xl font-extrabold mt-2">Rs. {{ number_format($totalRevenue - $totalExpenses, 2) }}</h3>
        </div>
    </div>

    <!-- Recent Sales -->
    <div class="bg-white shadow-lg rounded-xl overflow-hidden">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-xl font-semibold text-gray-800">Recent Sales</h3>
            <a href="{{ route('sales.index') }}" class="text-blue-600 text-sm hover:underline">
                View All →
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead class="bg-gray-50 text-gray-600 text-sm uppercase tracking-wide">
                    <tr>
                        <th class="py-3 px-6 text-left">Sale Type</th>
                        <th class="py-3 px-6 text-left">Total (Rs.)</th>
                        <th class="py-3 px-6 text-left">Customer</th>
                        <th class="py-3 px-6 text-left">Date</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    @foreach($recentSales as $sale)
                        <tr class="border-b hover:bg-gray-50 transition">
                            <td class="py-3 px-6 capitalize">{{ $sale->type }}</td>
                            <td class="py-3 px-6 font-medium text-gray-900">{{ number_format($sale->total, 2) }}</td>
                            <td class="py-3 px-6">{{ $sale->customer_name ?? '—' }}</td>
                            <td class="py-3 px-6 text-gray-500">{{ $sale->created_at->format('d M Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
