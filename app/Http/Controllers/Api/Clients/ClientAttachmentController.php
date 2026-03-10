<?php

namespace App\Http\Controllers\Api\Clients;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientAttachment;
use App\Services\Clients\AttachmentService;
use App\Services\Platform\Audit;
use Illuminate\Http\Request;

class ClientAttachmentController extends Controller
{
    public function upload(Request $request, Client $client, AttachmentService $svc, Audit $audit)
    {
        $this->assertSameSalon($request, $client);

        $request->validate([
            'file' => ['required','file','max:'.((int)env('KREMA_UPLOAD_MAX_MB',20))*1024],
            'kind' => ['nullable','string','max:40'],
            'treatment_id' => ['nullable','integer'],
        ]);

        $role = $request->attributes->get('currentRole');
        abort_if($role === 'RECEPTION', 403, 'Forbidden');

        $att = $svc->store(
            $client,
            $request->file('file'),
            $request->input('kind','document'),
            $request->input('treatment_id'),
            $request->user()?->id
        );

        $audit->log($request, null, 'client_attachment.upload', [
            'client_id' => $client->id,
            'attachment_id' => $att->id,
        ]);

        return response()->json($att, 201);
    }

    public function download(Request $request, ClientAttachment $attachment, AttachmentService $svc, Audit $audit)
    {
        // Ensure attachment belongs to current salon
        $client = Client::findOrFail($attachment->client_id);
        $this->assertSameSalon($request, $client);

        $role = $request->attributes->get('currentRole');
        abort_if($role === 'RECEPTION', 403, 'Forbidden');

        $audit->log($request, null, 'client_attachment.download', [
            'client_id' => $client->id,
            'attachment_id' => $attachment->id,
        ]);

        return response()->json([
            'url' => $svc->signedUrl($attachment),
            'expires_in_minutes' => (int) env('KREMA_SIGNED_URL_MINUTES', 10),
        ]);
    }

    private function assertSameSalon(Request $request, Client $client): void
    {
        $salon = $request->attributes->get('currentSalon');
        abort_unless((int)$client->salon_id === (int)$salon->id, 404, 'Not found');
    }
}
