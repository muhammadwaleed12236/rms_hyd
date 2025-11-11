<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreItemRequest;
use App\Models\Item;
use App\Models\Size;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::with('subcategory', 'sizes')->orderBy('created_at', 'desc')->get();
        return view('items.index', compact('items'));
    }

    public function store(StoreItemRequest $request)
    {
        $data = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('items', 'public');
        }

        $item = Item::create($data);

        foreach ($request->sizes as $size) {
            Size::create([
                'item_id' => $item->id,
                'size' => $size['size'],
                'price' => $size['price'],
            ]);
        }

        return response()->json($item->load('sizes'), 201);
    }

    public function show(Item $item)
    {
        return response()->json($item->load('sizes'));
    }

    public function update(StoreItemRequest $request, Item $item)
    {
        $data = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($item->image) {
                \Storage::disk('public')->delete($item->image);
            }
            $data['image'] = $request->file('image')->store('items', 'public');
        }

        $item->update($data);

        // Update sizes
        $item->sizes()->delete();
        foreach ($request->sizes as $size) {
            Size::create([
                'item_id' => $item->id,
                'size' => $size['size'],
                'price' => $size['price'],
            ]);
        }

        return response()->json($item->load('sizes'));
    }

    public function destroy(Item $item)
    {
        // Delete image if exists
        if ($item->image) {
            \Storage::disk('public')->delete($item->image);
        }

        $item->sizes()->delete();
        $item->delete();

        return response()->json(null, 204);
    }
}