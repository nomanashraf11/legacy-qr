# Deployment Guide

## Local Development Setup

1. **Copy environment file:**

    ```bash
    cd frontend
    cp .env.local.example .env.local
    ```

2. **Edit `.env.local`** with your local settings:

    ```
    VITE_API_BASE_URL=http://localhost:8000/api
    ```

3. **Start development server:**
    ```bash
    npm run dev
    ```

## Production Deployment

### Option 1: Using the deployment script (Recommended)

On your production server:

```bash
./deploy-production.sh
```

### Option 2: Manual deployment

1. **Pull latest code:**

    ```bash
    git pull origin main
    ```

2. **Install backend dependencies:**

    ```bash
    composer install --no-dev --optimize-autoloader
    ```

3. **Clear and cache Laravel:**

    ```bash
    php artisan config:clear
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    ```

4. **Build frontend with production environment:**

    ```bash
    cd frontend
    npm install
    export VITE_API_BASE_URL="https://www.livinglegacyqr.xyz/api"
    npm run build
    cd ..
    ```

5. **Run migrations:**
    ```bash
    php artisan migrate --force
    ```

## Environment Variables

### Backend (.env)

-   `FILESYSTEM_DISK=s3` (or `local` for local dev)
-   `AWS_BUCKET=your-bucket-name`
-   `AWS_ACCESS_KEY_ID=your-key`
-   `AWS_SECRET_ACCESS_KEY=your-secret`
-   `AWS_DEFAULT_REGION=us-east-1`

### Frontend (Build-time)

Set these as environment variables when building:

-   `VITE_API_BASE_URL` - API endpoint URL
-   `VITE_GOOGLE_API_KEY` - Google API key
-   `VITE_SPOTIFY_CLIENT_ID` - Spotify client ID
-   `VITE_LIVE_URL` - Live URL
-   `VITE_BASE_URL` - Base URL

## Important Notes

-   **Frontend `.env.local` is gitignored** - your local settings won't be committed
-   **Frontend config uses environment variables** - defaults to production values
-   **Always rebuild frontend** after pulling code on production server
-   **Backend `.env` is gitignored** - keep production secrets safe

## Troubleshooting

If frontend shows wrong API URL:

1. Check that you rebuilt the frontend after pulling code
2. Verify environment variables are set correctly during build
3. Clear browser cache and hard refresh (Cmd+Shift+R / Ctrl+Shift+R)
