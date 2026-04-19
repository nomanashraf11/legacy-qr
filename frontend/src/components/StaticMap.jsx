import { useEffect, useRef } from "react";
import { GOOGLE_API_KEY } from "../config";
import { useGoogleMapsScript } from "../hooks/useGoogleMapsScript";
import { getUserData, useAppSelector } from "../redux/index.js";

export const StaticMap = () => {
    const data = useAppSelector(getUserData);

    const mapRef = useRef(null);

    const mapsScriptUrl = GOOGLE_API_KEY
        ? `https://maps.googleapis.com/maps/api/js?key=${GOOGLE_API_KEY}&libraries=places`
        : null;

    const { isScriptLoaded, authFailed } = useGoogleMapsScript(mapsScriptUrl);

    function initMap() {
        const map = new window.google.maps.Map(mapRef.current, {
            center: {
                lat: parseFloat(data?.latitude) || 0,
                lng: parseFloat(data?.longitude) || 0,
            },
            zoom: 11,
            mapTypeControl: false,
        });

        const marker = new window.google.maps.Marker({
            map,
            anchorPoint: new window.google.maps.Point(0, 0),
            position: {
                lat: parseFloat(data?.latitude) || 0,
                lng: parseFloat(data?.longitude) || 0,
            },
        });
    }

    useEffect(() => {
        if (authFailed || !isScriptLoaded || !window.google?.maps?.Map) return;
        if (!mapRef?.current) return;
        try {
            initMap();
        } catch (e) {
            console.warn("[StaticMap] init failed", e);
        }
    }, [authFailed, isScriptLoaded, data, data?.latitude, data?.longitude]);

    return (
        <div className="w-full">
            {authFailed && (
                <p className="text-amber-300 text-sm mb-3 rounded-md border border-amber-500/40 bg-amber-950/40 p-3">
                    Google Maps rejected this API key. Enable Maps JavaScript
                    API and fix key restrictions for this origin, or set{" "}
                    <code className="text-xs">VITE_GOOGLE_API_KEY</code> in{" "}
                    <code className="text-xs">.env.local</code>.
                </p>
            )}
            {!GOOGLE_API_KEY && (
                <p className="text-red-400 text-xs mb-2">
                    Google Maps key is missing. Set `VITE_GOOGLE_API_KEY` in
                    frontend `.env.local` (see `.env.example`).
                </p>
            )}
            <div
                id="map"
                ref={mapRef}
                className="rounded mt-3"
                style={{ height: "300px", width: "100%", maxHeight: "300px" }}
            />
        </div>
    );
};
