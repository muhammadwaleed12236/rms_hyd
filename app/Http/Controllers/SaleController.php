<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Sale;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index()
    {
        $items = Item::with('sizes', 'subcategory')->get();
        // Load recent sales with payments for the sales list / payment flow
        $sales = Sale::with('payments')->orderBy('created_at', 'desc')->limit(50)->get();

        return view('sales.index', compact('items', 'sales'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|string|in:dine_in,take_away,delivery',
            'total' => 'required|numeric|min:0',
            'cart' => 'required|array|min:1',
            'cart.*.item_id' => 'required|exists:items,id',
            'cart.*.size' => 'required|string',
            'cart.*.price' => 'required|numeric|min:0',
            'cart.*.quantity' => 'required|integer|min:1',
            // Customer name required for dine_in/take_away, optional for delivery
            'customer_name' => [
                function ($attribute, $value, $fail) use ($request) {
                    $type = $request->input('type');
                    if (in_array($type, ['dine_in', 'take_away']) && (!is_string($value) || trim($value) === '')) {
                        $fail('Customer name is required for dine in and take away orders.');
                    }
                },
                'nullable', 'string', 'max:255'
            ],
            'customer_phone' => 'nullable|string|max:20',
            'customer_address' => 'nullable|string|max:500',
        ]);

        // Wrap creation in a transaction so payments are saved atomically with the sale
        $sale = DB::transaction(function () use ($data, $request) {
            $sale = Sale::create([
                'type' => $data['type'],
                'total' => $data['total'],
                'cart' => $data['cart'],
                'customer_name' => $data['customer_name'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'customer_address' => $data['customer_address'] ?? null,
            ]);

            // If frontend provided payments in payload, persist them and associate with the sale
            $payments = $request->input('payments');
            if (is_array($payments) && count($payments) > 0) {
                foreach ($payments as $p) {
                    // Basic safety: ensure required keys exist
                    if (empty($p['method']) || empty($p['amount'])) continue;

                    Payment::create([
                        'sale_id' => $sale->id,
                        'method' => $p['method'],
                        'amount' => $p['amount'],
                        'transaction_id' => $p['transaction_id'] ?? null,
                    ]);
                }
            }

            // Reload payments relationship
            $sale->load('payments');

            return $sale;
        });

        return response()->json([
            'success' => true,
            'id' => $sale->id,
            'invoice_number' => $sale->invoice_number,
            'total' => $sale->total,
            'payments' => $sale->payments,
        ], 201);
    }

    public function show(Sale $sale)
    {
        return response()->json($sale);
    }
}