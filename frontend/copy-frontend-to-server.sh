#!/bin/bash
# Copy Frontend Build to Production Server

set -e

if [ ! -d "frontend/dist" ]; then
    echo "❌ Error: frontend/dist not found!"
    echo "   Run: cd frontend && npm run build:prod"
    exit 1
fi

echo "📋 Server Configuration"
read -p "Enter server user@host (e.g., ec2-user@your-server.com): " SERVER
read -p "Enter remote path to public directory (e.g., /var/www/html/public/): " REMOTE_PATH

echo ""
echo "⚠️  About to copy frontend/dist/* to:"
echo "   Server: ${SERVER}"
echo "   Path: ${REMOTE_PATH}"
read -p "Continue? (y/n): " -n 1 -r
echo ""

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Cancelled."
    exit 1
fi

echo "📤 Copying files..."
scp -r frontend/dist/* ${SERVER}:${REMOTE_PATH}

echo "✅ Files copied successfully!"
