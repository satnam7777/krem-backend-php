<?php

namespace App\Tenancy;

use App\Models\Tenant;

class TenantContext
{
    private ?Tenant $tenant = null;

    public function setTenant(Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function tenant(): ?Tenant
    {
        return $this->tenant;
    }

    public function tenantId(): ?int
    {
        return $this->tenant?->id;
    }

    public function dbName(): ?string
    {
        return $this->tenant?->db_name;
    }

    public function isResolved(): bool
    {
        return $this->tenant !== null;
    }
}
