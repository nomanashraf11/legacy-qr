import { useEffect, useState } from "react";
import { useNavigate, useParams } from "react-router-dom";
import { useFetchTributeData } from "../services";
import { useAppSelector, getAuthToken } from "../redux";

export const Blank = () => {
    const { id } = useParams();
    const navigate = useNavigate();
    const { data, isLoading } = useFetchTributeData(id);
    const [hasCheckedVersion, setHasCheckedVersion] = useState(false);
    const token = useAppSelector(getAuthToken);

    useEffect(() => {
        if (id && data && !isLoading && !hasCheckedVersion) {
            setHasCheckedVersion(true);

            // Debug logging
            console.log("Blank component - API response:", {
                id,
                status: data?.status,
                details: data?.Details,
                version_type: data?.version_type,
                hasToken: !!token,
            });

            // Handle different API response statuses
            if (data?.status === 201) {
                // QR code exists but needs setup
                const versionType = data?.version_type || "full";
                console.log("QR code version type:", versionType);

                if (token) {
                    // User is logged in - redirect based on version type
                    if (versionType === "christmas") {
                        console.log(
                            "Redirecting logged-in user to Christmas setup"
                        );
                        navigate(`/${id}/christmas`);
                    } else {
                        console.log(
                            "Redirecting logged-in user to Legacy setup"
                        );
                        navigate(`/${id}/legacy`);
                    }
                } else {
                    // User is not logged in - redirect to link page
                    console.log("Redirecting visitor to link page");
                    navigate(`/${id}/link`);
                }
            } else if (
                data?.status === 200 &&
                data?.Details?.version_type === "christmas"
            ) {
                // Christmas version with profile data
                console.log("Redirecting to Christmas page");
                navigate(`/${id}/christmas`);
            } else if (data?.status === 200) {
                // Full version with profile data
                console.log("Redirecting to Legacy page");
                navigate(`/${id}/legacy`);
            } else {
                // Fallback to legacy route
                console.log("Fallback redirect to Legacy page");
                navigate(`/${id}/legacy`);
            }
        }
    }, [id, data, isLoading, hasCheckedVersion, navigate, token]);

    // Show loading while checking version
    if (isLoading || !hasCheckedVersion) {
        return (
            <div className="min-h-screen flex items-center justify-center">
                <div className="text-center">
                    <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto mb-4"></div>
                    <p className="text-gray-600">Loading...</p>
                </div>
            </div>
        );
    }

    return null;
};
