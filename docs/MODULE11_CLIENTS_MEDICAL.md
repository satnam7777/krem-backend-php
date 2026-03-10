# Module 11 — Clients & Medical

## Why it exists
This module gives the salon a **real client record** with:
- consents (GDPR/medical/marketing)
- encrypted medical profile
- treatment history
- attachments with signed download URLs

## Security defaults
- All endpoints require: `auth:sanctum` + `resolveSalon` (and resolveTenant globally in routes/api.php)
- Role: `RECEPTION` is blocked from medical + attachments by default.

## Tenancy
- DB-per-tenant: all tables in `database/migrations/tenant`.
- `TENANT_MIGRATION_PATH=database/migrations/tenant`

## API
- GET /clients?q=...
- POST /clients
- GET /clients/{id}
- PATCH /clients/{id}
- DELETE /clients/{id} (archives)
- POST /clients/{id}/consents
- GET /clients/{id}/medical
- PUT /clients/{id}/medical
- GET /clients/{id}/treatments
- POST /clients/{id}/treatments
- POST /clients/{id}/attachments
- GET /attachments/{id}/download
