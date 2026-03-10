<?php

namespace App\Services\Clients;

use App\Models\Client;
use App\Models\ClientAttachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachmentService
{
    public function store(Client $client, UploadedFile $file, string $kind, ?int $treatmentId, ?int $uploadedByUserId): ClientAttachment
    {
        $disk = config('filesystems.default', env('KREMA_UPLOAD_DISK', 'public'));
        $ext = $file->getClientOriginalExtension();
        $name = Str::uuid()->toString() . ($ext ? '.'.$ext : '');
        $path = 'salons/'.$client->salon_id.'/clients/'.$client->id.'/attachments/'.$name;

        $stream = fopen($file->getRealPath(), 'r');
        Storage::disk($disk)->put($path, $stream, ['visibility' => 'private']);
        if (is_resource($stream)) fclose($stream);

        $sha256 = hash_file('sha256', $file->getRealPath());

        return ClientAttachment::create([
            'client_id' => $client->id,
            'treatment_id' => $treatmentId,
            'kind' => $kind,
            'disk' => $disk,
            'path' => $path,
            'mime' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'original_name' => $file->getClientOriginalName(),
            'sha256' => $sha256,
            'uploaded_by_user_id' => $uploadedByUserId,
        ]);
    }

    public function signedUrl(ClientAttachment $att): string
    {
        $minutes = (int) env('KREMA_SIGNED_URL_MINUTES', 10);
        return Storage::disk($att->disk)->temporaryUrl($att->path, now()->addMinutes($minutes));
    }
}
