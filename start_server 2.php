<?php
// Set PHP configuration for large file uploads
ini_set('upload_max_filesize', '300M');
ini_set('post_max_size', '300M');
ini_set('max_execution_time', 1200);
ini_set('max_input_time', 1200);
ini_set('memory_limit', '1024M');

// Start Laravel development server
$command = 'php artisan serve --host=0.0.0.0 --port=8000';
echo "Starting server with custom PHP settings...\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";
echo "Starting server...\n";

passthru($command);

