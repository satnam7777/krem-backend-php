<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ClientAttachment;
use App\Models\PlatformAuditLog;
use App\Models\Salon;
use App\Models\SalonMember;
use App\Models\User;
use App\Services\Clients\AttachmentService;
use Laravel\Sanctum\Sanctum;

class AttachmentSignedDownloadTest extends TenancyTestCase
{
    public function test_download_returns_signed_url_and_is_audited(): void
    {
        [$tenant, $db] = $this->provisionTenant('Files', 'files.local');

        $this->centralMembership($tenant->id, $owner->id, 'OWNER');

        $this->useTenantDb($db);

        // Replace signed URL generator to avoid Storage::temporaryUrl runtime dependency in tests
        $this->app->bind(AttachmentService::class, function () {
            return new class extends AttachmentService {
                public function signedUrl(\App\Models\ClientAttachment $att): string {
                    return 'https://example.com/signed-download?att='.$att->id.'&sig=test';
                }
            };
        });

        $salon = Salon::create(['name' => 'Files Salon', 'status' => 'active']);

        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@files.local',
            'password' => bcrypt('password'),
        ]);

        SalonMember::create([
            'salon_id' => $salon->id,
            'user_id' => $owner->id,
            'role' => 'OWNER',
            'status' => 'active',
        ]);

        $client = Client::create([
            'salon_id' => $salon->id,
            'first_name' => 'Ana',
            'last_name' => 'Test',
            'phone' => '333-444',
            'email' => 'ana@files.local',
            'status' => 'active',
        ]);

        $att = ClientAttachment::create([
            'client_id' => $client->id,
            'treatment_id' => null,
            'kind' => 'document',
            'disk' => 'public',
            'path' => 'salons/'.$salon->id.'/clients/'.$client->id.'/attachments/test.pdf',
            'mime' => 'application/pdf',
            'size' => 1234,
            'original_name' => 'test.pdf',
            'sha256' => str_repeat('a', 64),
            'uploaded_by_user_id' => $owner->id,
        ]);

        Sanctum::actingAs($owner, ['*']);

        $res = $this->withHeaders([
            'Host' => 'files.local',
            'X-Salon-Id' => (string)$salon->id,
        ])->getJson('/api/attachments/'.$att->id.'/download');

        $res->assertOk()->assertJsonStructure(['url','expires_in_minutes']);
        $this->assertStringContainsString('example.com', $res->json('url'));

        $audit = PlatformAuditLog::where('action','client_attachment.download')->latest()->first();
        $this->assertNotNull($audit);
    }
}
