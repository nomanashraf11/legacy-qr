import { useEffect, useRef } from "react";
import { GOOGLE_API_KEY } from "../config";
import { useGoogleMapsScript } from "../hooks/useGoogleMapsScript";
import { getUserData, useAppSelector } from "../redux/index.js";
import { hasProfileMapCoords } from "../utils";

const DEFAULT_MAP_CENTER = { lat: 39.8283, lng: -98.5795 };

/** When `locationValue` is omitted, match previous behavior: read from Redux profile. */
function locationFromProfile(data) {
    if (!data) return { lat: "", lng: "" };
    const lat =
        data.latitude != null && String(data.latitude).trim() !== ""
            ? data.latitude
            : "";
    const lng =
        data.longitude != null && String(data.longitude).trim() !== ""
            ? data.longitude
            : "";
    return { lat, lng };
}

export const MapComponent = ({
    locationValue,
    onUpdateLocation,
    handleBlur,
    error,
    mapRef,
    mapVisible,
    setMapVisible,
}) => {
    const data = useAppSelector(getUserData);

    const inputRef = useRef(null);
    const markerRef = useRef(null);
    const hasLocationRef = useRef(false);

    const resolvedLocation =
        locationValue !== undefined ? locationValue : locationFromProfile(data);

    const mapsScriptUrl = GOOGLE_API_KEY
        ? `https://maps.googleapis.com/maps/api/js?key=${GOOGLE_API_KEY}&libraries=places`
        : null;

    const { isScriptLoaded, authFailed } = useGoogleMapsScript(mapsScriptUrl);

    function initMap() {
        const pinned = hasProfileMapCoords(resolvedLocation)
            ? {
                  lat: parseFloat(resolvedLocation.lat),
                  lng: parseFloat(resolvedLocation.lng),
              }
            : null;
        const center = pinned ?? DEFAULT_MAP_CENTER;

        const map = new window.google.maps.Map(mapRef.current, {
            center,
            zoom: pinned ? 11 : 4,
            mapTypeControl: false,
        });

        const input = inputRef.current;
        if (input) {
            input.value = pinned ? input.value : "";
        }
        const options = {
            types: ["geocode"],
        };

        const autocomplete = new window.google.maps.places.Autocomplete(
            input,
            options
        );
        autocomplete.bindTo("bounds", map);

        const marker = new window.google.maps.Marker({
            map,
            anchorPoint: new window.google.maps.Point(0, 0),
            position: center,
            draggable: true,
            visible: Boolean(pinned),
        });

        markerRef.current = marker;

        autocomplete.addListener("place_changed", () => {
            marker.setVisible(false);
            const place = autocomplete.getPlace();
            if (!place.geometry || !place.geometry.location) {
                window.alert(
                    "No hay detalles disponibles para la entrada: '" +
                        place.name +
                        "'"
                );
                return;
            }
            if (place.geometry.viewport) {
                map.fitBounds(place.geometry.viewport);
            } else {
                map.setCenter(place.geometry.location);
                map.setZoom(11);
            }
            marker.setPosition(place.geometry.location);
            marker.setVisible(true);
            hasLocationRef.current = true;
            updateInputField(place.geometry.location);
        });

        window.google.maps.event.addListener(marker, "dragend", function () {
            updateInputField(marker.getPosition());
            fetchAddress(marker.getPosition());
        });

        window.google.maps.event.addListener(map, "click", function (event) {
            marker.setPosition(event.latLng);
            marker.setVisible(true);
            updateInputField(event.latLng);
            fetchAddress(event.latLng);
        });

        function updateInputField(location) {
            onUpdateLocation({ lat: location.lat(), lng: location.lng() });
            setMapVisible(true);
        }

        function fetchAddress(latLng) {
            const geocoder = new window.google.maps.Geocoder();
            geocoder.geocode({ location: latLng }, function (results, status) {
                if (status === "OK") {
                    if (results[0]) {
                        input.value = results[0].formatted_address;
                    }
                } else {
                    console.log("Geocoder failed due to: " + status);
                }
            });
        }
    }

    useEffect(() => {
        if (authFailed || !isScriptLoaded || !window.google?.maps?.Map) return;
        if (!mapRef?.current || !inputRef?.current) return;

        // Do not require Redux `data` — Formik `locationValue` can be set before profile loads.
        try {
            initMap();
        } catch (e) {
            console.warn("[Map] init failed", e);
        }
    }, [
        authFailed,
        isScriptLoaded,
        resolvedLocation?.lat,
        resolvedLocation?.lng,
        data?.latitude,
        data?.longitude,
    ]);

    return (
        <div>
            {authFailed && (
                <p className="text-amber-300 text-sm mb-3 rounded-md border border-amber-500/40 bg-amber-950/40 p-3">
                    Google Maps rejected this API key (InvalidKeyMapError). Fix
                    it in{" "}
                    <a
                        className="underline"
                        href="https://console.cloud.google.com/google/maps-apis/api-list"
                        target="_blank"
                        rel="noreferrer"
                    >
                        Google Cloud
                    </a>
                    : enable <strong>Maps JavaScript API</strong>, check
                    billing, and under the key&apos;s{" "}
                    <strong>Application restrictions</strong> add this
                    page&apos;s origin (e.g.{" "}
                    <code className="text-xs">http://127.0.0.1:3000/*</code>).
                    Or set a working key in{" "}
                    <code className="text-xs">frontend/.env.local</code> as{" "}
                    <code className="text-xs">VITE_GOOGLE_API_KEY</code>.
                </p>
            )}
            {!GOOGLE_API_KEY && (
                <p className="text-red-400 text-xs mb-2">
                    Google Maps key is missing. Set `VITE_GOOGLE_API_KEY` in
                    frontend `.env.local` (see `.env.example`).
                </p>
            )}
            <input
                id="pac-input"
                ref={inputRef}
                onBlur={handleBlur}
                name="location"
                type="text"
                placeholder="Enter a location"
                className="border outline-none text-sm rounded-lg block w-full p-2.5 bg-[#333333] border-white/20 placeholder-gray-400 text-white"
            />
            {error && <p className="text-red-400 text-xs">{error}</p>}
            <div
                id="map"
                ref={mapRef}
                className="rounded mt-3"
                style={{
                    height: "300px",
                    width: "100%",
                    maxHeight: "300px",
                    visibility: mapVisible ? "visible" : "hidden",
                }}
            />
        </div>
    );
};
