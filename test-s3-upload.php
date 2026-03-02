<?php
/**
 * Test S3 Upload Configuration
 * Run: php test-s3-upload.php
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Storage;

echo "=== Testing S3 Upload Configuration ===\n\n";

// 1. Check filesystem config
echo "1. Filesystem Configuration:\n";
$defaultDisk = config('filesystems.default');
echo "   Default disk: {$defaultDisk}\n";

$s3Config = config('filesystems.disks.s3');
echo "   S3 bucket: " . ($s3Config['bucket'] ?? 'not set') . "\n";
echo "   S3 region: " . ($s3Config['region'] ?? 'not set') . "\n";
echo "   S3 visibility: " . ($s3Config['visibility'] ?? 'not set') . "\n";
echo "   S3 URL: " . ($s3Config['url'] ?? 'not set') . "\n";

// 2. Test Storage facade
echo "\n2. Storage Facade Test:\n";
try {
    $disk = Storage::disk($defaultDisk);
    echo "   ✅ Storage disk '{$defaultDisk}' is accessible\n";
    
    // Test if we can list files (for S3) or check directory (for local)
    if ($defaultDisk === 's3') {
        $files = $disk->files('images');
        echo "   ✅ S3 connection successful\n";
        echo "   Found " . count($files) . " files in 'images' folder\n";
    } else {
        $path = storage_path('app/public/images');
        if (is_dir($path)) {
            echo "   ✅ Local storage directory exists\n";
        } else {
            echo "   ⚠️  Local storage directory doesn't exist (will be created on first upload)\n";
        }
    }
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// 3. Test URL generation
echo "\n3. URL Generation Test:\n";
$testFileName = "test_image.jpg";
$testPath = "images/profile/profile_pictures/{$testFileName}";

try {
    if ($defaultDisk === 's3') {
        $url = Storage::disk($defaultDisk)->url($testPath);
        echo "   Test URL: {$url}\n";
        echo "   ✅ URL generation works\n";
    } else {
        $url = Storage::disk($defaultDisk)->url($testPath);
        echo "   Test URL: {$url}\n";
        echo "   ✅ URL generation works\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Error generating URL: " . $e->getMessage() . "\n";
}

// 4. Check .env settings
echo "\n4. Environment Variables:\n";
$envVars = [
    'FILESYSTEM_DISK',
    'AWS_ACCESS_KEY_ID',
    'AWS_SECRET_ACCESS_KEY',
    'AWS_DEFAULT_REGION',
    'AWS_BUCKET',
    'AWS_URL'
];

foreach ($envVars as $var) {
    $value = env($var);
    if ($var === 'AWS_SECRET_ACCESS_KEY' && $value) {
        echo "   {$var}: " . substr($value, 0, 4) . "..." . " (hidden)\n";
    } else {
        echo "   {$var}: " . ($value ?: 'not set') . "\n";
    }
}

echo "\n=== Test Complete ===\n";
echo "\nNext steps:\n";
if ($defaultDisk === 's3') {
    echo "✅ S3 is configured as default disk\n";
    echo "✅ Files will be uploaded to S3\n";
    echo "✅ URLs will point to S3\n";
} else {
    echo "⚠️  Default disk is '{$defaultDisk}', not 's3'\n";
    echo "   Set FILESYSTEM_DISK=s3 in your .env to use S3\n";
}
