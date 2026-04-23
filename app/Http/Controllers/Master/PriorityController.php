<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\TicketPriority;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PriorityController extends Controller
{
    public function index()
    {
        $priorities = TicketPriority::paginate(10);
        return Inertia::render('Master/Priority/Index', ['priorities' => $priorities]);
    }

    public function create()
    {
        return Inertia::render('Master/Priority/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'level' => 'required|integer|min:1|unique:ticket_priorities,level',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        TicketPriority::create($validated);

        return redirect()->route('master.priorities.index')->with('success', 'Prioritas berhasil ditambahkan.');
    }

    public function edit(TicketPriority $priority)
    {
        return Inertia::render('Master/Priority/Edit', ['priority' => $priority]);
    }

    public function update(Request $request, TicketPriority $priority)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'level' => 'required|integer|min:1|unique:ticket_priorities,level,' . $priority->id,
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $priority->update($validated);

        return redirect()->route('master.priorities.index')->with('success', 'Prioritas berhasil diperbarui.');
    }

    public function toggleActive(TicketPriority $priority)
    {
        $priority->update(['is_active' => !$priority->is_active]);
        return redirect()->back()->with('success', 'Status prioritas berhasil diubah.');
    }
}
