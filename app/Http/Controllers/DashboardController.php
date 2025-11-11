<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Expense;
use App\Models\Category;
use App\Models\Item;

class DashboardController extends Controller
{
    public function index()
    {
        $totalSales = Sale::count();
        $totalRevenue = Sale::sum('total');
        $totalExpenses = Expense::sum('amount');
        $recentSales = Sale::orderBy('created_at', 'desc')->take(5)->get();

        return view('dashboard', compact('totalSales', 'totalRevenue', 'totalExpenses', 'recentSales'));
    }
}