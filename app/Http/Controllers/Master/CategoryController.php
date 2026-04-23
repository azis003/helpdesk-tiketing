<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\TicketCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = TicketCategory::withCount('tickets')->paginate(10);
        return Inertia::render('Master/Category/Index', ['categories' => $categories]);
    }

    public function create()
    {
        return Inertia::render('Master/Category/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        TicketCategory::create($validated);

        return redirect()->route('master.categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(TicketCategory $category)
    {
        return Inertia::render('Master/Category/Edit', ['category' => $category]);
    }

    public function update(Request $request, TicketCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        $category->update($validated);

        return redirect()->route('master.categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function toggleActive(TicketCategory $category)
    {
        $category->update(['is_active' => !$category->is_active]);
        return redirect()->back()->with('success', 'Status kategori berhasil diubah.');
    }
}
