<?php
/**
 * Smatatech API - Web Installer & Maintenance Tool
 * 
 * This script allows you to run Laravel setup commands via browser
 * when you don't have SSH access on cPanel shared hosting.
 * 
 * SECURITY WARNING: 
 * 1. Change the INSTALL_TOKEN in your .env file before uploading!
 * 2. Delete this file immediately after installation is complete!
 * 3. Never commit your actual token to version control!
 */

// Prevent direct execution without proper setup
if (!file_exists(__DIR__.'/../vendor/autoload.php')) {
    die('
    <!DOCTYPE html>
    <html>
    <head><title>Setup Required</title></head>
    <body style="font-family: Arial, sans-serif; padding: 50px; text-align: center;">
        <h1 style="color: #e74c3c;">⚠️ Vendor Directory Missing</h1>
        <p>Please upload the complete Laravel project including the <code>vendor</code> folder.</p>
        <p>If you ran <code>composer install</code> locally, make sure to upload the entire vendor directory.</p>
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
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

// Get security token from .env or use default (MUST BE CHANGED!)
$SECURITY_TOKEN = env('INSTALL_TOKEN', 'change_this_to_random_string_2024');

// Check if using default token (warn user)
$usingDefaultToken = ($SECURITY_TOKEN === 'change_this_to_random_string_2024');

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
        <p style="color: #7f8c8d; font-size: 12px;">Set INSTALL_TOKEN in your .env file</p>
    </body>
    </html>
    ');
}

$action = $_GET['action'] ?? 'status';
$output = '';
$success = true;

try {
    switch ($action) {
        case 'status':
            $output = "<h2>📊 System Status</h2>";
            
            // Security warning
            if ($usingDefaultToken) {
                $output .= "<div class='warning' style='background:#ffebee;border-color:#f44336;'>";
                $output .= "<strong>🚨 SECURITY WARNING:</strong> You are using the default INSTALL_TOKEN!<br>";
                $output .= "Please change <code>INSTALL_TOKEN</code> in your .env file to a random string.";
                $output .= "</div>";
            }
            
            // Environment info
            $output .= "<h3>🔧 Environment</h3>";
            $output .= "<ul>";
            $output .= "<li>App Environment: <strong>" . app()->environment() . "</strong></li>";
            $output .= "<li>Debug Mode: <strong>" . (config('app.debug') ? 'ON ⚠️' : 'OFF ✅') . "</strong></li>";
            $output .= "<li>PHP Version: <strong>" . PHP_VERSION . "</strong></li>";
            $output .= "<li>Laravel Version: <strong>" . app()->version() . "</strong></li>";
            $output .= "<li>Cache Driver: <strong>" . config('cache.default') . "</strong></li>";
            $output .= "<li>Session Driver: <strong>" . config('session.driver') . "</strong></li>";
            $output .= "<li>Queue Driver: <strong>" . config('queue.default') . "</strong></li>";
            $output .= "</ul>";
            
            // Check database connection
            $output .= "<h3>🗄️ Database</h3>";
            try {
                DB::connection()->getPdo();
                $output .= "<p>✅ Database Connection: <strong style='color:green'>Connected</strong></p>";
                $output .= "<p>📁 Database: " . DB::connection()->getDatabaseName() . "</p>";
                
                // Check if migrations table exists
                if (Schema::hasTable('migrations')) {
                    $migrationCount = DB::table('migrations')->count();
                    $output .= "<p>✅ Migrations Table: <strong style='color:green'>Exists</strong> ({$migrationCount} migrations run)</p>";
                } else {
                    $output .= "<p>⚠️ Migrations Table: <strong style='color:orange'>Not Found</strong> (Run migrations)</p>";
                }
                
                // Check important tables
                $tables = ['users', 'admins', 'personal_access_tokens', 'posts', 'categories', 'services', 'contacts', 'site_settings'];
                $output .= "<h4>📋 Tables:</h4><ul>";
                foreach ($tables as $table) {
                    if (Schema::hasTable($table)) {
                        $count = DB::table($table)->count();
                        $output .= "<li>✅ {$table}: {$count} records</li>";
                    } else {
                        $output .= "<li>❌ {$table}: Not found</li>";
                    }
                }
                $output .= "</ul>";
            } catch (Exception $e) {
                $output .= "<p>❌ Database Connection: <strong style='color:red'>Failed</strong></p>";
                $output .= "<p style='color:red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
                $output .= "<p>Please check your .env database settings.</p>";
                $success = false;
            }
            
            // Check storage permissions
            $output .= "<h3>📂 Storage & Permissions</h3><ul>";
            $storagePaths = [
                'storage/logs' => storage_path('logs'),
                'storage/framework/cache/data' => storage_path('framework/cache/data'),
                'storage/framework/sessions' => storage_path('framework/sessions'),
                'storage/framework/views' => storage_path('framework/views'),
                'storage/app/public' => storage_path('app/public'),
                'bootstrap/cache' => base_path('bootstrap/cache'),
            ];
            
            foreach ($storagePaths as $label => $path) {
                if (!file_exists($path)) {
                    @mkdir($path, 0755, true);
                }
                
                if (is_writable($path)) {
                    $output .= "<li>✅ {$label}: Writable</li>";
                } else {
                    $output .= "<li>❌ {$label}: <strong style='color:red'>Not writable</strong> (Set to 755 or 775)</li>";
                }
            }
            $output .= "</ul>";
            
            // Check storage link
            $storageLink = public_path('storage');
            $output .= "<h4>🔗 Storage Link:</h4>";
            if (file_exists($storageLink)) {
                if (is_link($storageLink)) {
                    $output .= "<p>✅ Symlink exists and points to: " . readlink($storageLink) . "</p>";
                } else {
                    $output .= "<p>✅ Storage folder exists (manual copy)</p>";
                }
            } else {
                $output .= "<p>⚠️ Storage link not created. Run 'Create Storage Link' action.</p>";
            }
            
            // Check APP_KEY
            $output .= "<h3>🔐 Security</h3>";
            if (config('app.key')) {
                $output .= "<p>✅ APP_KEY: Set</p>";
            } else {
                $output .= "<p>❌ APP_KEY: <strong style='color:red'>Not set!</strong> Run 'Generate App Key'</p>";
            }
            
            // Show available actions
            $output .= "<h2>🔧 Available Actions</h2>";
            $output .= "<div class='actions-grid'>";
            $output .= "<a href='?token={$SECURITY_TOKEN}&action=migrate' class='action-btn'>🗄️ Run Migrations</a>";
            $output .= "<a href='?token={$SECURITY_TOKEN}&action=seed' class='action-btn'>🌱 Run Seeders</a>";
            $output .= "<a href='?token={$SECURITY_TOKEN}&action=migrate_fresh_seed' class='action-btn warning-btn' onclick=\"return confirm('⚠️ This will DROP all tables and recreate them. Are you sure?')\">🔄 Fresh Migration + Seed</a>";
            $output .= "<a href='?token={$SECURITY_TOKEN}&action=storage_link' class='action-btn'>🔗 Create Storage Link</a>";
            $output .= "<a href='?token={$SECURITY_TOKEN}&action=clear_cache' class='action-btn'>🧹 Clear All Caches</a>";
            $output .= "<a href='?token={$SECURITY_TOKEN}&action=optimize' class='action-btn'>⚡ Optimize (Cache Config/Routes)</a>";
            $output .= "<a href='?token={$SECURITY_TOKEN}&action=key_generate' class='action-btn'>🔑 Generate App Key</a>";
            $output .= "<a href='?token={$SECURITY_TOKEN}&action=create_directories' class='action-btn'>📁 Create Required Directories</a>";
            $output .= "<a href='?token={$SECURITY_TOKEN}&action=test_api' class='action-btn'>🧪 Test API Health</a>";
            $output .= "</div>";
            break;
            
        case 'migrate':
            $output = "<h2>🗄️ Running Migrations...</h2>";
            Artisan::call('migrate', ['--force' => true]);
            $output .= "<pre>" . htmlspecialchars(Artisan::output()) . "</pre>";
            $output .= "<p style='color:green;'>✅ Migrations completed!</p>";
            break;
            
        case 'seed':
            $output = "<h2>🌱 Running Seeders...</h2>";
            Artisan::call('db:seed', ['--force' => true]);
            $output .= "<pre>" . htmlspecialchars(Artisan::output()) . "</pre>";
            $output .= "<p style='color:green;'>✅ Seeding completed!</p>";
            $output .= "<div class='info-box'>";
            $output .= "<strong>Default Admin Credentials:</strong><br>";
            $output .= "Email: <code>admin@smatatech.com</code><br>";
            $output .= "Password: <code>password</code><br>";
            $output .= "<strong style='color:red;'>⚠️ Change this password immediately after login!</strong>";
            $output .= "</div>";
            break;
            
        case 'migrate_fresh_seed':
            $output = "<h2>🔄 Fresh Migration + Seeding...</h2>";
            $output .= "<p style='color:orange;'>⚠️ Dropping all tables and recreating...</p>";
            
            Artisan::call('migrate:fresh', ['--force' => true]);
            $output .= "<h3>Migrations:</h3>";
            $output .= "<pre>" . htmlspecialchars(Artisan::output()) . "</pre>";
            
            Artisan::call('db:seed', ['--force' => true]);
            $output .= "<h3>Seeding:</h3>";
            $output .= "<pre>" . htmlspecialchars(Artisan::output()) . "</pre>";
            
            $output .= "<p style='color:green;'>✅ Fresh migration and seeding completed!</p>";
            $output .= "<div class='info-box'>";
            $output .= "<strong>Default Admin Credentials:</strong><br>";
            $output .= "Email: <code>admin@smatatech.com</code><br>";
            $output .= "Password: <code>password</code><br>";
            $output .= "<strong style='color:red;'>⚠️ Change this password immediately after login!</strong>";
            $output .= "</div>";
            break;
            
        case 'storage_link':
            $output = "<h2>🔗 Creating Storage Link...</h2>";
            
            $target = storage_path('app/public');
            $link = public_path('storage');
            
            // Ensure target exists
            if (!file_exists($target)) {
                mkdir($target, 0755, true);
            }
            
            if (file_exists($link)) {
                if (is_link($link)) {
                    $output .= "<p>✅ Symlink already exists.</p>";
                } else {
                    $output .= "<p>ℹ️ Storage folder already exists (manual copy method).</p>";
                }
            } else {
                // Try symlink first
                if (@symlink($target, $link)) {
                    $output .= "<p style='color:green;'>✅ Symbolic link created successfully!</p>";
                } else {
                    // Fallback: create directory and show instructions
                    @mkdir($link, 0755, true);
                    $output .= "<p style='color:orange;'>⚠️ Symlink creation failed (common on shared hosting).</p>";
                    $output .= "<div class='info-box'>";
                    $output .= "<strong>Manual Solution:</strong><br>";
                    $output .= "1. In cPanel File Manager, navigate to <code>public_html/storage</code><br>";
                    $output .= "2. Upload files you want publicly accessible to <code>storage/app/public</code><br>";
                    $output .= "3. They will be accessible at <code>yourdomain.com/storage/filename</code><br>";
                    $output .= "OR: Copy contents from <code>storage/app/public</code> to <code>public/storage</code> manually.";
                    $output .= "</div>";
                }
            }
            break;
            
        case 'clear_cache':
            $output = "<h2>🧹 Clearing All Caches...</h2>";
            
            $commands = [
                'config:clear' => 'Config cache',
                'cache:clear' => 'Application cache',
                'route:clear' => 'Route cache',
                'view:clear' => 'View cache',
            ];
            
            foreach ($commands as $command => $label) {
                try {
                    Artisan::call($command);
                    $output .= "<p>✅ {$label} cleared</p>";
                } catch (Exception $e) {
                    $output .= "<p>⚠️ {$label}: " . $e->getMessage() . "</p>";
                }
            }
            
            $output .= "<p style='color:green;'>✅ All caches cleared!</p>";
            break;
            
        case 'optimize':
            $output = "<h2>⚡ Optimizing for Production...</h2>";
            
            // Clear first
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            
            // Then cache
            try {
                Artisan::call('config:cache');
                $output .= "<p>✅ Config cached</p>";
            } catch (Exception $e) {
                $output .= "<p>⚠️ Config cache failed: " . $e->getMessage() . "</p>";
            }
            
            try {
                Artisan::call('route:cache');
                $output .= "<p>✅ Routes cached</p>";
            } catch (Exception $e) {
                $output .= "<p>⚠️ Route cache failed (may have closure routes): " . $e->getMessage() . "</p>";
            }
            
            try {
                Artisan::call('view:cache');
                $output .= "<p>✅ Views cached</p>";
            } catch (Exception $e) {
                $output .= "<p>⚠️ View cache failed: " . $e->getMessage() . "</p>";
            }
            
            $output .= "<p style='color:green;'>✅ Optimization completed!</p>";
            break;
            
        case 'key_generate':
            $output = "<h2>🔑 Generating Application Key...</h2>";
            
            if (config('app.key')) {
                $output .= "<p style='color:orange;'>⚠️ An APP_KEY already exists. Generating a new one will invalidate existing encrypted data.</p>";
            }
            
            Artisan::call('key:generate', ['--force' => true]);
            $output .= "<pre>" . htmlspecialchars(Artisan::output()) . "</pre>";
            $output .= "<p style='color:green;'>✅ Application key generated!</p>";
            $output .= "<p><strong>Note:</strong> The key has been saved to your .env file.</p>";
            break;
            
        case 'create_directories':
            $output = "<h2>📁 Creating Required Directories...</h2>";
            
            $directories = [
                storage_path('logs'),
                storage_path('framework/cache/data'),
                storage_path('framework/sessions'),
                storage_path('framework/views'),
                storage_path('app/public'),
                storage_path('app/private'),
                base_path('bootstrap/cache'),
            ];
            
            foreach ($directories as $dir) {
                if (!file_exists($dir)) {
                    if (@mkdir($dir, 0755, true)) {
                        $output .= "<p>✅ Created: " . str_replace(base_path(), '', $dir) . "</p>";
                    } else {
                        $output .= "<p>❌ Failed to create: " . str_replace(base_path(), '', $dir) . "</p>";
                    }
                } else {
                    $output .= "<p>✓ Exists: " . str_replace(base_path(), '', $dir) . "</p>";
                }
            }
            
            // Create .gitignore files
            $gitignoreContent = "*\n!.gitignore\n";
            $gitignorePaths = [
                storage_path('framework/cache/data/.gitignore'),
                storage_path('framework/sessions/.gitignore'),
                storage_path('framework/views/.gitignore'),
            ];
            
            foreach ($gitignorePaths as $path) {
                if (!file_exists($path)) {
                    @file_put_contents($path, $gitignoreContent);
                }
            }
            
            $output .= "<p style='color:green;'>✅ Directory structure ready!</p>";
            break;
            
        case 'test_api':
            $output = "<h2>🧪 API Health Check</h2>";
            
            // Test basic API response
            $baseUrl = config('app.url');
            $output .= "<p>Base URL: <code>{$baseUrl}</code></p>";
            
            // Test database
            try {
                DB::connection()->getPdo();
                $output .= "<p>✅ Database: Connected</p>";
            } catch (Exception $e) {
                $output .= "<p>❌ Database: " . $e->getMessage() . "</p>";
            }
            
            // Test routes
            $output .= "<h3>📡 API Endpoints:</h3>";
            $output .= "<ul>";
            $output .= "<li>Health Check: <code>GET {$baseUrl}/up</code></li>";
            $output .= "<li>Public Settings: <code>GET {$baseUrl}/api/settings</code></li>";
            $output .= "<li>Admin Login: <code>POST {$baseUrl}/api/admin/login</code></li>";
            $output .= "<li>User Login: <code>POST {$baseUrl}/api/auth/login</code></li>";
            $output .= "</ul>";
            
            $output .= "<h3>🔐 Test Admin Login:</h3>";
            $output .= "<pre>";
            $output .= "curl -X POST {$baseUrl}/api/admin/login \\\n";
            $output .= "  -H 'Content-Type: application/json' \\\n";
            $output .= "  -H 'Accept: application/json' \\\n";
            $output .= "  -d '{\"email\": \"admin@smatatech.com\", \"password\": \"password\"}'";
            $output .= "</pre>";
            
            $output .= "<p style='color:green;'>✅ Use the above curl command or Postman to test the API.</p>";
            break;
            
        default:
            $output = "<p style='color:red;'>Unknown action: " . htmlspecialchars($action) . "</p>";
            $success = false;
    }
} catch (Exception $e) {
    $output = "<h2 style='color:red;'>❌ Error</h2>";
    $output .= "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    if (config('app.debug')) {
        $output .= "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    }
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
            max-width: 1000px;
            margin: 0 auto;
            background: #f5f5f5;
            color: #333;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 10px; }
        h2 { color: #34495e; margin-top: 30px; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        h3 { color: #555; margin-top: 20px; }
        h4 { color: #666; margin-top: 15px; }
        pre {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            font-size: 13px;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        code {
            background: #ecf0f1;
            padding: 2px 8px;
            border-radius: 3px;
            font-family: 'Consolas', 'Monaco', monospace;
            font-size: 13px;
        }
        ul { padding-left: 20px; }
        li { margin: 5px 0; }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .info-box {
            background: #e3f2fd;
            border: 1px solid #2196f3;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 20px;
        }
        .action-btn {
            display: block;
            padding: 15px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            transition: background 0.3s;
        }
        .action-btn:hover { background: #2980b9; }
        .warning-btn { background: #e74c3c; }
        .warning-btn:hover { background: #c0392b; }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #3498db;
            color: white;
            border-radius: 5px;
            text-decoration: none;
        }
        .back-link:hover { background: #2980b9; }
        .delete-warning {
            background: #ffebee;
            border: 2px solid #f44336;
            padding: 20px;
            border-radius: 5px;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Smatatech API Installer</h1>
        
        <?php echo $output; ?>
        
        <?php if ($action !== 'status'): ?>
            <a href="?token=<?php echo htmlspecialchars($SECURITY_TOKEN); ?>&action=status" class="back-link">← Back to Status</a>
        <?php endif; ?>
        
        <div class="delete-warning">
            <h3>🗑️ Post-Installation Cleanup</h3>
            <p><strong>IMPORTANT:</strong> Delete this file (<code>install.php</code>) immediately after completing setup!</p>
            <p>This file provides administrative access to your application and should never exist on a production server.</p>
            <p>To delete: Use cPanel File Manager → Navigate to <code>public_html</code> → Delete <code>install.php</code></p>
        </div>
        
        <hr style="margin-top: 30px;">
        <p style="color: #7f8c8d; font-size: 12px; text-align: center;">
            Smatatech API Installer v1.0 | Laravel <?php echo app()->version(); ?> | PHP <?php echo PHP_VERSION; ?>
        </p>
    </div>
</body>
</html>
