// Environment-based configuration
// These values are injected at build time via Vite environment variables
// Set them in .env.local for local development or in your production environment

export const GOOGLE_API_KEY =
    import.meta.env.VITE_GOOGLE_API_KEY ||
    "AIzaSyBSF_cWChkYEVRE337dWmKl1usv9asM1As";
export const SPOTIFY_CLIENT_ID =
    import.meta.env.VITE_SPOTIFY_CLIENT_ID ||
    "a079012386e644ba81a345fed291157b";
export const SPOTIFY_ACCESS_TOKEN =
    "BQDmD0kR8cYRv0aMBdk10UvNoie5vQmZU24Cn45gqT4mPd9_xG3cFiRgs6hq9U7-LpY3BJv7oj0E7PPxM_A6veK0ml_hK-NJdmWQtNDdO0Di8cFTfXIeMyzJSBo2n2SXT53KVqWaH4aj7bDvmWSKARJv8iMZWAJSXJJlbdkPoUnIy9sH-SKzup-uuqcF6wDSC5TQwUb4BbiPuG9tuG2M3YY9vSaS";

export const LIVE_URL =
    import.meta.env.VITE_LIVE_URL || "https://qr.livinglegacyqr.com/";
export const BASE_URL =
    import.meta.env.VITE_BASE_URL || "https://legacy.livinglegacyqr.com/";

// API Base URL - defaults to production, override with .env.local for local development
export const API_BASE_URL =
    import.meta.env.VITE_API_BASE_URL || "https://www.livinglegacyqr.xyz/api";
