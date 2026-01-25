#!/bin/bash
# Copy Frontend Build to Production Server
# Usage: ./copy-build-to-server.sh [server_user@server_host] [remote_path]

set -e

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

# Check if frontend/dist exists
if [ ! -d "frontend/dist" ]; then
    echo -e "${RED}❌ Error: frontend/dist directory not found!${NC}"
    echo "   Run './build-and-deploy.sh' first to build the frontend."
    exit 1
fi

# Get server details from user or use defaults
if [ -z "$1" ]; then
    echo -e "${YELLOW}📋 Server Configuration${NC}"
    read -p "Enter server user@host (e.g., user@example.com): " SERVER
else
    SERVER=$1
fi

if [ -z "$2" ]; then
    echo ""
    echo -e "${YELLOW}Common Laravel public paths:${NC}"
    echo "  1. /var/www/html/public/ (Apache)"
    echo "  2. /var/www/your-app/public/ (Nginx)"
    echo "  3. /home/user/public_html/ (cPanel)"
    echo ""
    read -p "Enter remote path to public directory: " REMOTE_PATH
else
    REMOTE_PATH=$2
fi

# Confirm before copying
echo ""
echo -e "${YELLOW}⚠️  About to copy frontend/dist/* to:${NC}"
echo "   Server: ${SERVER}"
echo "   Path: ${REMOTE_PATH}"
echo ""
read -p "Continue? (y/n): " -n 1 -r
echo ""

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Cancelled."
    exit 1
fi

# Copy files
echo -e "${BLUE}📤 Copying files to server...${NC}"
scp -r frontend/dist/* ${SERVER}:${REMOTE_PATH}

echo -e "${GREEN}✅ Files copied successfully!${NC}"
echo ""
echo -e "${YELLOW}📋 Next steps on server:${NC}"
echo "  1. Verify files are in place"
echo "  2. Set proper permissions if needed"
echo "  3. Clear Laravel caches: php artisan cache:clear"
