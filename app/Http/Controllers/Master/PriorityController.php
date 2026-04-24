<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StorePriorityRequest;
use App\Http\Requests\Master\UpdatePriorityRequest;
use App\Models\TicketPriority;
use Inertia\Inertia;

class PriorityController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $query = \App\Models\TicketPriority::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $priorities = $query->orderBy('level')->paginate(10)->withQueryString();

        return Inertia::render('Master/Priority/Index', [
            'priorities' => $priorities,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('Master/Priority/Create');
    }

    public function store(StorePriorityRequest $request)
    {
        TicketPriority::query()->create($request->validated());

        return redirect()->route('master.priorities.index')->with('success', 'Prioritas berhasil ditambahkan.');
    }

    public function edit(TicketPriority $priority)
    {
        return Inertia::render('Master/Priority/Edit', ['priority' => $priority]);
    }

    public function update(UpdatePriorityRequest $request, TicketPriority $priority)
    {
        $priority->update($request->validated());

        return redirect()->route('master.priorities.index')->with('success', 'Prioritas berhasil diperbarui.');
    }

    public function toggleActive(TicketPriority $priority)
    {
        $priority->update(['is_active' => !$priority->is_active]);
        return redirect()->back()->with('success', 'Status prioritas berhasil diubah.');
    }
}
