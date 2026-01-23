import { useState, useEffect } from "react";
import { useParams, Link } from "react-router-dom";
import { Helmet } from "react-helmet";
import { useFetchTributeData, useFetchQrCodesData } from "../../services";
import {
    useAppSelector,
    getUserData,
    getAuthToken,
    getID,
    removeUserToken,
    useAppDispatch,
} from "../../redux";
import {
    FaEdit,
    FaShare,
    FaChevronLeft,
    FaChevronRight,
    FaUpload,
    FaTrash,
    FaUser,
    FaCog,
    FaSignOutAlt,
} from "react-icons/fa";
import { TbPencil } from "react-icons/tb";
import { IoMdShare, IoIosMenu } from "react-icons/io";
import { IoClose } from "react-icons/io5";
import { toast } from "react-toastify";
import { Spotify } from "react-spotify-embed";
import { ChristmasEditModal } from "../../components/ChristmasEditModal";
import { ChristmasSetupModal } from "../../components/ChristmasSetupModal";
import { ChristmasSuccessModal } from "../../components/ChristmasSuccessModal";
import { LoginModal, EmptyModal, RegisterModal } from "../../components";
import { API_BASE_URL } from "../../config";
import { objectToFormData } from "../../utils";
import { useQueryClient } from "@tanstack/react-query";

