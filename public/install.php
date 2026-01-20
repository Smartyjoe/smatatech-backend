<?php
/**
 * Smatatech API - Web Installer
 * 
 * This script allows you to run Laravel setup commands via browser
 * when you don't have SSH access on cPanel.
 * 
 * SECURITY: Delete this file immediately after installation!
 */

// Security token - CHANGE THIS before uploading!
$SECURITY_TOKEN = 'smatatech2024install';

// Check security token
$providedToken = $_GET['token'] ?? '';
if ($providedToken !== $SECURITY_TOKEN) {
    http_response_code(403);
    die('
    <!DOCTYPE html>
    <html>
    <head><title>Access Denied</title></head>
    <body style="font-family: Arial, sans-serif; padding: 50px; text-align: center;">
        <h1 style="color: #e74c3c;">⛔ Access Denied</h1>
        <p>Invalid or missing security token.</p>
        <p>Usage: <code>install.php?token=YOUR_TOKEN&action=ACTION</code></p>
    </body>
    </html>
    ');
}

// Load Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$action = $_GET['action'] ?? 'status';
$output = '';
$success = true;

try {
    switch ($action) {
        case 'status':
            // Check system status
            $output = "<h2>📊 System Status</h2>";
            
            // Check database connection
            try {
                DB::connection()->getPdo();
                $output .= "<p>✅ Database Connection: <strong style='color:green'>Connected</strong></p>";
                $output .= "<p>📁 Database: " . DB::connection()->getDatabaseName() . "</p>";
            } catch (Exception $e) {
                $output .= "<p>❌ Database Connection: <strong style='color:red'>Failed</strong></p>";
                $output .= "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
                $success = false;
            }
            
            // Check if migrations table exists
            if (Schema::hasTable('migrations')) {
                $output .= "<p>✅ Migrations Table: <strong style='color:green'>Exists</strong></p>";
                $migrationCount = DB::table('migrations')->count();
                $output .= "<p>📊 Migrations Run: {$migrationCount}</p>";
            } else {
                $output .= "<p>⚠️ Migrations Table: <strong style='color:orange'>Not Found</strong> (Run migrations first)</p>";
            }
            
            // Check important tables
            $tables = ['users', 'admins', 'posts', 'services', 'contacts'];
            $output .= "<h3>📋 Tables Check:</h3><ul>";
            foreach ($tables as $table) {
                if (Schema::hasTable($table)) {
                    $count = DB::table($table)->count();
                    $output .= "<li>✅ {$table}: {$count} records</li>";
                } else {
                    $output .= "<li>❌ {$table}: Not found</li>";
                }
            }
            $output .= "</ul>";
            
            // Check storage permissions
            $output .= "<h3>📂 Storage Check:</h3><ul>";
            $storagePaths = [
                'storage/logs',
                'storage/framework/cache',
                'storage/framework/sessions',
                'storage/framework/views',
                'storage/app/public',
            ];
            foreach ($storagePaths as $path) {
                $fullPath = base_path($path);
                if (is_writable($fullPath)) {
                    $output .= "<li>✅ {$path}: Writable</li>";
                } else {
                    $output .= "<li>❌ {$path}: Not writable</li>";
                }
            }
            $output .= "</ul>";
            
            // Show available actions
            $output .= "<h2>🔧 Available Actions</h2>";
            $output .= "<p>Click links below to run commands:</p>";
            $output .= "<ul>";
            $output .= "<li><a href='?token={$SECURITY_TOKEN}&action=migrate'>▶️ Run Migrations</a></li>";
            $output .= "<li><a href='?token={$SECURITY_TOKEN}&action=seed'>▶️ Run Seeders</a></li>";
            $output .= "<li><a href='?token={$SECURITY_TOKEN}&action=migrate_seed'>▶️ Run Migrations + Seeders</a></li>";
            $output .= "<li><a href='?token={$SECURITY_TOKEN}&action=storage_link'>▶️ Create Storage Link</a></li>";
            $output .= "<li><a href='?token={$SECURITY_TOKEN}&action=clear_cache'>▶️ Clear All Caches</a></li>";
            $output .= "<li><a href='?token={$SECURITY_TOKEN}&action=optimize'>▶️ Optimize for Production</a></li>";
            $output .= "<li><a href='?token={$SECURITY_TOKEN}&action=key_generate'>▶️ Generate App Key</a></li>";
            $output .= "</ul>";
            break;
            
        case 'migrate':
            $output = "<h2>🗄️ Running Migrations...</h2>";
            Artisan::call('migrate', ['--force' => true]);
            $output .= "<pre>" . Artisan::output() . "</pre>";
            $output .= "<p style='color:green;'>✅ Migrations completed!</p>";
            break;
            
        case 'seed':
            $output = "<h2>🌱 Running Seeders...</h2>";
            Artisan::call('db:seed', ['--force' => true]);
            $output .= "<pre>" . Artisan::output() . "</pre>";
            $output .= "<p style='color:green;'>✅ Seeding completed!</p>";
            $output .= "<p><strong>Default Admin:</strong> admin@smatatech.com / password</p>";
            break;
            
        case 'migrate_seed':
            $output = "<h2>🗄️ Running Migrations...</h2>";
            Artisan::call('migrate', ['--force' => true]);
            $output .= "<pre>" . Artisan::output() . "</pre>";
            
            $output .= "<h2>🌱 Running Seeders...</h2>";
            Artisan::call('db:seed', ['--force' => true]);
            $output .= "<pre>" . Artisan::output() . "</pre>";
            
            $output .= "<p style='color:green;'>✅ Migrations and Seeding completed!</p>";
            $output .= "<p><strong>Default Admin:</strong> admin@smatatech.com / password</p>";
            break;
            
        case 'storage_link':
            $output = "<h2>🔗 Creating Storage Link...</h2>";
            
            $target = base_path('storage/app/public');
            $link = public_path('storage');
            
            if (file_exists($link)) {
                $output .= "<p>⚠️ Storage link already exists.</p>";
            } else {
                if (symlink($target, $link)) {
                    $output .= "<p style='color:green;'>✅ Storage link created successfully!</p>";
                } else {
                    // Fallback for Windows or if symlink fails
                    $output .= "<p style='color:orange;'>⚠️ Symlink failed. This is common on shared hosting.</p>";
                    $output .= "<p>Manual solution: Copy contents of <code>storage/app/public</code> to <code>public/storage</code></p>";
                }
            }
            break;
            
        case 'clear_cache':
            $output = "<h2>🧹 Clearing Caches...</h2>";
            
            Artisan::call('config:clear');
            $output .= "<p>✅ Config cache cleared</p>";
            
            Artisan::call('cache:clear');
            $output .= "<p>✅ Application cache cleared</p>";
            
            Artisan::call('route:clear');
            $output .= "<p>✅ Route cache cleared</p>";
            
            Artisan::call('view:clear');
            $output .= "<p>✅ View cache cleared</p>";
            
            $output .= "<p style='color:green;'>✅ All caches cleared!</p>";
            break;
            
        case 'optimize':
            $output = "<h2>⚡ Optimizing for Production...</h2>";
            
            Artisan::call('config:cache');
            $output .= "<p>✅ Config cached</p>";
            
            Artisan::call('route:cache');
            $output .= "<p>✅ Routes cached</p>";
            
            Artisan::call('view:cache');
            $output .= "<p>✅ Views cached</p>";
            
            $output .= "<p style='color:green;'>✅ Optimization completed!</p>";
            break;
            
        case 'key_generate':
            $output = "<h2>🔑 Generating Application Key...</h2>";
            Artisan::call('key:generate', ['--force' => true]);
            $output .= "<pre>" . Artisan::output() . "</pre>";
            $output .= "<p style='color:green;'>✅ Application key generated!</p>";
            break;
            
        default:
            $output = "<p style='color:red;'>Unknown action: {$action}</p>";
            $success = false;
    }
} catch (Exception $e) {
    $output = "<h2 style='color:red;'>❌ Error</h2>";
    $output .= "<p>" . $e->getMessage() . "</p>";
    $output .= "<pre>" . $e->getTraceAsString() . "</pre>";
    $success = false;
}

// Output HTML
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smatatech API - Installer</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            line-height: 1.6;
            padding: 20px;
            max-width: 900px;
            margin: 0 auto;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 10px; }
        h2 { color: #34495e; margin-top: 30px; }
        pre {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            font-size: 13px;
        }
        a { color: #3498db; text-decoration: none; }
        a:hover { text-decoration: underline; }
        ul { padding-left: 20px; }
        li { margin: 8px 0; }
        code {
            background: #ecf0f1;
            padding: 2px 8px;
            border-radius: 3px;
            font-family: monospace;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #3498db;
            color: white;
            border-radius: 5px;
        }
        .back-link:hover { background: #2980b9; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Smatatech API Installer</h1>
        
        <div class="warning">
            <strong>⚠️ Security Warning:</strong> Delete this file (<code>install.php</code>) immediately after completing installation!
        </div>
        
        <?php echo $output; ?>
        
        <?php if ($action !== 'status'): ?>
            <a href="?token=<?php echo $SECURITY_TOKEN; ?>&action=status" class="back-link">← Back to Status</a>
        <?php endif; ?>
        
        <hr style="margin-top: 40px;">
        <p style="color: #7f8c8d; font-size: 13px;">
            <strong>After installation:</strong><br>
            1. Test API: <code>POST /api/admin/login</code> with admin@smatatech.com / password<br>
            2. Delete this install.php file!<br>
            3. Update .env with your production values
        </p>
    </div>
</body>
</html>
