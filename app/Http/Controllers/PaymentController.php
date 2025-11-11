<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Models\Payment;
use App\Models\Sale;

class PaymentController extends Controller
{
    /**
     * Display a listing of sales and payment summary.
     */
    public function index()
    {
        // Get recent sales with their payment relations
        $sales = Sale::with('payments')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        /**
         * Calculate totals across all sales.
         * Make sure the `sales` table has a numeric `total` column.
         */
        $totalSalesAmount = (float) Sale::sum('total'); // Sum of all sale totals
        $totalCollected   = (float) Payment::sum('amount'); // Sum of all payment amounts
        $totalPending     = max(0, $totalSalesAmount - $totalCollected); // Prevent negative
        $totalOrders      = Sale::count(); // Total number of orders

        // Return all to the view
        return view('payments.index', compact(
            'sales',
            'totalSalesAmount',
            'totalCollected',
            'totalPending',
            'totalOrders'
        ));
    }

    /**
     * Store a new payment for a sale.
     */
    public function store(StorePaymentRequest $request)
    {
        $data = $request->validated();

        // Prevent duplicate payment creation within a short time window
        $query = Payment::where('sale_id', $data['sale_id'])
            ->where('method', $data['method'])
            ->where('amount', $data['amount']);

        if (!empty($data['transaction_id'])) {
            $query->where('transaction_id', $data['transaction_id']);
        } else {
            $query->whereNull('transaction_id');
        }

        $recent = (clone $query)
            ->where('created_at', '>=', now()->subSeconds(5))
            ->first();

        if ($recent) {
            return response()->json([
                'message' => 'Duplicate payment detected.',
                'payment' => $recent->load('sale'),
            ], 200);
        }

        // Create payment
        $payment = Payment::create($data);

        return response()->json([
            'message' => 'Payment recorded successfully.',
            'payment' => $payment->load('sale'),
        ], 201);
    }

    /**
     * Show a single sale with its payments.
     */
    public function show(Sale $sale)
    {
        return response()->json($sale->load('payments'));
    }
}
