import { IoClose } from "react-icons/io5";
import { useEffect } from "react";

export const ChristmasSuccessModal = ({
    open,
    setOpen,
    setOpenUploadPhotosModal,
    isDarkTheme = false,
}) => {
    if (!open) return null;

    const handleCloseModal = () => {
        setOpen(false);
    };

    const handleFinishLater = () => {
        setOpen(false);
        setOpenUploadPhotosModal(false); // Also close photo upload modal if it's open
    };

    // Handle ESC key to close modal
    useEffect(() => {
        const handleEscKey = (event) => {
            if (event.key === "Escape") {
                handleCloseModal();
            }
        };

        if (open) {
            document.addEventListener("keydown", handleEscKey);
        }

        return () => {
            document.removeEventListener("keydown", handleEscKey);
        };
    }, [open]);

    return (
        <div
            className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
            onClick={handleCloseModal}
        >
            <div
                className="relative p-4 w-full max-w-md max-h-full"
                onClick={(e) => e.stopPropagation()}
            >
                <div
                    className={`relative rounded-lg shadow-xl transition-all duration-300 ${
                        isDarkTheme ? "bg-gray-800" : "bg-white"
                    }`}
                >
                    <div className="p-8 text-center">
                        <div className="text-6xl mb-4">🎄</div>
                        <h2
                            className={`text-2xl font-bold mb-4 transition-colors duration-300 ${
                                isDarkTheme ? "text-white" : "text-gray-800"
                            }`}
                        >
                            Christmas Profile Created!
                        </h2>
                        <p
                            className={`mb-6 transition-colors duration-300 ${
                                isDarkTheme ? "text-gray-300" : "text-gray-600"
                            }`}
                        >
                            Your memory page has been set up successfully. You
                            can now add more photos to make it even more
                            special. Upload up to 20 photos total.
                        </p>

                        <div className="space-y-3">
                            <button
                                onClick={() => {
                                    setOpen(false);
                                    setOpenUploadPhotosModal(true);
                                }}
                                className="w-full bg-gradient-to-r from-red-500 to-green-500 text-white py-3 px-6 rounded-lg hover:from-red-600 hover:to-green-600 transition-all duration-300 font-medium"
                            >
                                Add Photos
                            </button>

                            <button
                                onClick={handleFinishLater}
                                className={`w-full py-3 px-6 rounded-lg transition-colors ${
                                    isDarkTheme
                                        ? "bg-gray-700 text-gray-200 hover:bg-gray-600"
                                        : "bg-gray-200 text-gray-800 hover:bg-gray-300"
                                }`}
                            >
                                Finish Later
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};
