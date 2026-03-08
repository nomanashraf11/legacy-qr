import { useEffect, useRef, useState } from "react";
import { GOOGLE_API_KEY } from "../config";
import { getUserData, useAppSelector } from "../redux/index.js";

const useScript = (url) => {
    const [isScriptLoaded, setIsScriptLoaded] = useState(false);

    useEffect(() => {
        if (!url) {
            setIsScriptLoaded(false);
            return undefined;
        }

        const script = document.createElement("script");
        script.src = url;
        script.async = true;

        script.onload = () => {
            setIsScriptLoaded(true);
        };

        document.body.appendChild(script);

        return () => {
            document.body.removeChild(script);
        };
    }, [url]);

    return isScriptLoaded;
};

export const StaticMap = () => {
    const data = useAppSelector(getUserData);

    const mapRef = useRef(null);

    const isScriptLoaded = useScript(
        GOOGLE_API_KEY
            ? `https://maps.googleapis.com/maps/api/js?key=${GOOGLE_API_KEY}&libraries=places`
            : null
    );

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
        if (!isScriptLoaded) return;

        initMap();
    }, [isScriptLoaded, data, data?.latitude, data?.longitude]);

    return (
        <div className="w-full">
            {!GOOGLE_API_KEY && (
                <p className="text-red-400 text-xs mb-2">
                    Google Maps key is missing. Set `VITE_GOOGLE_API_KEY` in
                    frontend `.env.local`.
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
