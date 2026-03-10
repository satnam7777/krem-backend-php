<?php

return [
    // When true, TenantDatabaseManager will use a dedicated admin PDO connection for CREATE DATABASE.
    'use_admin_connection' => env('TENANCY_ADMIN_DB', false),

    'admin' => [
        'host' => env('TENANCY_ADMIN_HOST', env('DB_HOST', '127.0.0.1')),
        'port' => env('TENANCY_ADMIN_PORT', env('DB_PORT', '5432')),
        'database' => env('TENANCY_ADMIN_DATABASE', 'postgres'),
        'username' => env('TENANCY_ADMIN_USERNAME', env('DB_USERNAME')),
        'password' => env('TENANCY_ADMIN_PASSWORD', env('DB_PASSWORD')),
    ],

    // Tenant DB naming
    'tenant_db_prefix' => env('TENANT_DB_PREFIX', 'krema_tenant_'),

    // Which migration path should run against tenant DBs (defaults to normal app migrations).
    // If you later split migrations, you can point this to e.g. database/migrations/tenant
    'tenant_migration_path' => env('TENANT_MIGRATION_PATH', 'database/migrations/tenant'),
];
