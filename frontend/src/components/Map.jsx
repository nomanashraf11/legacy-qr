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

export const MapComponent = ({
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

        const input = inputRef.current;
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
            position: {
                lat: parseFloat(data?.latitude) || 0,
                lng: parseFloat(data?.longitude) || 0,
            },
            draggable: true,
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
        if (!isScriptLoaded) return;

        if (data) {
            initMap();
        }
    }, [isScriptLoaded, data, data?.latitude, data?.longitude]);

    return (
        <div>
            {!GOOGLE_API_KEY && (
                <p className="text-red-400 text-xs mb-2">
                    Google Maps key is missing. Set `VITE_GOOGLE_API_KEY` in
                    frontend `.env.local`.
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
