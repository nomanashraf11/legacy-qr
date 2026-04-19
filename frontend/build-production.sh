#!/bin/bash
# Production Build Script
# This script builds the frontend with production environment variables

echo "🔨 Building frontend for production..."

# Set production environment variables
export VITE_API_BASE_URL="https://www.livinglegacyqr.com/api"
export VITE_GOOGLE_API_KEY="AIzaSyBSF_cWChkYEVRE337dWmKl1usv9asM1As"
export VITE_SPOTIFY_CLIENT_ID="a079012386e644ba81a345fed291157b"
export VITE_LIVE_URL="https://qr.livinglegacyqr.com/"
export VITE_BASE_URL="https://legacy.livinglegacyqr.com/"

# Build with production mode
npm run build

echo "✅ Production build complete!"
echo "📦 Build output: frontend/dist/"
