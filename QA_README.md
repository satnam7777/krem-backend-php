# QA P1 Pack (recommended baseline)

## What you get
P0:
- Tenant isolation regression (host routing + data isolation)
- Medical access regression (RECEPTION blocked)
- Encryption-at-rest assertion for medical notes
- Central audit assertion for sensitive reads

P1:
- Double-booking regression (409 on overlap)
- Signed download endpoint regression (url + audit) with test-safe URL stub
- API rate limiting regression (429 after threshold)

## Migration layout
- Central: `database/migrations/central`
- Tenant: `database/migrations/tenant`

Run central:
- php artisan migrate --path=database/migrations/central

Run tenant:
- php artisan tenant:migrate


P2:
- Reschedule conflict regression (patch /appointments/{id} -> 409) (skips if endpoint missing)
- Status transitions regression (patch /appointments/{id}/status) (skips if endpoint missing)
- Notifications queue/health smoke (skips if endpoint missing)


Central identity enabled:
- Users + Sanctum tokens live in CENTRAL DB (pgsql)
- Tenant DB stores business data only; references users by user_id
- ResolveTenant no longer flips default connection


FIX: Removed tenant-side users migration (central identity owns users). Also removed active_salon_id persistence; client must send X-Salon-Id.


Membership enforcement:
- /api/me and /api/me/salons added
- requireTenantMembership enforced in module route files by injecting middleware into auth:sanctum groups


Membership management:
- GET /api/tenant/members (OWNER/superadmin)
- PUT /api/tenant/members {email,role,status}
- DELETE /api/tenant/members/{userId}
- Audited in platform_audit_logs
