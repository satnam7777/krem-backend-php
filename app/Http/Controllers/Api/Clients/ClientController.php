<?php

namespace App\Http\Controllers\Api\Clients;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clients\ClientStoreRequest;
use App\Http\Requests\Clients\ClientUpdateRequest;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $salon = $request->attributes->get('currentSalon');

        $q = Client::query()
            ->where('salon_id', $salon->id)
            ->whereNull('deleted_at');

        if ($search = $request->query('q')) {
            $q->where(function($qq) use ($search){
                $qq->where('first_name','ilike', '%'.$search.'%')
                   ->orWhere('last_name','ilike', '%'.$search.'%')
                   ->orWhere('phone','ilike', '%'.$search.'%')
                   ->orWhere('email','ilike', '%'.$search.'%');
            });
        }

        $per = min(100, max(1, (int) $request->query('per_page', 20)));

        return response()->json($q->orderByDesc('id')->paginate($per));
    }

    public function store(ClientStoreRequest $request)
    {
        $salon = $request->attributes->get('currentSalon');

        $client = Client::create(array_merge(
            $request->validated(),
            ['salon_id' => $salon->id]
        ));

        return response()->json($client, 201);
    }

    public function show(Request $request, Client $client)
    {
        $this->assertSameSalon($request, $client);

        return response()->json($client);
    }

    public function update(ClientUpdateRequest $request, Client $client)
    {
        $this->assertSameSalon($request, $client);

        $client->update($request->validated());

        return response()->json($client);
    }

    public function archive(Request $request, Client $client)
    {
        $this->assertSameSalon($request, $client);

        $client->status = 'archived';
        $client->save();

        return response()->json(['ok'=>true]);
    }

    private function assertSameSalon(Request $request, Client $client): void
    {
        $salon = $request->attributes->get('currentSalon');
        abort_unless((int)$client->salon_id === (int)$salon->id, 404, 'Not found');
    }
}
