<?php
/**
 * Debug Script - Check database tables and seed data
 * Access: https://your-domain.com/debug.php?token=setup123
 * DELETE THIS FILE AFTER USE!
 */

$setupToken = 'setup123';
if (!isset($_GET['token']) || $_GET['token'] !== $setupToken) {
    http_response_code(403);
    header('Content-Type: application/json');
    die(json_encode(['error' => 'Invalid token. Use ?token=setup123']));
}

header('Content-Type: application/json');

// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$results = [];

try {
    // Check database connection
    DB::connection()->getPdo();
    $results['database_connection'] = 'OK';
    
    // Check all tables exist
    $tables = [
        'users', 'admins', 'categories', 'posts', 'services', 
        'case_studies', 'testimonials', 'brands', 'contacts',
        'activity_logs', 'site_settings', 'chatbot_configs',
        'roles', 'permissions', 'model_has_roles'
    ];
    
    $tableStatus = [];
    foreach ($tables as $table) {
        $exists = Schema::hasTable($table);
        $count = $exists ? DB::table($table)->count() : 0;
        $tableStatus[$table] = [
            'exists' => $exists,
            'count' => $count
        ];
    }
    $results['tables'] = $tableStatus;
    
    // Check contacts table structure
    if (Schema::hasTable('contacts')) {
        $columns = Schema::getColumnListing('contacts');
        $results['contacts_columns'] = $columns;
    }
    
    // Check activity_logs table structure
    if (Schema::hasTable('activity_logs')) {
        $columns = Schema::getColumnListing('activity_logs');
        $results['activity_logs_columns'] = $columns;
    }
    
    // Try to insert test contact
    $results['contact_test'] = 'Not run';
    if (isset($_GET['test_contact'])) {
        try {
            $contact = \App\Models\Contact::create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'message' => 'This is a test message',
            ]);
            $results['contact_test'] = 'SUCCESS - Created ID: ' . $contact->id;
            // Delete test record
            $contact->delete();
            $results['contact_test'] .= ' (deleted)';
        } catch (Exception $e) {
            $results['contact_test'] = 'FAILED: ' . $e->getMessage();
        }
    }
    
    // Seed data if requested
    if (isset($_GET['seed'])) {
        try {
            Artisan::call('db:seed', ['--class' => 'SampleDataSeeder', '--force' => true]);
            $results['seeding'] = 'SUCCESS';
            $results['seeder_output'] = Artisan::output();
        } catch (Exception $e) {
            $results['seeding'] = 'FAILED: ' . $e->getMessage();
        }
    }
    
    // Show sample data from services
    if (Schema::hasTable('services')) {
        $services = DB::table('services')->select('id', 'title', 'slug', 'status')->get();
        $results['services_data'] = $services;
    }
    
    // Instructions
    $results['actions'] = [
        'seed_data' => 'Add &seed=1 to URL to run SampleDataSeeder',
        'test_contact' => 'Add &test_contact=1 to test contact creation',
    ];
    
    echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], JSON_PRETTY_PRINT);
}
