import { useState, useEffect } from "react";
import { useParams, useNavigate } from "react-router-dom";
import { useFetchTributeData } from "../../services";
import { EmptyModal, LoginModal, RegisterModal } from "../../components";

export const Link = () => {
    const { id } = useParams();
    const navigate = useNavigate();
    const { data, isLoading, refetch } = useFetchTributeData(id);

    const [showEmptyModal, setShowEmptyModal] = useState(false);
    const [openLoginModal, setOpenLoginModal] = useState(false);
    const [openRegisterModal, setOpenRegisterModal] = useState(false);

    // Show EmptyModal when component mounts (status 201 means QR needs linking)
    useEffect(() => {
        if (data && !isLoading && data?.status === 201) {
            setShowEmptyModal(true);
        }
    }, [data, isLoading]);

    // Handle when modals are closed - show fallback buttons
    const [showFallbackButtons, setShowFallbackButtons] = useState(false);

    useEffect(() => {
        if (
            !showEmptyModal &&
            !openLoginModal &&
            !openRegisterModal &&
            data &&
            !isLoading &&
            data?.status === 201
        ) {
            setShowFallbackButtons(true);
        } else {
            setShowFallbackButtons(false);
        }
    }, [showEmptyModal, openLoginModal, openRegisterModal, data, isLoading]);

    // Handle successful login/registration
    const handleAuthSuccess = () => {
        setShowEmptyModal(false);
        setOpenLoginModal(false);
        setOpenRegisterModal(false);
        // Refresh data to get updated status
        refetch();
    };

    // Check if we should redirect after successful authentication
    useEffect(() => {
        if (data && !isLoading && data?.status !== 201) {
            // QR code is no longer in "needs linking" status
            // Redirect based on version type or status
            if (data?.status === 200) {
                if (data?.Details?.version_type === "christmas") {
                    navigate(`/${id}/christmas`);
                } else {
                    navigate(`/${id}/legacy`);
                }
            }
        }
    }, [data, isLoading, id, navigate]);

    if (isLoading) {
        return (
            <div className="min-h-screen flex items-center justify-center">
                <div className="text-center">
                    <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto mb-4"></div>
                    <p className="text-gray-600">Loading...</p>
                </div>
            </div>
        );
    }

    return (
        <div className="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-100">
            {/* App Logo Header - Simple and Clean */}
            <div className="py-8 px-6 text-center">
                <div className="max-w-6xl mx-auto">
                    {/* App Logo */}
                    <div className="mb-6">
                        <img
                            src="/logo-dark.png"
                            alt="Living Legacy"
                            className="h-20 w-40 object-cover mx-auto"
                        />
                    </div>
                    <h1 className="text-3xl font-bold text-gray-800 mb-2">
                        Living Legacy
                    </h1>
                    <p className="text-gray-600 text-lg">
                        Preserve precious memories forever
                    </p>
                </div>
            </div>

            {/* Main Content */}
            <div className="max-w-5xl mx-auto py-12 px-8">
                <div className="text-center">
                    <div className="bg-white rounded-3xl shadow-lg p-12 border border-gray-200">
                        <div className="text-6xl mb-4">🕊️</div>
                        <h3 className="text-2xl font-bold text-gray-800 mb-4">
                            Connect Your Living Legacy Memorial
                        </h3>
                        <p className="text-gray-600 text-lg max-w-2xl mx-auto">
                            This QR code is ready to be connected to your
                            account. Please login or register to set up your
                            Living Legacy memorial page and start preserving
                            precious memories.
                        </p>

                        {/* Fallback buttons when modals are closed */}
                        {showFallbackButtons && (
                            <div className="mt-8">
                                <div className="flex flex-col sm:flex-row gap-4 justify-center">
                                    <button
                                        onClick={() =>
                                            setOpenRegisterModal(true)
                                        }
                                        className="px-6 py-3 bg-gray-800 hover:bg-gray-900 text-white rounded-lg font-medium transition-all duration-300 shadow-lg hover:shadow-xl"
                                    >
                                        Register
                                    </button>
                                    <button
                                        onClick={() => setOpenLoginModal(true)}
                                        className="px-6 py-3 border-2 border-gray-600 text-gray-700 hover:bg-gray-100 hover:border-gray-800 rounded-lg font-medium transition-all duration-300"
                                    >
                                        Login
                                    </button>
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </div>

            {/* Empty Modal for QR code linking */}
            <EmptyModal
                open={showEmptyModal}
                setOpen={setShowEmptyModal}
                setOpenLoginModal={setOpenLoginModal}
                setOpenRegisterModal={setOpenRegisterModal}
                setIsOpen={() => {}} // We don't have tour in Link page
                refetch={refetch}
                versionType={data?.version_type || "full"}
            />

            {/* Login Modal */}
            <LoginModal
                open={openLoginModal}
                setOpen={setOpenLoginModal}
                refetch={refetch}
                onSuccess={handleAuthSuccess}
            />

            {/* Register Modal */}
            <RegisterModal
                open={openRegisterModal}
                setOpen={setOpenRegisterModal}
                setOpenLoginModal={setOpenLoginModal}
                refetch={refetch}
                onSuccess={handleAuthSuccess}
            />
        </div>
    );
};
