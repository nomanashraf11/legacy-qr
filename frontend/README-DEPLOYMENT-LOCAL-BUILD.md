# Deployment Guide - Local Build & Copy

This guide is for deploying when your production server doesn't have enough memory to build the frontend.

## Workflow Overview

1. **Build frontend locally** (on your development machine)
2. **Copy built files to server** (via SCP)
3. **Deploy backend on server** (pull code, run migrations, etc.)

## Step-by-Step Instructions

### Step 1: Build Frontend Locally

On your **local machine**:

```bash
./build-and-deploy.sh
```

This will:
- Build the frontend with production environment variables
- Create optimized production build in `frontend/dist/`

### Step 2: Copy Build Files to Server

On your **local machine**:

```bash
./copy-build-to-server.sh
```

The script will prompt you for:
- Server details (user@host)
- Remote path (e.g., `/var/www/html/public/`)

**Or manually:**
```bash
scp -r frontend/dist/* user@your-server.com:/var/www/html/public/
```

### Step 3: Deploy Backend on Server

On your **production server**:

```bash
./deploy-production-server-only.sh
```

This will:
- Pull latest code from git
- Install backend dependencies
- Clear and cache Laravel configs
- Run database migrations
- Set proper permissions

## Alternative: One-Line Copy

If you know your server details, you can copy directly:

```bash
# Build locally
./build-and-deploy.sh

# Copy to server (replace with your details)
scp -r frontend/dist/* user@server.com:/var/www/html/public/
```

## Server Paths (Common)

- **Apache**: `/var/www/html/public/`
- **Nginx**: `/var/www/your-app/public/`
- **cPanel**: `/home/username/public_html/`
- **Laravel Forge**: `/home/forge/your-app/public/`

## Troubleshooting

### Permission Issues
If you get permission errors, you may need to:
```bash
# On server, set ownership
sudo chown -R www-data:www-data /var/www/html/public/
sudo chmod -R 755 /var/www/html/public/
```

### Files Not Updating
1. Clear browser cache (hard refresh: Cmd+Shift+R / Ctrl+Shift+R)
2. Check file permissions on server
3. Verify files were copied correctly

### Build Fails Locally
- Make sure you have Node.js and npm installed
- Run `npm install` in frontend directory first
- Check for any errors in the build output

## Quick Reference

```bash
# Local machine - Build
./build-and-deploy.sh

# Local machine - Copy to server
./copy-build-to-server.sh

# Production server - Deploy backend
./deploy-production-server-only.sh
```

## Environment Variables

The build script automatically sets production environment variables:
- `VITE_API_BASE_URL=https://www.livinglegacyqr.com/api`
- `VITE_GOOGLE_API_KEY=...`
- `VITE_SPOTIFY_CLIENT_ID=...`
- `VITE_LIVE_URL=https://qr.livinglegacyqr.com/`
- `VITE_BASE_URL=https://legacy.livinglegacyqr.com/`

No need to set these manually!
