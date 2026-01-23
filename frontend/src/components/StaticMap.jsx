import { useEffect, useRef, useState } from "react";
import { GOOGLE_API_KEY } from "../config";
import { getUserData, useAppSelector } from "../redux/index.js";

const useScript = (url) => {
  const [isScriptLoaded, setIsScriptLoaded] = useState(false);

  useEffect(() => {
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
    `https://maps.googleapis.com/maps/api/js?key=${GOOGLE_API_KEY}&libraries=places`,
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
      <div
        id="map"
        ref={mapRef}
        className="rounded mt-3"
        style={{ height: "300px", width: "100%", maxHeight: "300px" }}
      />
    </div>
  );
};
