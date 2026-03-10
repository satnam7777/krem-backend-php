<?php

namespace App\Tenancy;

use App\Models\Tenant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PDO;
use RuntimeException;

class TenantDatabaseManager
{
    public function makeSlug(string $name): string
    {
        $slug = Str::slug($name);
        return $slug !== '' ? $slug : Str::lower(Str::random(8));
    }

    public function makeDbName(string $slug): string
    {
        $prefix = config('krema_tenancy.tenant_db_prefix', 'krema_tenant_');
        // Postgres DB name must be <= 63 chars. Keep it tight.
        $candidate = $prefix . Str::replace('-', '_', Str::lower($slug));
        return substr($candidate, 0, 63);
    }

    /**
     * Create the tenant database (PostgreSQL).
     * Requires the DB user to have CREATE DATABASE privileges,
     * or enable admin connection via config/krema_tenancy.php.
     */
    public function createDatabase(string $dbName): void
    {
        $this->validateDbIdentifier($dbName);

        if ($this->databaseExists($dbName)) {
            throw new RuntimeException("Tenant DB already exists: {$dbName}");
        }

        if (config('krema_tenancy.use_admin_connection', false)) {
            $pdo = $this->adminPdo();
            $pdo->exec('CREATE DATABASE ' . $this->quoteIdent($dbName) . ' WITH ENCODING = \'UTF8\'');
            return;
        }

        // Use Laravel central connection
        DB::statement('CREATE DATABASE ' . $this->quoteIdent($dbName) . ' WITH ENCODING = \'UTF8\'');
    }

    public function databaseExists(string $dbName): bool
    {
        $this->validateDbIdentifier($dbName);

        $sql = "SELECT 1 FROM pg_database WHERE datname = ?";
        if (config('krema_tenancy.use_admin_connection', false)) {
            $pdo = $this->adminPdo();
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$dbName]);
            return (bool) $stmt->fetchColumn();
        }

        return (bool) DB::selectOne($sql, [$dbName]);
    }

    /**
     * Switch the 'tenant' connection to the given tenant DB and reconnect.
     */
    public function switchToTenantDb(string $dbName): void
    {
        $this->validateDbIdentifier($dbName);

        Config::set('database.connections.tenant.database', $dbName);

        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    /**
     * Run migrations against the tenant DB.
     */
    public function migrateTenant(string $dbName, bool $fresh = false): void
    {
        $this->switchToTenantDb($dbName);

        $path = config('krema_tenancy.tenant_migration_path', 'database/migrations');

        if ($fresh) {
            // NOTE: this will wipe the tenant DB schema.
            \Artisan::call('migrate:fresh', [
                '--database' => 'tenant',
                '--path' => $path,
                '--force' => true,
            ]);
            return;
        }

        \Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => $path,
            '--force' => true,
        ]);
    }

    private function adminPdo(): PDO
    {
        $cfg = config('krema_tenancy.admin');
        $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', $cfg['host'], $cfg['port'], $cfg['database']);
        return new PDO($dsn, $cfg['username'], $cfg['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
    }

    private function validateDbIdentifier(string $dbName): void
    {
        // strict: letters numbers underscore only
        if (!preg_match('/^[a-zA-Z0-9_]{1,63}$/', $dbName)) {
            throw new RuntimeException('Invalid database identifier.');
        }
    }

    private function quoteIdent(string $ident): string
    {
        // safe quote for postgres identifiers
        return '"' . str_replace('"', '""', $ident) . '"';
    }
}
