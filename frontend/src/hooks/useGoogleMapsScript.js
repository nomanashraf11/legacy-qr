import { useEffect, useState } from "react";

/**
 * Loads the Maps JS API. Sets `window.gm_authFailure` so InvalidKeyMapError surfaces as state
 * instead of uncaught internal errors (e.g. minified `YJ`).
 */
export function useGoogleMapsScript(mapsScriptUrl) {
    const [isScriptLoaded, setIsScriptLoaded] = useState(false);
    const [authFailed, setAuthFailed] = useState(false);

    useEffect(() => {
        if (!mapsScriptUrl) {
            setIsScriptLoaded(false);
            setAuthFailed(false);
            return undefined;
        }

        const prevGm = window.gm_authFailure;
        window.gm_authFailure = () => {
            setAuthFailed(true);
            if (typeof prevGm === "function") prevGm();
        };

        const script = document.createElement("script");
        script.src = mapsScriptUrl;
        script.async = true;
        script.onload = () => setIsScriptLoaded(true);
        document.body.appendChild(script);

        return () => {
            window.gm_authFailure = prevGm;
            if (script.parentNode) {
                document.body.removeChild(script);
            }
            setIsScriptLoaded(false);
            setAuthFailed(false);
        };
    }, [mapsScriptUrl]);

    return { isScriptLoaded, authFailed };
}
