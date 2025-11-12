<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Sale;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $totalSales = Sale::count();
        $totalRevenue = Sale::sum('total');
        $totalExpenses = Expense::sum('amount');
        $recentSales = Sale::orderBy('created_at', 'desc')->take(5)->get();

        // Weekly sales data (last 12 weeks)
        $weeklySalesData = $this->getWeeklySalesData();

        // Top selling items
        $topItems = $this->getTopSellingItems();

        return view('dashboard', compact('totalSales', 'totalRevenue', 'totalExpenses', 'recentSales', 'weeklySalesData', 'topItems'));
    }

    private function getWeeklySalesData(): array
    {
        $weeks = [];
        $salesData = [];
        $revenueData = [];

        // Get last 12 weeks
        for ($i = 11; $i >= 0; $i--) {
            $startOfWeek = Carbon::now()->subWeeks($i)->startOfWeek();
            $endOfWeek = Carbon::now()->subWeeks($i)->endOfWeek();

            $weekLabel = $startOfWeek->format('M d').' - '.$endOfWeek->format('M d');
            $weeks[] = $weekLabel;

            $sales = Sale::whereBetween('created_at', [$startOfWeek, $endOfWeek])->count();
            $revenue = Sale::whereBetween('created_at', [$startOfWeek, $endOfWeek])->sum('total');

            $salesData[] = $sales;
            $revenueData[] = round($revenue ?? 0, 2);
        }

        return [
            'weeks' => $weeks,
            'sales' => $salesData,
            'revenue' => $revenueData,
        ];
    }

    private function getTopSellingItems(): array
    {
        $topItems = [];

        $sales = Sale::all();

        $itemCounts = [];
        $itemRevenue = [];

        foreach ($sales as $sale) {
            $cart = $sale->cart;
            if (is_array($cart)) {
                foreach ($cart as $item) {
                    $itemName = $item['name'] ?? 'Unknown';
                    $quantity = $item['quantity'] ?? 0;
                    $price = $item['price'] ?? 0;
                    $itemRevenue[$itemName] = ($itemRevenue[$itemName] ?? 0) + ($price * $quantity);
                    $itemCounts[$itemName] = ($itemCounts[$itemName] ?? 0) + $quantity;
                }
            }
        }

        // Sort by quantity and get top 8
        arsort($itemCounts);
        $topItemsList = array_slice($itemCounts, 0, 8, true);

        foreach ($topItemsList as $itemName => $quantity) {
            $topItems[] = [
                'name' => $itemName,
                'quantity' => $quantity,
                'revenue' => round($itemRevenue[$itemName] ?? 0, 2),
            ];
        }

        return $topItems;
    }
}
