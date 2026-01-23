#!/bin/bash

# Start Laravel development server with custom PHP configuration
# This script ensures proper file upload limits for development

echo "Starting Laravel server with custom PHP configuration..."
echo "Upload limits: 100MB"
echo "Post size: 100MB"
echo "Memory limit: 256MB"
echo ""

# Kill any existing Laravel server processes
pkill -f "php.*serve" 2>/dev/null

# Start the server with custom configuration using PHP's built-in server
cd public && php -S localhost:8000 -c ../php.ini
