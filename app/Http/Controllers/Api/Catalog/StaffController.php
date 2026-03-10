<?php

namespace App\Http\Controllers\Api\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\StaffStoreRequest;
use App\Http\Requests\Catalog\StaffUpdateRequest;
use App\Models\Staff;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $salon = $request->attributes->get('currentSalon');

        $q = Staff::where('salon_id', $salon->id);

        if ($request->has('active')) {
            $q->where('is_active', (bool)$request->boolean('active'));
        }

        return response()->json([
            'data' => $q->orderBy('sort_order')->orderBy('name')->get()
        ]);
    }

    public function show(Request $request, $id)
    {
        $salon = $request->attributes->get('currentSalon');

        $row = Staff::where('salon_id', $salon->id)->findOrFail($id);
        return response()->json(['data' => $row]);
    }

    public function store(StaffStoreRequest $request)
    {
        $salon = $request->attributes->get('currentSalon');
        $data = $request->validated();

        $row = Staff::create([
            'salon_id' => $salon->id,
            'name' => $data['name'],
            'title' => $data['title'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'sort_order' => $data['sort_order'] ?? 0,
            'avatar_url' => $data['avatar_url'] ?? null,
        ]);

        return response()->json(['data' => $row], 201);
    }

    public function update(StaffUpdateRequest $request, $id)
    {
        $salon = $request->attributes->get('currentSalon');
        $data = $request->validated();

        $row = Staff::where('salon_id', $salon->id)->findOrFail($id);
        $row->update($data);

        return response()->json(['data' => $row]);
    }

    public function destroy(Request $request, $id)
    {
        $salon = $request->attributes->get('currentSalon');

        $row = Staff::where('salon_id', $salon->id)->findOrFail($id);

        // Safe delete: deactivate
        $row->update(['is_active' => false]);

        return response()->json(['ok' => true]);
    }
}
