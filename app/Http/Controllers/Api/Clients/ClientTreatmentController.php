<?php

namespace App\Http\Controllers\Api\Clients;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clients\TreatmentStoreRequest;
use App\Models\Client;
use App\Models\ClientTreatment;
use Illuminate\Http\Request;

class ClientTreatmentController extends Controller
{
    public function index(Request $request, Client $client)
    {
        $this->assertSameSalon($request, $client);

        return response()->json(
            ClientTreatment::where('client_id',$client->id)->orderByDesc('performed_at')->paginate(20)
        );
    }

    public function store(TreatmentStoreRequest $request, Client $client)
    {
        $this->assertSameSalon($request, $client);

        $t = ClientTreatment::create(array_merge(
            $request->validated(),
            [
                'client_id' => $client->id,
                'performed_by_user_id' => $request->user()?->id,
            ]
        ));

        return response()->json($t, 201);
    }

    private function assertSameSalon(Request $request, Client $client): void
    {
        $salon = $request->attributes->get('currentSalon');
        abort_unless((int)$client->salon_id === (int)$salon->id, 404, 'Not found');
    }
}
