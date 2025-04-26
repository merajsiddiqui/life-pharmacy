<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->setupTestDatabase();
    }

    /**
     * Setup the test database.
     */
    protected function setupTestDatabase(): void
    {
        // For in-memory SQLite, we don't need to manage the file
        if (config('database.connections.sqlite.database') === ':memory:') {
            $this->runMigrations();
            return;
        }

        $databasePath = database_path('database.sqlite');

        // Delete the database file if it exists
        if (File::exists($databasePath)) {
            File::delete($databasePath);
        }

        // Create a new database file
        File::put($databasePath, '');

        // Run migrations
        $this->runMigrations();
    }

    /**
     * Run database migrations.
     */
    protected function runMigrations(): void
    {
        // Run migrations
        Artisan::call('migrate:fresh', [
            '--force' => true,
            '--path' => 'database/migrations',
            '--database' => 'sqlite'
        ]);

        // Run seeders if needed
        // Artisan::call('db:seed', ['--force' => true]);
    }

    /**
     * Clean up the test environment.
     */
    protected function tearDown(): void
    {
        // Close any open database connections
        DB::disconnect();

        parent::tearDown();
    }

    /**
     * Begin a database transaction.
     */
    protected function beginDatabaseTransaction(): void
    {
        $this->beforeApplicationDestroyed(function () {
            DB::rollBack();
        });

        DB::beginTransaction();
    }
}
