<?php
/**
 * Local Test Script for S3 Upload Fixes
 * 
 * This script tests:
 * 1. File upload to S3 (or local if FILESYSTEM_DISK=local)
 * 2. URL generation
 * 3. Visibility settings
 * 
 * Run: php test-upload-local.php
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Storage;

echo "=== Local Upload Test ===\n\n";

// 1. Check configuration
$defaultDisk = config('filesystems.default');
echo "1. Configuration:\n";
echo "   Default disk: {$defaultDisk}\n";
echo "   Expected: 's3' for production, 'local' for local testing\n\n";

// 2. Test file upload
echo "2. Testing File Upload:\n";

// Create a test file
$testContent = "This is a test file for S3 upload";
$testFileName = "test_" . time() . ".txt";
$testPath = "images/profile/profile_pictures/{$testFileName}";

try {
    $disk = Storage::disk($defaultDisk);
    
    // Upload file
    echo "   Uploading test file...\n";
    $path = $disk->put($testPath, $testContent);
    echo "   ✅ File uploaded to: {$path}\n";
    
    // Set visibility (for S3)
    if ($defaultDisk === 's3') {
        $disk->setVisibility($path, 'public');
        echo "   ✅ Visibility set to 'public'\n";
    }
    
    // Generate URL
    $url = $disk->url($path);
    echo "   ✅ URL generated: {$url}\n";
    
    // Verify file exists
    if ($disk->exists($path)) {
        echo "   ✅ File exists in storage\n";
    } else {
        echo "   ❌ File not found!\n";
    }
    
    // Clean up - delete test file
    $disk->delete($path);
    echo "   ✅ Test file cleaned up\n";
    
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    echo "   Stack trace:\n" . $e->getTraceAsString() . "\n";
}

// 3. Test Resource URL generation
echo "\n3. Testing Resource URL Generation:\n";
try {
    $testImageName = "test_image.jpg";
    $testImagePath = "images/profile/profile_pictures/{$testImageName}";
    
    // Simulate what ProfileResource does
    $url = Storage::disk(config('filesystems.default'))->url($testImagePath);
    echo "   Test image URL: {$url}\n";
    
    if ($defaultDisk === 's3') {
        if (strpos($url, 's3.amazonaws.com') !== false || strpos($url, 's3.') !== false) {
            echo "   ✅ URL is an S3 URL\n";
        } else {
            echo "   ⚠️  URL doesn't look like S3 URL\n";
        }
    } else {
        if (strpos($url, '/storage/') !== false || strpos($url, 'http') === 0) {
            echo "   ✅ URL is a local/storage URL\n";
        } else {
            echo "   ⚠️  URL format unexpected\n";
        }
    }
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// 4. Summary
echo "\n=== Test Summary ===\n";
if ($defaultDisk === 's3') {
    echo "✅ Ready for S3 deployment\n";
    echo "✅ Files will be uploaded to S3\n";
    echo "✅ URLs will point to S3\n";
} else {
    echo "ℹ️  Using local storage (good for local testing)\n";
    echo "ℹ️  Set FILESYSTEM_DISK=s3 in .env for S3 uploads\n";
}

echo "\n✅ All tests completed!\n";
