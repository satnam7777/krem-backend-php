<?php

namespace App\Http\Controllers\Api\Clients;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clients\MedicalUpsertRequest;
use App\Models\Client;
use App\Models\MedicalProfile;
use App\Services\Platform\Audit;
use Illuminate\Http\Request;

class ClientMedicalController extends Controller
{
    public function show(Request $request, Client $client, Audit $audit)
    {
        $this->assertSameSalon($request, $client);
        $this->authorizeMedical($request);

        $audit->log($request, null, 'client_medical.view', [
            'client_id' => $client->id,
            'salon_id' => $client->salon_id,
        ]);

        $profile = MedicalProfile::firstOrCreate(['client_id' => $client->id]);

        return response()->json($profile);
    }

    public function upsert(MedicalUpsertRequest $request, Client $client, Audit $audit)
    {
        $this->assertSameSalon($request, $client);
        $this->authorizeMedical($request);

        $profile = MedicalProfile::firstOrCreate(['client_id' => $client->id]);
        $profile->fill($request->validated());
        $profile->save();

        $audit->log($request, null, 'client_medical.update', [
            'client_id' => $client->id,
            'salon_id' => $client->salon_id,
        ]);

        return response()->json($profile);
    }

    private function authorizeMedical(Request $request): void
    {
        $role = $request->attributes->get('currentRole');
        // Reception is forbidden by default
        abort_if($role === 'RECEPTION', 403, 'Forbidden');
    }

    private function assertSameSalon(Request $request, Client $client): void
    {
        $salon = $request->attributes->get('currentSalon');
        abort_unless((int)$client->salon_id === (int)$salon->id, 404, 'Not found');
    }
}
