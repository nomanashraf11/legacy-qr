#!/bin/bash
# Build Frontend Locally and Deploy to Server
# This script builds the frontend locally and copies it to production server

set -e

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${BLUE}🔨 Building frontend for production locally...${NC}"

# Build frontend with production environment variables
cd frontend
export VITE_API_BASE_URL="https://www.livinglegacyqr.xyz/api"
export VITE_GOOGLE_API_KEY="AIzaSyBSF_cWChkYEVRE337dWmKl1usv9asM1As"
export VITE_SPOTIFY_CLIENT_ID="a079012386e644ba81a345fed291157b"
export VITE_LIVE_URL="https://qr.livinglegacyqr.com/"
export VITE_BASE_URL="https://legacy.livinglegacyqr.com/"

npm run build

echo -e "${GREEN}✅ Frontend build complete!${NC}"
echo -e "${BLUE}📦 Build output: frontend/dist/${NC}"
cd ..

# Check if dist directory exists
if [ ! -d "frontend/dist" ]; then
    echo -e "${YELLOW}❌ Error: frontend/dist directory not found!${NC}"
    exit 1
fi

echo ""
echo -e "${YELLOW}📋 Next steps:${NC}"
echo "  1. Copy built files to server using: ./copy-build-to-server.sh"
echo "  2. Or manually: scp -r frontend/dist/* user@server:/path/to/public/"
