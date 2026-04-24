<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreCategoryRequest;
use App\Http\Requests\Master\UpdateCategoryRequest;
use App\Models\TicketCategory;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $query = TicketCategory::withCount('tickets');

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $categories = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Master/Category/Index', [
            'categories' => $categories,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('Master/Category/Create');
    }

    public function store(StoreCategoryRequest $request)
    {
        TicketCategory::query()->create($request->validated());

        return redirect()->route('master.categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(TicketCategory $category)
    {
        return Inertia::render('Master/Category/Edit', ['category' => $category]);
    }

    public function update(UpdateCategoryRequest $request, TicketCategory $category)
    {
        $category->update($request->validated());

        return redirect()->route('master.categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function toggleActive(TicketCategory $category)
    {
        $category->update(['is_active' => !$category->is_active]);
        return redirect()->back()->with('success', 'Status kategori berhasil diubah.');
    }
}
