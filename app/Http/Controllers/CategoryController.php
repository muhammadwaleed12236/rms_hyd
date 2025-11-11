<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        $subcategories = Subcategory::with('category')->get();
        if (request()->wantsJson()) {
            return response()->json($categories);
        }
        return view('categories.index', compact('categories', 'subcategories'));
    }

public function store(Request $request)
{
    $request->validate(['name' => 'required|string|max:255']);
    $category = Category::create(['name' => $request->name]);
    return response()->json(['success' => true, 'id' => $category->id]);
}

public function update(Request $request, Category $category)
{
    $request->validate(['name' => 'required|string|max:255']);
    $category->update(['name' => $request->name]);
    return response()->json(['success' => true]);
}

public function destroy(Category $category)
{
    $category->delete();
    return response()->json(['success' => true]);
}
}