export const Christmas = () => {
    const { id } = useParams();
    const { data, isLoading, refetch } = useFetchTributeData(id);
    const { data: qrCodes, isLoading: qrCodesLoading } = useFetchQrCodesData();
    const userData = useAppSelector(getUserData);
    const token = useAppSelector(getAuthToken);
    const currentId = useAppSelector(getID);
    const dispatch = useAppDispatch();
    const queryClient = useQueryClient();

    const [currentPhotoIndex, setCurrentPhotoIndex] = useState(0);
    const [isEditing, setIsEditing] = useState(false);
    const [showShareModal, setShowShareModal] = useState(false);
    const [isUploading, setIsUploading] = useState(false);
    const [showPhotoUpload, setShowPhotoUpload] = useState(false);
    const [showSetupModal, setShowSetupModal] = useState(false);
    const [showSuccessModal, setShowSuccessModal] = useState(false);
    const [selectedFiles, setSelectedFiles] = useState([]);
    const [uploadPreview, setUploadPreview] = useState(false);
    const [showHamburgerMenu, setShowHamburgerMenu] = useState(false);
    const [openLoginModal, setOpenLoginModal] = useState(false);
    const [openRegisterModal, setOpenRegisterModal] = useState(false);
    const [showEmptyModal, setShowEmptyModal] = useState(false);
    const [setupModalManuallyClosed, setSetupModalManuallyClosed] =
        useState(false);
    const [darkTheme, setDarkTheme] = useState(false);
    const [uploadErrors, setUploadErrors] = useState([]);
    const [fullscreenImage, setFullscreenImage] = useState(null);

    // Get photos from the API response
    const photos = data?.Details?.Photos || [];
    const profile = data?.Details || null;

    // Debug logging for component data
    useEffect(() => {
        if (data && !isLoading) {
            console.log("Christmas component - Data received:", {
                status: data?.status,
                details: data?.Details,
                hasPhotos: !!data?.Details?.Photos?.length,
                hasProfile: !!data?.Details?.name,
            });
        }
    }, [data, isLoading]);

    // Check if current user owns this QR code
    // Since APIs have authentication, if user has token, they can edit
    const isOwner = () => {
        const hasToken = !!token;
        console.log("isOwner function called:", { hasToken, token: !!token });
        // If user has token, they can edit (APIs will handle authorization)
        return hasToken;
    };

    // Handle logout
    const handleLogout = () => {
        dispatch(removeUserToken());
        queryClient.clear();
        refetch();
        setShowHamburgerMenu(false);
        toast.success("Logged out successfully");
    };

    // Auto-advance photo slider
    useEffect(() => {
        if (photos.length > 1) {
            const interval = setInterval(() => {
                setCurrentPhotoIndex((prevIndex) =>
                    prevIndex === photos.length - 1 ? 0 : prevIndex + 1
                );
            }, 4000); // Change photo every 4 seconds

            return () => clearInterval(interval);
        }
    }, [photos.length]);

    // Handle first-time setup flow - only for logged-in users who own the QR code
    useEffect(() => {
        console.log("Christmas component - Setup flow useEffect triggered:", {
            hasData: !!data,
            isLoading,
            hasToken: !!token,
            isOwner: isOwner(),
            setupModalManuallyClosed,
            dataStatus: data?.status,
            hasDetails: !!data?.Details,
            hasName: !!data?.Details?.name,
        });

        // Force show setup modal if user is logged in and QR needs setup
        if (data && !isLoading && token && !setupModalManuallyClosed) {
            // Check if this is a new Christmas QR code without profile
            // Handle both status 200 (no profile data) and status 201 (QR exists but needs setup)
            if (
                (data?.status === 200 && !data?.Details?.name) ||
                data?.status === 201
            ) {
                console.log("Showing setup modal for Christmas QR code");
                setShowSetupModal(true);

                // Debug: Check if the state was set correctly
                setTimeout(() => {
                    console.log(
                        "Setup modal state after setting:",
                        showSetupModal
                    );
                }, 100);
            }
        }
    }, [data, isLoading, token, setupModalManuallyClosed]);

    // Handle ESC key to close photo upload modal
    useEffect(() => {
        const handleEscKey = (event) => {
            if (event.key === "Escape" && showPhotoUpload) {
                closePhotoUploadModal();
            }
            if (event.key === "Escape" && fullscreenImage) {
                closeFullscreenImage();
            }
        };

        if (showPhotoUpload || fullscreenImage) {
            document.addEventListener("keydown", handleEscKey);
        }

        return () => {
            document.removeEventListener("keydown", handleEscKey);
        };
    }, [showPhotoUpload, fullscreenImage]);

    // Handle keyboard navigation for fullscreen viewer
    useEffect(() => {
        const handleKeyDown = (event) => {
            if (!fullscreenImage) return;

            switch (event.key) {
                case "ArrowLeft":
                    event.preventDefault();
                    prevFullscreenPhoto();
                    break;
                case "ArrowRight":
                    event.preventDefault();
                    nextFullscreenPhoto();
                    break;
                case "Escape":
                    event.preventDefault();
                    closeFullscreenImage();
                    break;
            }
        };

        if (fullscreenImage) {
            document.addEventListener("keydown", handleKeyDown);
        }

        return () => {
            document.removeEventListener("keydown", handleKeyDown);
        };
    }, [fullscreenImage, photos]);

    // Theme toggle function
    const handleThemeToggle = async () => {
        // Only allow theme toggle for logged-in users who own the QR code
        if (!token || !profile || !isOwner()) return;

        try {
            const newTheme = !profile.dark_theme;

            // Update local state immediately for better UX
            setDarkTheme(newTheme);

            // Prepare the payload for API
            const payload = {
                name: profile.name || "",
                bio: profile.bio || "",
                title: profile.title || "", // Preserve custom title
                dob: profile.dob || "2000-01-01", // Default date for Christmas edition
                dod: profile.dod || "",
                spotify: profile.spotify || "",
                dark_theme: newTheme,
            };

            // Create FormData
            const formData = new FormData();
            Object.keys(payload).forEach((key) => {
                if (payload[key] !== null && payload[key] !== undefined) {
                    formData.append(key, payload[key]);
                }
            });

            // Make API call to update theme
            const response = await fetch(
                `${API_BASE_URL}/${id}/add_bio?is_legacy=true`,
                {
                    method: "POST",
                    headers: {
                        Accept: "application/json",
                        Authorization: `Bearer ${token}`,
                    },
                    body: formData,
                }
            );

            const result = await response.json();

            if (result.status === 200) {
                toast.success("Theme updated successfully!");
                // Refresh data to get updated theme
                refetch();
            } else {
                toast.error("Failed to update theme");
                // Revert local state if API call failed
                setDarkTheme(profile.dark_theme);
            }
        } catch (error) {
            console.error("Theme toggle error:", error);
            toast.error("Failed to update theme");
            // Revert local state if API call failed
            setDarkTheme(profile.dark_theme);
        }
    };

    // Sync theme state with profile data
    useEffect(() => {
        if (profile?.dark_theme !== undefined) {
            setDarkTheme(profile.dark_theme);
        }
    }, [profile?.dark_theme]);

    // Handle empty state for visitors when QR code needs setup
    useEffect(() => {
        if (data && !isLoading && !token) {
            // Show empty modal for visitors when QR code exists but has no profile
            // Handle both status 200 (no profile data) and status 201 (QR exists but needs setup)
            if (
                (data?.status === 200 && !data?.Details?.name) ||
                data?.status === 201
            ) {
                setShowEmptyModal(true);
            }
        }
    }, [data, isLoading, token]);

    // Close hamburger menu when clicking outside
    useEffect(() => {
        const handleClickOutside = (event) => {
            if (showHamburgerMenu && !event.target.closest(".hamburger-menu")) {
                setShowHamburgerMenu(false);
            }
        };

        document.addEventListener("mousedown", handleClickOutside);
        return () => {
            document.removeEventListener("mousedown", handleClickOutside);
        };
    }, [showHamburgerMenu]);

    const nextPhoto = () => {
        setCurrentPhotoIndex((prevIndex) =>
            prevIndex === photos.length - 1 ? 0 : prevIndex + 1
        );
    };

    const prevPhoto = () => {
        setCurrentPhotoIndex((prevIndex) =>
            prevIndex === 0 ? photos.length - 1 : prevIndex - 1
        );
    };

    const handleShare = () => {
        if (navigator.share) {
            navigator.share({
                title: `Cherished memories with ${
                    profile?.name || "Someone Special"
                } to look back at this Christmas`,
                text: `Celebrate ${new Date().getFullYear()} with cherished memories and special moments shared by family and friends. Join us in remembering the beautiful times with ${
                    profile?.name || "Someone Special"
                }.`,
                url: window.location.href,
            });
        } else {
            navigator.clipboard.writeText(window.location.href);
            toast.success("Link copied to clipboard!");
        }
    };

    // Full-screen image viewer functions
    const openFullscreenImage = (photo) => {
        setFullscreenImage(photo);
    };

    const closeFullscreenImage = () => {
        setFullscreenImage(null);
    };

    const nextFullscreenPhoto = () => {
        if (fullscreenImage) {
            const currentIndex = photos.findIndex(
                (p) => p.uuid === fullscreenImage.uuid
            );
            const nextIndex = (currentIndex + 1) % photos.length;
            setFullscreenImage(photos[nextIndex]);
        }
    };

    const prevFullscreenPhoto = () => {
        if (fullscreenImage) {
            const currentIndex = photos.findIndex(
                (p) => p.uuid === fullscreenImage.uuid
            );
            const prevIndex =
                currentIndex === 0 ? photos.length - 1 : currentIndex - 1;
            setFullscreenImage(photos[prevIndex]);
        }
    };

    // Close photo upload modal and reset state
    const closePhotoUploadModal = () => {
        console.log("Closing photo upload modal");
        setShowPhotoUpload(false);
        setUploadPreview(false);
        setSelectedFiles([]);
        setIsUploading(false); // Ensure uploading state is reset
        setUploadErrors([]); // Clear any upload errors
    };

    // Handle file selection (preview mode)
    const handleFileSelection = (files) => {
        if (!files || files.length === 0) return;

        // Check total photo count limit (20 photos maximum for Christmas profiles)
        const currentPhotoCount = photos.length;
        if (currentPhotoCount + files.length > 20) {
            toast.error(
                `Maximum 20 photos allowed per Christmas profile. You currently have ${currentPhotoCount} photos and are trying to upload ${files.length} more.`
            );
            return;
        }

        // Validate file sizes (max 50MB each - increased from 3MB)
        const maxSize = 50 * 1024 * 1024; // 50MB in bytes
        const oversizedFiles = [];
        const validFiles = [];

        for (let i = 0; i < files.length; i++) {
            if (files[i].size > maxSize) {
                oversizedFiles.push(files[i].name);
            } else {
                validFiles.push(files[i]);
            }
        }

        if (oversizedFiles.length > 0) {
            toast.error(
                `Files too large (max 50MB): ${oversizedFiles.join(", ")}`
            );
        }

        if (validFiles.length > 0) {
            setSelectedFiles(validFiles);
            setUploadPreview(true);
        }
    };

    // Remove selected file
    const removeSelectedFile = (index) => {
        setSelectedFiles((prev) => prev.filter((_, i) => i !== index));
    };

    // Replace selected file
    const replaceSelectedFile = (index, newFile) => {
        // Validate file size (max 50MB - increased from 3MB)
        const maxSize = 50 * 1024 * 1024; // 50MB in bytes
        if (newFile.size > maxSize) {
            toast.error(`${newFile.name} is too large (max 50MB)`);
            return;
        }

        setSelectedFiles((prev) => {
            const newFiles = [...prev];
            newFiles[index] = newFile;
            return newFiles;
        });
    };

    // Generate meta tags for sharing
    const generateMetaTags = () => {
        const currentYear = new Date().getFullYear();
        const profileName = profile?.name || "Someone Special";

        return (
            <Helmet>
                <title>{`Cherished memories with ${profileName} to look back at this Christmas`}</title>
                <meta
                    name="description"
                    content={`Celebrate ${currentYear} with cherished memories and special moments shared by family and friends. Join us in remembering the beautiful times with ${profileName}.`}
                />

                {/* Open Graph Meta Tags */}
                <meta
                    property="og:title"
                    content={`Cherished memories with ${profileName} to look back at this Christmas`}
                />
                <meta
                    property="og:description"
                    content={`Celebrate ${currentYear} with cherished memories and special moments shared by family and friends. Join us in remembering the beautiful times with ${profileName}.`}
                />
                <meta property="og:type" content="website" />
                <meta property="og:url" content={window.location.href} />
                <meta
                    property="og:image"
                    content={
                        profile?.profile_picture || "/images/logo/logo-dark.png"
                    }
                />
                <meta
                    property="og:image:alt"
                    content={`Cherished Memories - ${currentYear} Christmas Edition`}
                />
                <meta property="og:site_name" content="Living Legacy Project" />

                {/* Twitter Meta Tags */}
                <meta name="twitter:card" content="summary_large_image" />
                <meta
                    name="twitter:title"
                    content={`Cherished memories with ${profileName} to look back at this Christmas`}
                />
                <meta
                    name="twitter:description"
                    content={`Celebrate ${currentYear} with cherished memories and special moments shared by family and friends. Join us in remembering the beautiful times with ${profileName}.`}
                />
                <meta
                    name="twitter:image"
                    content={
                        profile?.profile_picture || "/images/logo/logo-dark.png"
                    }
                />

                {/* Additional Meta Tags */}
                <meta
                    name="keywords"
                    content="christmas memories, cherished moments, family memories, holiday memories, remembrance, photos, videos, memories, family, friends"
                />
            </Helmet>
        );
    };

    // Confirm and upload photos
    const confirmUpload = async () => {
        if (selectedFiles.length === 0) return;

        // Double-check photo count limit before uploading
        const currentPhotoCount = photos.length;
        if (currentPhotoCount + selectedFiles.length > 20) {
            toast.error(
                `Maximum 20 photos allowed per Christmas profile. You currently have ${currentPhotoCount} photos and are trying to upload ${selectedFiles.length} more.`
            );
            return;
        }

        setIsUploading(true);

        try {
            let successCount = 0;
            let errorCount = 0;
            const errors = [];

            for (const file of selectedFiles) {
                try {
                    // Create FormData directly without using objectToFormData
                    const formData = new FormData();
                    formData.append("image", file);
                    formData.append(
                        "caption",
                        `Memory - ${new Date().toLocaleDateString()}`
                    );

                    const response = await fetch(
                        `${API_BASE_URL}/${id}/add_photo`,
                        {
                            method: "POST",
                            headers: {
                                Authorization: `Bearer ${token}`,
                            },
                            body: formData,
                            signal: AbortSignal.timeout(60000), // 60 seconds for larger files
                        }
                    );

                    if (!response.ok) {
                        const errorData = await response
                            .json()
                            .catch(() => ({}));
                        throw new Error(
                            `Failed to upload ${file.name}: ${
                                errorData.message || response.statusText
                            }`
                        );
                    }

                    successCount++;
                } catch (fileError) {
                    // Handle specific error types
                    let errorMessage = fileError.message;
                    if (fileError.name === "AbortError") {
                        errorMessage = "Upload timed out. Please try again.";
                    } else if (
                        fileError.name === "TypeError" &&
                        fileError.message.includes("fetch")
                    ) {
                        errorMessage =
                            "Network error. Please check your connection.";
                    }

                    errors.push(`${file.name}: ${errorMessage}`);
                    errorCount++;
                }
            }

            // Store errors for display in modal
            setUploadErrors(errors);

            // Show appropriate success/error messages
            if (successCount > 0 && errorCount === 0) {
                toast.success(
                    `${successCount} photo${
                        successCount > 1 ? "s" : ""
                    } uploaded successfully!`
                );
            } else if (successCount > 0 && errorCount > 0) {
                toast.warning(
                    `${successCount} photo${
                        successCount > 1 ? "s" : ""
                    } uploaded, ${errorCount} failed`
                );
            } else {
                toast.error("Failed to upload photos");
            }

            // Always refresh data and close modal, regardless of success/failure
            await refetch();
            closePhotoUploadModal();
        } catch (error) {
            toast.error("Failed to upload photos. Please try again.");
            // Ensure modal closes even on error
            closePhotoUploadModal();
        } finally {
            setIsUploading(false);
        }
    };

    // Delete photo functionality
    const handleDeletePhoto = async (photoUuid) => {
        if (!window.confirm("Are you sure you want to delete this photo?"))
            return;

        try {
            const response = await fetch(
                `${API_BASE_URL}/delete_photo/${photoUuid}`,
                {
                    method: "POST",
                    headers: {
                        Authorization: `Bearer ${token}`,
                        "Content-Type": "application/json",
                    },
                }
            );

            if (!response.ok) {
                throw new Error("Failed to delete photo");
            }

            toast.success("Photo deleted successfully!");
            refetch(); // Refresh the data
        } catch (error) {
            toast.error("Failed to delete photo");
            console.error("Delete error:", error);
        }
    };

    // Update profile functionality
    const handleUpdateProfile = async (formData) => {
        try {
            // Debug: Log the form data received from edit modal
            console.log("Christmas index - Form data received:", formData);

            // Filter out null file fields to avoid validation errors
            const filteredFormData = { ...formData };
            if (filteredFormData.profile_picture === null) {
                delete filteredFormData.profile_picture;
            }
            if (filteredFormData.cover_picture === null) {
                delete filteredFormData.cover_picture;
            }

            // Add current theme state to preserve it
            filteredFormData.dark_theme = darkTheme;

            console.log(
                "Christmas index - Filtered form data:",
                filteredFormData
            );

            // Debug: Create FormData and log what's being sent
            const formDataObj = objectToFormData(filteredFormData);
            console.log(
                "Christmas index - FormData object created:",
                formDataObj
            );

            // Debug: Log each field in FormData
            for (let [key, value] of formDataObj.entries()) {
                console.log(`FormData field - ${key}:`, value);
            }

            const response = await fetch(`${API_BASE_URL}/${id}/add_bio`, {
                method: "POST",
                headers: {
                    Authorization: `Bearer ${token}`,
                },
                body: formDataObj,
            });

            if (!response.ok) {
                throw new Error("Failed to update profile");
            }

            toast.success("Profile updated successfully!");
            await refetch();
        } catch (error) {
            toast.error("Failed to update profile");
            console.error("Update error:", error);
        }
    };

    if (isLoading) {
        return (
            <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-red-50 to-green-50">
                <div className="text-center">
                    <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-red-500 mx-auto mb-4"></div>
                    <p className="text-gray-600">Loading memories...</p>
                </div>
            </div>
        );
    }

    // Check if this is a new QR code that needs setup (status 201 or no profile data)
    const needsSetup =
        data?.status === 201 || (data?.status === 200 && !profile?.name);

    if (!profile && !needsSetup) {
        return (
            <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-red-50 to-green-50">
                <div className="text-center">
                    <h2 className="text-2xl font-bold text-gray-800 mb-4">
                        No Profile Found
                    </h2>
                    <p className="text-gray-600">
                        This QR code doesn't have a profile yet.
                    </p>
                </div>
            </div>
        );
    }

    // If it needs setup and user is logged in, force show setup modal
    if (needsSetup && token) {
        console.log("Christmas component - Showing setup mode:", {
            needsSetup,
            hasToken: !!token,
            dataStatus: data?.status,
            hasProfile: !!profile?.name,
        });

        // Setup modal state is managed by useEffect - no need to set it here

        // return (
        <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-gray-50">
            {/* Brand Navbar */}
            <nav className="bg-white shadow-sm border-b border-gray-200">
                <div className="max-w-6xl mx-auto px-6 py-4">
                    <div className="flex items-center justify-between">
                        {/* Brand */}
                        <div>
                            <h1 className="text-xl font-bold text-gray-800">
                                Living Legacy
                            </h1>
                            <p className="text-xs text-gray-500">
                                Preserving Memories Forever
                            </p>
                        </div>

                        {/* Christmas Edition Badge */}
                        <div className="flex items-center space-x-2 bg-gradient-to-r from-red-50 to-green-50 px-4 py-2 rounded-full border border-red-200">
                            <div className="text-2xl animate-pulse">🎄</div>
                            <span className="text-sm font-medium text-gray-700">
                                Christmas Edition
                            </span>
                        </div>

                        {/* Right side actions */}
                        <div className="flex items-center space-x-3">
                            {/* Theme Toggle - Only for logged-in users who own the QR code */}
                            {token && isOwner() && (
                                <button
                                    onClick={() => handleThemeToggle()}
                                    className="bg-gray-800 hover:bg-gray-900 text-white rounded-full p-2 transition-all duration-300 hover:scale-110 shadow-md"
                                    title={
                                        profile?.dark_theme
                                            ? "Switch to Light Mode"
                                            : "Switch to Dark Mode"
                                    }
                                >
                                    {profile?.dark_theme ? (
                                        <svg
                                            className="w-5 h-5"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                fillRule="evenodd"
                                                d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
                                                clipRule="evenodd"
                                            />
                                        </svg>
                                    ) : (
                                        <svg
                                            className="w-5 h-5"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                                        </svg>
                                    )}
                                </button>
                            )}

                            {/* Hamburger Menu */}
                            <div className="relative hamburger-menu">
                                <button
                                    onClick={() =>
                                        setShowHamburgerMenu(!showHamburgerMenu)
                                    }
                                    className="bg-gray-800 hover:bg-gray-900 text-white rounded-full p-2 transition-all duration-300 hover:scale-110 shadow-md"
                                    title="Menu"
                                >
                                    <IoIosMenu size={20} />
                                </button>

                                {/* Dropdown Menu */}
                                {showHamburgerMenu && (
                                    <div
                                        className={`absolute right-0 top-12 rounded-lg shadow-xl border min-w-[200px] z-50 transition-all duration-300 ${
                                            profile?.dark_theme
                                                ? "bg-gray-800 border-gray-600"
                                                : "bg-white border-gray-200"
                                        }`}
                                    >
                                        <div className="py-2">
                                            {token && (
                                                <Link
                                                    to="/my-qrcodes"
                                                    onClick={() =>
                                                        setShowHamburgerMenu(
                                                            false
                                                        )
                                                    }
                                                    className={`w-full text-left px-4 py-2 transition-colors flex items-center space-x-2 ${
                                                        profile?.dark_theme
                                                            ? "text-gray-200 hover:bg-gray-700"
                                                            : "text-gray-700 hover:bg-gray-100"
                                                    }`}
                                                >
                                                    <FaUser size={16} />
                                                    <span>My QR Codes</span>
                                                </Link>
                                            )}

                                            {token && (
                                                <button
                                                    onClick={handleLogout}
                                                    className={`w-full text-left px-4 py-2 transition-colors flex items-center space-x-2 ${
                                                        profile?.dark_theme
                                                            ? "text-gray-200 hover:bg-gray-700"
                                                            : "text-gray-700 hover:bg-gray-100"
                                                    }`}
                                                >
                                                    <FaSignOutAlt size={16} />
                                                    <span>Logout</span>
                                                </button>
                                            )}
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            {/* Main Content - Show setup message */}
            <div className="max-w-5xl mx-auto py-12">
                <div className="text-center mb-16 px-8">
                    <div className="text-6xl mb-4">🎄</div>
                    <h2 className="text-4xl font-bold text-gray-800 mb-2">
                        Welcome to Christmas Edition
                    </h2>
                    <p className="text-lg text-gray-600 mb-8">
                        Let's set up your beautiful Christmas ornament
                    </p>
                    <div className="animate-pulse">
                        <div className="inline-block bg-gradient-to-r from-red-500 to-green-500 text-white px-8 py-4 rounded-full text-lg font-semibold">
                            Setting up your Christmas QR code...
                        </div>
                    </div>
                </div>
            </div>
        </div>;
    }

    return (
        <div
            className={`min-h-screen transition-all duration-300 ${
                profile?.dark_theme
                    ? "bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white"
                    : "bg-gradient-to-br from-slate-50 via-white to-gray-50 text-gray-900"
            }`}
        >
            {/* Meta Tags for Social Sharing */}
            {generateMetaTags()}
            {/* Brand Navbar */}
            <nav
                className={`transition-all duration-300 ${
                    profile?.dark_theme
                        ? "bg-gray-800 shadow-lg border-b border-gray-700"
                        : "bg-white shadow-sm border-b border-gray-200"
                }`}
            >
                <div className="max-w-6xl mx-auto px-6 py-4">
                    <div className="flex items-center justify-between">
                        {/* Brand */}
                        <div>
                            <h1
                                className={`text-xl font-bold transition-colors duration-300 ${
                                    profile?.dark_theme
                                        ? "text-white"
                                        : "text-gray-800"
                                }`}
                            >
                                Living Legacy
                            </h1>
                            <p
                                className={`text-xs transition-colors duration-300 ${
                                    profile?.dark_theme
                                        ? "text-gray-300"
                                        : "text-gray-500"
                                }`}
                            >
                                Preserving Memories
                            </p>
                        </div>

                        {/* Christmas Edition Badge */}
                        <div className="flex items-center space-x-2 bg-gradient-to-r from-red-50 to-green-50 px-4 py-2 rounded-full border border-red-200">
                            <div className="text-2xl animate-pulse">🎄</div>
                            <span className="hidden sm:inline text-sm font-medium text-gray-700">
                                Christmas Edition
                            </span>
                        </div>

                        {/* Right side actions */}
                        <div className="flex items-center space-x-3">
                            {/* Share Button */}
                            <button
                                onClick={handleShare}
                                className="bg-gray-800 hover:bg-gray-900 text-white rounded-full p-2 transition-all duration-300 hover:scale-110 shadow-md"
                                title="Share Christmas Page"
                            >
                                <IoMdShare size={20} />
                            </button>

                            {/* Theme Toggle Button - Only for logged-in users who own the QR code */}
                            {token && isOwner() && (
                                <button
                                    onClick={() => handleThemeToggle()}
                                    className={`rounded-full p-2 transition-all duration-300 hover:scale-110 shadow-md ${
                                        profile?.dark_theme
                                            ? "bg-yellow-500 hover:bg-yellow-600 text-white"
                                            : "bg-gray-800 hover:bg-gray-900 text-white"
                                    }`}
                                    title={`Switch to ${
                                        profile?.dark_theme ? "light" : "dark"
                                    } theme`}
                                >
                                    {profile?.dark_theme ? "☀️" : "🌙"}
                                </button>
                            )}

                            {/* Hamburger Menu */}
                            <div className="relative hamburger-menu">
                                <button
                                    onClick={() =>
                                        setShowHamburgerMenu(!showHamburgerMenu)
                                    }
                                    className="bg-gray-800 hover:bg-gray-900 text-white rounded-full p-2 transition-all duration-300 hover:scale-110 shadow-md"
                                    title="Menu"
                                >
                                    <IoIosMenu size={20} />
                                </button>

                                {/* Dropdown Menu */}
                                {showHamburgerMenu && (
                                    <div
                                        className={`absolute right-0 top-12 rounded-lg shadow-xl border min-w-[200px] z-50 transition-all duration-300 ${
                                            profile?.dark_theme
                                                ? "bg-gray-800 border-gray-600"
                                                : "bg-white border-gray-200"
                                        }`}
                                    >
                                        <div className="py-2">
                                            {!token && (
                                                <button
                                                    onClick={() => {
                                                        setShowHamburgerMenu(
                                                            false
                                                        );
                                                        setOpenLoginModal(true);
                                                    }}
                                                    className={`w-full text-left px-4 py-2 transition-colors flex items-center space-x-2 ${
                                                        profile?.dark_theme
                                                            ? "text-gray-200 hover:bg-gray-700"
                                                            : "text-gray-700 hover:bg-gray-100"
                                                    }`}
                                                >
                                                    <FaUser size={16} />
                                                    <span>Login</span>
                                                </button>
                                            )}

                                            {token && (
                                                <Link
                                                    to="/my-qrcodes"
                                                    onClick={() =>
                                                        setShowHamburgerMenu(
                                                            false
                                                        )
                                                    }
                                                    className={`w-full text-left px-4 py-2 transition-colors flex items-center space-x-2 ${
                                                        profile?.dark_theme
                                                            ? "text-gray-200 hover:bg-gray-700"
                                                            : "text-gray-700 hover:bg-gray-100"
                                                    }`}
                                                >
                                                    <FaUser size={16} />
                                                    <span>My QR Codes</span>
                                                </Link>
                                            )}

                                            {token && (
                                                <button
                                                    onClick={handleLogout}
                                                    className={`w-full text-left px-4 py-2 transition-colors flex items-center space-x-2 ${
                                                        profile?.dark_theme
                                                            ? "text-gray-200 hover:bg-gray-700"
                                                            : "text-gray-700 hover:bg-gray-100"
                                                    }`}
                                                >
                                                    <FaSignOutAlt size={16} />
                                                    <span>Logout</span>
                                                </button>
                                            )}

                                            {token && isOwner() && (
                                                <button
                                                    onClick={() => {
                                                        setShowHamburgerMenu(
                                                            false
                                                        );
                                                        setIsEditing(true);
                                                    }}
                                                    className={`w-full text-left px-4 py-2 transition-colors flex items-center space-x-2 ${
                                                        profile?.dark_theme
                                                            ? "text-gray-200 hover:bg-gray-700"
                                                            : "text-gray-700 hover:bg-gray-100"
                                                    }`}
                                                >
                                                    <FaEdit size={16} />
                                                    <span>Edit Profile</span>
                                                </button>
                                            )}

                                            {token && isOwner() && (
                                                <button
                                                    onClick={() => {
                                                        setShowHamburgerMenu(
                                                            false
                                                        );
                                                        setShowPhotoUpload(
                                                            true
                                                        );
                                                    }}
                                                    className={`w-full text-left px-4 py-2 transition-colors flex items-center space-x-2 ${
                                                        profile?.dark_theme
                                                            ? "text-gray-200 hover:bg-gray-700"
                                                            : "text-gray-700 hover:bg-gray-100"
                                                    }`}
                                                >
                                                    <FaUpload size={16} />
                                                    <span>Add Photos</span>
                                                </button>
                                            )}
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            {/* Main Content */}
            <div className="max-w-5xl mx-auto py-12">
                {/* Profile Section */}
                <div className="text-center mb-16 px-8">
                    {/* Profile Name - no photo needed */}
                    {profile?.name && (
                        <div className="mb-8">
                            <h2
                                className={`text-4xl font-bold mb-2 transition-colors duration-300 ${
                                    profile?.dark_theme
                                        ? "text-white"
                                        : "text-gray-800"
                                }`}
                            >
                                {profile?.name}
                            </h2>
                            {profile?.title && (
                                <p
                                    className={`text-lg transition-colors duration-300 ${
                                        profile?.dark_theme
                                            ? "text-gray-300"
                                            : "text-gray-600"
                                    }`}
                                >
                                    {profile?.title}
                                </p>
                            )}
                        </div>
                    )}

                    {profile?.bio && (
                        <div className="max-w-3xl mx-auto">
                            <div
                                className={`rounded-2xl p-8 border transition-all duration-300 ${
                                    profile?.dark_theme
                                        ? "bg-gradient-to-r from-gray-800 to-gray-700 border-gray-600"
                                        : "bg-gradient-to-r from-slate-50 to-gray-50 border-gray-200"
                                }`}
                            >
                                <p
                                    className={`text-xl leading-relaxed font-medium transition-colors duration-300 ${
                                        profile?.dark_theme
                                            ? "text-gray-200"
                                            : "text-gray-700"
                                    }`}
                                    style={{ whiteSpace: "pre-wrap" }}
                                >
                                    {profile.bio}
                                </p>
                            </div>
                        </div>
                    )}

                    {/* Spotify Player */}
                    {profile?.spotify && (
                        <div className="max-w-3xl mx-auto mt-8">
                            <div
                                className={`rounded-2xl p-6 border transition-all duration-300 ${
                                    profile?.dark_theme
                                        ? "bg-gradient-to-r from-green-900 to-emerald-900 border-green-700"
                                        : "bg-gradient-to-r from-green-50 to-emerald-50 border-green-200"
                                }`}
                            >
                                <div className="flex items-center justify-between mb-4">
                                    <div className="flex items-center space-x-3">
                                        <div className="text-2xl">🎵</div>
                                        <h3
                                            className={`text-xl font-semibold transition-colors duration-300 ${
                                                profile?.dark_theme
                                                    ? "text-white"
                                                    : "text-gray-800"
                                            }`}
                                        >
                                            {profile?.name}'s Music
                                        </h3>
                                    </div>
                                </div>

                                <div className="w-full">
                                    <Spotify
                                        wide
                                        className="w-full"
                                        link={profile?.spotify}
                                    />
                                </div>
                            </div>
                        </div>
                    )}
                </div>

                {/* Photo Slider */}
                {photos.length > 0 && (
                    <div
                        className={`rounded-3xl shadow-2xl overflow-hidden mb-12 w-full max-w-6xl mx-auto transition-all duration-300 ${
                            profile?.dark_theme ? "bg-gray-800" : "bg-white"
                        }`}
                    >
                        <div className="relative">
                            {/* Main Photo Display */}
                            <div
                                className={`relative min-h-[400px] md:min-h-[500px] lg:min-h-[600px] max-h-[600px] transition-all duration-300 flex items-center justify-center cursor-pointer ${
                                    profile?.dark_theme
                                        ? "bg-gradient-to-br from-gray-800 to-gray-900"
                                        : "bg-gradient-to-br from-gray-50 to-gray-100"
                                }`}
                                onClick={() =>
                                    openFullscreenImage(
                                        photos[currentPhotoIndex]
                                    )
                                }
                            >
                                <img
                                    src={photos[currentPhotoIndex]?.image}
                                    alt={`Memory ${currentPhotoIndex + 1}`}
                                    className="max-w-full max-h-full object-contain"
                                    loading="lazy"
                                    style={{
                                        width: "auto",
                                        height: "auto",
                                        maxWidth: "100%",
                                        maxHeight: "100%",
                                    }}
                                />

                                {/* Gradient Overlay for Better Text Visibility */}
                                <div className="absolute inset-0 bg-gradient-to-t from-black/20 via-transparent to-transparent pointer-events-none"></div>

                                {/* Navigation Arrows */}
                                {photos.length > 1 && (
                                    <>
                                        <button
                                            onClick={prevPhoto}
                                            className={`absolute left-6 top-1/2 transform -translate-y-1/2 rounded-full p-3 shadow-lg transition-all duration-300 hover:scale-110 backdrop-blur-sm ${
                                                profile?.dark_theme
                                                    ? "bg-gray-800/90 hover:bg-gray-700 text-white hover:text-red-400"
                                                    : "bg-white/90 hover:bg-white text-gray-800 hover:text-red-500"
                                            }`}
                                        >
                                            <FaChevronLeft size={20} />
                                        </button>
                                        <button
                                            onClick={nextPhoto}
                                            className={`absolute right-6 top-1/2 transform -translate-y-1/2 rounded-full p-3 shadow-lg transition-all duration-300 hover:scale-110 backdrop-blur-sm ${
                                                profile?.dark_theme
                                                    ? "bg-gray-800/90 hover:bg-gray-700 text-white hover:text-red-400"
                                                    : "bg-white/90 hover:bg-white text-gray-800 hover:text-red-500"
                                            }`}
                                        >
                                            <FaChevronRight size={20} />
                                        </button>
                                    </>
                                )}

                                {/* Photo Counter */}
                                {photos.length > 1 && (
                                    <div
                                        className={`absolute bottom-6 left-1/2 transform -translate-x-1/2 backdrop-blur-sm px-4 py-2 rounded-full text-sm font-medium shadow-lg transition-all duration-300 ${
                                            profile?.dark_theme
                                                ? "bg-gray-800/90 text-white"
                                                : "bg-white/90 text-gray-800"
                                        }`}
                                    >
                                        {currentPhotoIndex + 1} /{" "}
                                        {photos.length}
                                    </div>
                                )}
                            </div>

                            {/* Photo Thumbnails */}
                            {photos.length > 1 && (
                                <div
                                    className={`mt-3 p-3 transition-all duration-300 ${
                                        profile?.dark_theme
                                            ? "bg-gradient-to-r from-gray-800 to-gray-900"
                                            : "bg-gradient-to-r from-gray-50 to-gray-100"
                                    }`}
                                >
                                    <div className="flex justify-center">
                                        <div className="flex space-x-2 md:space-x-4 overflow-x-auto max-w-full px-4 pb-2">
                                            {photos.map((photo, index) => (
                                                <div
                                                    key={index}
                                                    className="relative flex-shrink-0 group"
                                                >
                                                    <button
                                                        onClick={() =>
                                                            setCurrentPhotoIndex(
                                                                index
                                                            )
                                                        }
                                                        className={`w-16 h-16 md:w-20 md:h-20 rounded-xl overflow-hidden border-3 transition-all duration-300 transform hover:scale-105 ${
                                                            index ===
                                                            currentPhotoIndex
                                                                ? "border-red-500 ring-4 ring-red-200 shadow-lg"
                                                                : `${
                                                                      profile?.dark_theme
                                                                          ? "border-gray-600 hover:border-red-400"
                                                                          : "border-gray-200 hover:border-red-300"
                                                                  } hover:shadow-md`
                                                        }`}
                                                    >
                                                        <img
                                                            src={photo.image}
                                                            alt={`Thumbnail ${
                                                                index + 1
                                                            }`}
                                                            className="w-full h-full object-cover object-center"
                                                            loading="lazy"
                                                        />
                                                    </button>

                                                    {token && (
                                                        <button
                                                            onClick={() =>
                                                                handleDeletePhoto(
                                                                    photo.uuid
                                                                )
                                                            }
                                                            className="absolute -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full p-2 text-xs transition-all duration-300 hover:scale-110 shadow-lg z-50"
                                                            title="Delete photo"
                                                        >
                                                            <FaTrash
                                                                size={10}
                                                            />
                                                        </button>
                                                    )}

                                                    {/* Active indicator */}
                                                    {index ===
                                                        currentPhotoIndex && (
                                                        <div className="absolute -bottom-1 left-1/2 transform -translate-x-1/2 w-2 h-2 bg-red-500 rounded-full"></div>
                                                    )}
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                )}

                {/* Action Buttons - Only for logged-in users */}

                {token && (
                    <div className="fixed bottom-6 right-6 flex flex-col space-y-3">
                        {/* Photo Upload Button */}
                        <button
                            onClick={() => setShowPhotoUpload(true)}
                            className="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-full p-4 shadow-lg transition-all duration-300 ease-in-out hover:scale-105 active:scale-95"
                            title="Add Christmas Photos"
                        >
                            <FaUpload size={20} />
                        </button>

                        {/* Edit Button */}
                        <button
                            onClick={() => setIsEditing(true)}
                            className="bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-full p-4 shadow-lg transition-all duration-300 ease-in-out hover:scale-105 active:scale-95"
                            title="Edit Christmas Page"
                        >
                            <FaEdit size={20} />
                        </button>

                        {/* Share Button */}
                        <button
                            onClick={handleShare}
                            className="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-full p-4 shadow-lg transition-all duration-300 ease-in-out hover:scale-105 active:scale-95"
                            title="Share Christmas Page"
                        >
                            <FaShare size={20} />
                        </button>
                    </div>
                )}
            </div>

            {/* Christmas Edit Modal */}
            <ChristmasEditModal
                isOpen={isEditing}
                onClose={() => setIsEditing(false)}
                profile={profile}
                onSave={handleUpdateProfile}
                isDarkTheme={profile?.dark_theme}
            />

            {/* Photo Upload Modal */}
            {showPhotoUpload && (
                <div
                    className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
                    onClick={closePhotoUploadModal}
                >
                    <div
                        className={`rounded-2xl p-6 max-w-2xl w-full max-h-[90vh] overflow-y-auto transition-all duration-300 ${
                            profile?.dark_theme ? "bg-gray-800" : "bg-white"
                        }`}
                        onClick={(e) => e.stopPropagation()}
                    >
                        <div className="flex items-center justify-between mb-4">
                            <div className="flex items-center space-x-3">
                                <div className="text-2xl">📸</div>
                                <h3
                                    className={`text-xl font-bold transition-colors duration-300 ${
                                        profile?.dark_theme
                                            ? "text-white"
                                            : "text-gray-900"
                                    }`}
                                >
                                    Add Christmas Photos
                                </h3>
                            </div>
                            <button
                                onClick={closePhotoUploadModal}
                                className={`transition-colors p-2 rounded-full ${
                                    profile?.dark_theme
                                        ? "text-gray-400 hover:text-gray-300 hover:bg-gray-700"
                                        : "text-gray-400 hover:text-gray-600 hover:bg-gray-100"
                                }`}
                            >
                                <IoClose size={20} />
                            </button>
                        </div>

                        <div
                            className={`border rounded-lg p-4 mb-6 transition-all duration-300 ${
                                profile?.dark_theme
                                    ? "bg-blue-900 border-blue-600"
                                    : "bg-blue-50 border-blue-200"
                            }`}
                        >
                            <div className="flex items-start space-x-2">
                                <div className="text-blue-500 mt-0.5">ℹ️</div>
                                <div
                                    className={`text-sm transition-colors duration-300 ${
                                        profile?.dark_theme
                                            ? "text-blue-200"
                                            : "text-blue-800"
                                    }`}
                                >
                                    <p className="font-medium mb-1">
                                        Upload Guidelines:
                                    </p>
                                    <ul className="space-y-1 text-xs">
                                        <li>
                                            • Maximum 20 photos total per
                                            Christmas profile
                                        </li>
                                        <li>
                                            • Each photo max 50MB (increased
                                            limit)
                                        </li>
                                        <li>
                                            • Supported formats: JPG, PNG, GIF
                                        </li>
                                        <li>
                                            • Photos will be automatically
                                            optimized
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {!uploadPreview ? (
                            <>
                                <input
                                    type="file"
                                    multiple
                                    accept="image/*"
                                    onChange={(e) =>
                                        handleFileSelection(e.target.files)
                                    }
                                    className={`w-full p-3 border-2 border-dashed rounded-lg mb-4 transition-colors cursor-pointer ${
                                        profile?.dark_theme
                                            ? "border-gray-600 hover:border-green-400 bg-gray-700 text-white"
                                            : "border-gray-300 hover:border-green-400 bg-white text-gray-900"
                                    }`}
                                    disabled={isUploading}
                                />

                                <div className="flex space-x-3">
                                    <button
                                        onClick={closePhotoUploadModal}
                                        className={`flex-1 py-2 px-4 rounded-lg transition-colors ${
                                            profile?.dark_theme
                                                ? "bg-gray-600 text-white hover:bg-gray-700"
                                                : "bg-gray-500 text-white hover:bg-gray-600"
                                        }`}
                                    >
                                        Cancel
                                    </button>
                                </div>
                            </>
                        ) : (
                            <>
                                {/* Preview Selected Files */}
                                <div className="mb-6">
                                    <h4
                                        className={`text-lg font-semibold mb-3 transition-colors duration-300 ${
                                            profile?.dark_theme
                                                ? "text-white"
                                                : "text-gray-900"
                                        }`}
                                    >
                                        Selected Photos ({selectedFiles.length})
                                    </h4>
                                    <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
                                        {selectedFiles.map((file, index) => (
                                            <div
                                                key={index}
                                                className="relative group"
                                            >
                                                <div
                                                    className={`aspect-square rounded-lg overflow-hidden transition-colors duration-300 ${
                                                        profile?.dark_theme
                                                            ? "bg-gray-700"
                                                            : "bg-gray-100"
                                                    }`}
                                                >
                                                    <img
                                                        src={URL.createObjectURL(
                                                            file
                                                        )}
                                                        alt={`Preview ${
                                                            index + 1
                                                        }`}
                                                        className="w-full h-full object-cover"
                                                    />
                                                </div>

                                                {/* File Info */}
                                                <div className="absolute bottom-0 left-0 right-0 bg-black/70 text-white p-2 text-xs">
                                                    <p className="truncate">
                                                        {file.name}
                                                    </p>
                                                    <p>
                                                        {(
                                                            file.size /
                                                            (1024 * 1024)
                                                        ).toFixed(2)}{" "}
                                                        MB
                                                    </p>
                                                </div>

                                                {/* Action Buttons */}
                                                <div className="absolute top-2 right-2 flex space-x-1">
                                                    <button
                                                        onClick={() =>
                                                            removeSelectedFile(
                                                                index
                                                            )
                                                        }
                                                        className="bg-red-500 hover:bg-red-600 text-white rounded-full p-1 text-xs transition-colors z-50"
                                                        title="Remove photo"
                                                    >
                                                        <FaTrash size={10} />
                                                    </button>
                                                </div>

                                                {/* Replace Button */}
                                                <div className="absolute bottom-2 left-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <label className="bg-blue-500 hover:bg-blue-600 text-white rounded-full p-1 text-xs transition-colors cursor-pointer">
                                                        <input
                                                            type="file"
                                                            accept="image/*"
                                                            onChange={(e) =>
                                                                e.target
                                                                    .files[0] &&
                                                                replaceSelectedFile(
                                                                    index,
                                                                    e.target
                                                                        .files[0]
                                                                )
                                                            }
                                                            className="hidden"
                                                        />
                                                        <TbPencil size={10} />
                                                    </label>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>

                                {/* Upload Progress */}
                                {isUploading && (
                                    <div className="text-center mb-4">
                                        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-green-500 mx-auto mb-2"></div>
                                        <p
                                            className={`transition-colors duration-300 ${
                                                profile?.dark_theme
                                                    ? "text-gray-400"
                                                    : "text-gray-600"
                                            }`}
                                        >
                                            Uploading photos...
                                        </p>
                                    </div>
                                )}

                                {/* Upload Errors */}
                                {uploadErrors.length > 0 && (
                                    <div className="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                                        <div className="flex items-start space-x-2">
                                            <div className="text-red-500 mt-0.5">
                                                ⚠️
                                            </div>
                                            <div className="text-sm text-red-800">
                                                <p className="font-medium mb-2">
                                                    Upload Errors:
                                                </p>
                                                <ul className="space-y-1 text-xs">
                                                    {uploadErrors.map(
                                                        (error, index) => (
                                                            <li
                                                                key={index}
                                                                className="text-red-700"
                                                            >
                                                                • {error}
                                                            </li>
                                                        )
                                                    )}
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                )}

                                {/* Action Buttons */}
                                <div className="flex space-x-3">
                                    <button
                                        onClick={() => {
                                            setUploadPreview(false);
                                            setSelectedFiles([]);
                                        }}
                                        className={`flex-1 py-2 px-4 rounded-lg transition-colors ${
                                            profile?.dark_theme
                                                ? "bg-gray-600 text-white hover:bg-gray-700"
                                                : "bg-gray-500 text-white hover:bg-gray-600"
                                        }`}
                                        disabled={isUploading}
                                    >
                                        Back
                                    </button>
                                    <button
                                        onClick={confirmUpload}
                                        className="flex-1 bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50"
                                        disabled={
                                            isUploading ||
                                            selectedFiles.length === 0
                                        }
                                    >
                                        {isUploading
                                            ? "Uploading..."
                                            : `Upload ${
                                                  selectedFiles.length
                                              } Photo${
                                                  selectedFiles.length > 1
                                                      ? "s"
                                                      : ""
                                              }`}
                                    </button>
                                </div>
                            </>
                        )}
                    </div>
                </div>
            )}

            {/* Share Modal */}
            {showShareModal && (
                <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
                    <div
                        className={`rounded-2xl p-6 max-w-sm mx-4 transition-all duration-300 ${
                            profile?.dark_theme ? "bg-gray-800" : "bg-white"
                        }`}
                    >
                        <h3
                            className={`text-xl font-bold mb-4 transition-colors duration-300 ${
                                profile?.dark_theme
                                    ? "text-white"
                                    : "text-gray-900"
                            }`}
                        >
                            Share Memories
                        </h3>
                        <p
                            className={`mb-6 transition-colors duration-300 ${
                                profile?.dark_theme
                                    ? "text-gray-300"
                                    : "text-gray-600"
                            }`}
                        >
                            Share this beautiful Christmas tribute with family
                            and friends.
                        </p>
                        <div className="flex space-x-3">
                            <button
                                onClick={handleShare}
                                className="flex-1 bg-red-600 text-white py-2 px-4 rounded-lg hover:bg-red-700 transition-colors"
                            >
                                Share
                            </button>
                            <button
                                onClick={() => setShowShareModal(false)}
                                className={`flex-1 py-2 px-4 rounded-lg transition-colors ${
                                    profile?.dark_theme
                                        ? "bg-gray-700 text-gray-200 hover:bg-gray-600"
                                        : "bg-gray-200 text-gray-800 hover:bg-gray-300"
                                }`}
                            >
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* Christmas Setup Modal - New Advanced Version */}
            {console.log(
                "Christmas component - Rendering setup modal with open:",
                showSetupModal
            )}
            <ChristmasSetupModal
                open={showSetupModal}
                setOpen={setShowSetupModal}
                refetch={refetch}
                setOpenSuccessModal={setShowSuccessModal}
                token={token}
                setSetupModalManuallyClosed={setSetupModalManuallyClosed}
                qrCodeStatus={data?.status}
                needsLinking={data?.status === 201}
                isDarkTheme={profile?.dark_theme}
            />

            {/* Christmas Success Modal */}
            <ChristmasSuccessModal
                open={showSuccessModal}
                setOpen={setShowSuccessModal}
                setOpenUploadPhotosModal={setShowPhotoUpload}
                isDarkTheme={profile?.dark_theme}
            />

            {/* Login Modal */}
            <LoginModal
                open={openLoginModal}
                setOpen={setOpenLoginModal}
                refetch={refetch}
            />

            {/* Empty Modal for visitors when QR code needs setup */}
            <EmptyModal
                open={showEmptyModal}
                setOpen={setShowEmptyModal}
                setOpenLoginModal={setOpenLoginModal}
                setOpenRegisterModal={setOpenRegisterModal}
                setIsOpen={() => {}} // We don't have tour in Christmas version
                refetch={refetch}
            />

            {/* Register Modal */}
            <RegisterModal
                open={openRegisterModal}
                setOpen={setOpenRegisterModal}
                setOpenLoginModal={setOpenLoginModal}
                refetch={refetch}
            />

            {/* Full-Screen Image Viewer */}
            {fullscreenImage && (
                <div className="fixed inset-0 bg-black/90 flex items-center justify-center z-[60] p-4">
                    <div className="relative w-full h-full flex items-center justify-center">
                        {/* Close Button */}
                        <button
                            onClick={closeFullscreenImage}
                            className="absolute top-4 right-4 z-10 bg-black/50 hover:bg-black/70 text-white rounded-full p-3 transition-all duration-300 hover:scale-110"
                        >
                            <IoClose size={24} />
                        </button>

                        {/* Main Image */}
                        <img
                            src={fullscreenImage.image}
                            alt="Full screen memory"
                            className="max-w-full max-h-full object-contain"
                            style={{
                                width: "auto",
                                height: "auto",
                                maxWidth: "100%",
                                maxHeight: "100%",
                            }}
                        />

                        {/* Navigation Arrows */}
                        {photos.length > 1 && (
                            <>
                                <button
                                    onClick={prevFullscreenPhoto}
                                    className="absolute left-4 top-1/2 transform -translate-y-1/2 bg-black/50 hover:bg-black/70 text-white rounded-full p-4 transition-all duration-300 hover:scale-110"
                                >
                                    <FaChevronLeft size={24} />
                                </button>
                                <button
                                    onClick={nextFullscreenPhoto}
                                    className="absolute right-4 top-1/2 transform -translate-y-1/2 bg-black/50 hover:bg-black/70 text-white rounded-full p-4 transition-all duration-300 hover:scale-110"
                                >
                                    <FaChevronRight size={24} />
                                </button>
                            </>
                        )}

                        {/* Photo Counter */}
                        {photos.length > 1 && (
                            <div className="absolute bottom-4 left-1/2 transform -translate-x-1/2 bg-black/50 text-white px-4 py-2 rounded-full text-sm font-medium">
                                {photos.findIndex(
                                    (p) => p.uuid === fullscreenImage.uuid
                                ) + 1}{" "}
                                / {photos.length}
                            </div>
                        )}

                        {/* Keyboard Navigation Hint */}
                        <div className="absolute bottom-4 right-4 bg-black/50 text-white px-3 py-1 rounded text-xs opacity-70">
                            Use ← → keys or click arrows
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};
