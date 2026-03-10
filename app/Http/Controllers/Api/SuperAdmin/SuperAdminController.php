<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Salon;
use App\Models\User;
use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    public function stats()
    {
        return response()->json([
            'data' => [
                'salons' => Salon::count(),
                'users' => User::count(),
            ]
        ]);
    }

    public function salons(Request $request)
    {
        $q = Salon::query();
        if ($request->filled('q')) {
            $term = $request->query('q');
            $q->where('name','like',"%{$term}%");
        }
        return response()->json(['data'=>$q->orderByDesc('id')->paginate(50)]);
    }

    public function users(Request $request)
    {
        $q = User::query();
        if ($request->filled('q')) {
            $term = $request->query('q');
            $q->where('email','like',"%{$term}%");
        }
        return response()->json(['data'=>$q->orderByDesc('id')->paginate(50)]);
    }

    public function suspendSalon(Request $request, $id)
    {
        $salon = Salon::findOrFail($id);
        $salon->update(['is_suspended'=>true]);
        return response()->json(['ok'=>true]);
    }

    public function unsuspendSalon(Request $request, $id)
    {
        $salon = Salon::findOrFail($id);
        $salon->update(['is_suspended'=>false]);
        return response()->json(['ok'=>true]);
    }
}
