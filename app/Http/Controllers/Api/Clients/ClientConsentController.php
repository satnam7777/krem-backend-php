<?php

namespace App\Http\Controllers\Api\Clients;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clients\ConsentStoreRequest;
use App\Models\Client;
use App\Models\ClientConsent;
use App\Services\Clients\ConsentHasher;
use Illuminate\Http\Request;

class ClientConsentController extends Controller
{
    public function store(ConsentStoreRequest $request, Client $client, ConsentHasher $hasher)
    {
        $this->assertSameSalon($request, $client);

        $data = $request->validated();

        $textHash = $hasher->sha256($data['consent_text']);
        $acceptedAt = isset($data['accepted_at']) ? now()->parse($data['accepted_at']) : now();

        $consent = ClientConsent::create([
            'client_id' => $client->id,
            'type' => $data['type'],
            'version' => $data['version'],
            'text_hash' => $textHash,
            'accepted_at' => $acceptedAt,
            'source' => $data['source'] ?? 'in_person',
            'recorded_by_user_id' => $request->user()?->id,
            'meta' => $data['meta'] ?? null,
        ]);

        return response()->json($consent, 201);
    }

    private function assertSameSalon(Request $request, Client $client): void
    {
        $salon = $request->attributes->get('currentSalon');
        abort_unless((int)$client->salon_id === (int)$salon->id, 404, 'Not found');
    }
}
