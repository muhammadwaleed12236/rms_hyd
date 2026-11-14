<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\Payment;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index()
    {
        // Sales listing/history page
        $sales = Sale::with('payments')->orderBy('created_at', 'desc')->paginate(20);
       
        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        // POS page
        $categories = Category::all();
        $items = Item::with('sizes', 'subcategory')->get();

        return view('pos.index', compact('categories', 'items'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.size_id' => 'required|exists:sizes,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'sale_type' => 'required|string|in:dine_in,take_away,delivery',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_address' => 'nullable|string|max:500',
            'delivery_charge' => 'nullable|numeric|min:0',
            'payments' => 'nullable|array',
            'payments.*.method' => 'required_with:payments|string|in:cash,easypaisa,jazzcash,bank',
            'payments.*.amount' => 'required_with:payments|numeric|min:0.01',
            'total_amount' => 'required|numeric|min:0',
        ]);

        // Delivery type requires customer details
        if ($data['sale_type'] === 'delivery') {
            if (! $data['customer_name'] || ! $data['customer_phone'] || ! $data['customer_address']) {
                return response()->json([
                    'message' => 'Customer details are required for delivery orders',
                ], 422);
            }
        }

        // Wrap creation in a transaction
        $sale = DB::transaction(function () use ($data) {
            // Build cart array from items
            $cartItems = [];
            foreach ($data['items'] as $item) {
                $itemRecord = Item::findOrFail($item['item_id']);
                $sizeRecord = $itemRecord->sizes()->findOrFail($item['size_id']);

                $cartItems[] = [
                    'item_id' => $item['item_id'],
                    'name' => $itemRecord->name,
                    'size' => $sizeRecord->size,
                    'size_id' => $item['size_id'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'total' => $item['price'] * $item['quantity'],
                ];
            }

            $sale = Sale::create([
                'type' => $data['sale_type'],
                'total' => $data['total_amount'],
                'cart' => $cartItems,
                'customer_name' => $data['customer_name'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'customer_address' => $data['customer_address'] ?? null,
                'delivery_charge' => $data['delivery_charge'] ?? 0,
            ]);

            // Create payment records if provided
            if (! empty($data['payments'])) {
                foreach ($data['payments'] as $p) {
                    Payment::create([
                        'sale_id' => $sale->id,
                        'method' => $p['method'],
                        'amount' => $p['amount'],
                    ]);
                }
            }

            return $sale->load('payments');
        });

        return response()->json($sale, 201);
    }

    public function show(Sale $sale)
    {
        return response()->json($sale);
    }

    public function addPayment(Request $request, Sale $sale)
    {
        $data = $request->validate([
            'method' => 'required|string|in:cash,easypaisa,jazzcash,bank',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $totalPaid = $sale->payments()->sum('amount');
        $remaining = $sale->total - $totalPaid;

        if ($data['amount'] > $remaining) {
            return response()->json([
                'message' => 'Payment amount exceeds remaining balance',
            ], 422);
        }

        Payment::create([
            'sale_id' => $sale->id,
            'method' => $data['method'],
            'amount' => $data['amount'],
        ]);

        return response()->json([
            'message' => 'Payment added successfully',
            'sale' => $sale->load('payments'),
        ], 201);
    }
}
