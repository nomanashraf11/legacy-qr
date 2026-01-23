import { useState, useEffect } from "react";
import { useParams } from "react-router-dom";
import { toast } from "react-toastify";
import { Spotify } from "react-spotify-embed";

import { FaUser, FaHeart, FaTimes, FaUpload } from "react-icons/fa";
import { API_BASE_URL } from "../config";
import { objectToFormData } from "../utils";

export const ChristmasSetupModal = ({
    open,
    setOpen,
    refetch,
    setOpenSuccessModal,
    token,
    setSetupModalManuallyClosed,
    qrCodeStatus,
    needsLinking,
    isDarkTheme = false, // Default to light theme
}) => {
    const { id } = useParams();
    const [formData, setFormData] = useState({
        name: "",
        bio: "",
    });
    const [selectedFiles, setSelectedFiles] = useState([]);
    const [uploadPreview, setUploadPreview] = useState(false);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [currentStep, setCurrentStep] = useState(1);
    const [spotifyLink, setSpotifyLink] = useState("");
    const [showSpotifyPreview, setShowSpotifyPreview] = useState(false);

    // Spotify validation regex
    const spotifyRegex =
        /^(https?:\/\/)?(www\.)?open.spotify.com\/(?:track|playlist)\/[a-zA-Z0-9]+/;

    const isValidSpotifyUrl = (url) => {
        if (!url.trim()) return true; // Empty is valid (optional)
        return spotifyRegex.test(url);
    };

    const handleInputChange = (e) => {
        const { name, value } = e.target;
        setFormData((prev) => ({
            ...prev,
            [name]: value,
        }));
    };

    const handleSpotifyLinkChange = (e) => {
        const value = e.target.value;
        setSpotifyLink(value);

        // Auto-show preview when valid URL is entered
        if (value.trim() && isValidSpotifyUrl(value)) {
            setShowSpotifyPreview(true);
        } else {
            setShowSpotifyPreview(false);
        }
    };

    const handleFileSelection = (files) => {
        const fileArray = Array.from(files);

        // Validate file count FIRST
        if (selectedFiles.length + fileArray.length > 5) {
            toast.error("Maximum 5 photos allowed");
            return;
        }

        // Validate file sizes
        const validFiles = fileArray.filter((file) => {
            if (file.size > 20 * 1024 * 1024) {
                toast.error(`${file.name} is too large. Max size: 20MB`);
                return false;
            }
            return true;
        });

        setSelectedFiles((prev) => [...prev, ...validFiles]);
    };

    const removeSelectedFile = (index) => {
        setSelectedFiles((prev) => prev.filter((_, i) => i !== index));
    };

    // Profile picture functions removed - no longer needed for Christmas edition

    const nextStep = () => {
        // Step 1 validation
        if (currentStep === 1) {
            if (!formData.name.trim()) {
                toast.error("Please enter a name");
                return;
            }
            if (formData.name.trim().length < 2) {
                toast.error("Name must be at least 2 characters long");
                return;
            }
        }

        // Step 2 validation (bio is now optional)
        if (currentStep === 2) {
            // Bio is optional - no validation required
            // User can skip this step if they want
        }

        // Step 3 validation (Spotify is optional)
        if (currentStep === 3) {
            // Spotify is optional, but if provided, it must be valid
            if (spotifyLink.trim() && !isValidSpotifyUrl(spotifyLink)) {
                toast.error("Please enter a valid Spotify URL");
                return;
            }
        }

        setCurrentStep((prev) => prev + 1);
    };

    const prevStep = () => {
        setCurrentStep((prev) => prev - 1);
    };

    const handleCloseModal = () => {
        console.log("handleCloseModal called, current open state:", open);

        // Reset all form state when closing
        setFormData({
            name: "",
            bio: "",
        });
        // setProfilePicture(null); // This state is removed, so this line is no longer needed
        setSelectedFiles([]);
        setCurrentStep(1);
        setUploadPreview(false);
        setSpotifyLink("");
        setShowSpotifyPreview(false);

        // Force close the modal
        setOpen(false);

        // Prevent modal from reopening automatically
        if (setSetupModalManuallyClosed) {
            setSetupModalManuallyClosed(true);
        }

        // Additional cleanup
        document.body.style.overflow = "auto";

        console.log("Modal should now be closed, open state set to false");
    };

    // Force close function as fallback
    const forceClose = () => {
        console.log("forceClose called");
        setOpen(false);

        // Prevent modal from reopening automatically
        if (setSetupModalManuallyClosed) {
            setSetupModalManuallyClosed(true);
        }

        document.body.style.overflow = "auto";
        // Reset form state
        setFormData({
            name: "",
            bio: "",
        });
        // setProfilePicture(null); // This state is removed, so this line is no longer needed
        setSelectedFiles([]);
        setCurrentStep(1);
        setUploadPreview(false);
        setSpotifyLink("");
        setShowSpotifyPreview(false);
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

    // Force modal close when open state becomes false
    useEffect(() => {
        console.log("useEffect triggered, open state:", open);

        if (!open) {
            console.log("Modal should be closing, performing cleanup...");
            // Force close the modal when open becomes false
            document.body.style.overflow = "auto";
            // Reset any modal-specific styles
            setCurrentStep(1);
            setUploadPreview(false);
            console.log("Cleanup completed");
        }
    }, [open]);

    const handleSubmit = async () => {
        if (!formData.name.trim()) {
            toast.error("Name is required");
            return;
        }

        if (spotifyLink.trim() && !isValidSpotifyUrl(spotifyLink)) {
            toast.error("Please enter a valid Spotify URL");
            return;
        }

        // Check if user is authenticated
        if (!token) {
            toast.error("Please login to create a profile");
            return;
        }

        setIsSubmitting(true);
        try {
            // Step 1: Link QR code ONLY if it's a new QR code (status 201)
            if (needsLinking) {
                console.log("Linking new QR code to user account...");
                const linkResponse = await fetch(`${API_BASE_URL}/link/${id}`, {
                    method: "POST",
                    headers: {
                        Authorization: `Bearer ${token}`,
                        Accept: "application/json",
                    },
                });

                if (!linkResponse.ok) {
                    console.error("QR code linking failed:", {
                        status: linkResponse.status,
                        statusText: linkResponse.statusText,
                    });
                    throw new Error(
                        `Failed to link QR code (${linkResponse.status})`
                    );
                }

                const linkResult = await linkResponse.json();
                console.log("QR code linked successfully:", linkResult);
            } else {
                console.log(
                    "QR code already linked, proceeding to create profile..."
                );
            }

            // Step 2: Create the profile
            console.log("Creating Christmas profile...");
            const profileFormData = new FormData();
            profileFormData.append("name", formData.name);
            profileFormData.append("bio", formData.bio);

            // Create simple Christmas-themed images for backend requirements
            const createChristmasImage = (width, height, text) => {
                const canvas = document.createElement("canvas");
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext("2d");

                // Create a Christmas-themed background (red and green)
                const gradient = ctx.createLinearGradient(0, 0, width, height);
                gradient.addColorStop(0, "#dc2626"); // Red
                gradient.addColorStop(1, "#16a34a"); // Green
                ctx.fillStyle = gradient;
                ctx.fillRect(0, 0, width, height);

                // Add Christmas tree emoji
                ctx.font = "48px Arial";
                ctx.textAlign = "center";
                ctx.textBaseline = "middle";
                ctx.fillText("🎄", width / 2, height / 2);

                // Add text below
                ctx.font = "16px Arial";
                ctx.fillStyle = "white";
                ctx.fillText(text, width / 2, height / 2 + 40);

                return new Promise((resolve) => {
                    canvas.toBlob((blob) => {
                        resolve(
                            new File(
                                [blob],
                                `christmas-${text.toLowerCase()}.png`,
                                { type: "image/png" }
                            )
                        );
                    }, "image/png");
                });
            };

            // Generate profile and cover pictures
            const profilePicture = await createChristmasImage(
                200,
                200,
                "Profile"
            );
            const coverPicture = await createChristmasImage(400, 200, "Cover");

            profileFormData.append("profile_picture", profilePicture);
            profileFormData.append("cover_picture", coverPicture);

            // Add dummy date for backend requirements
            profileFormData.append("dob", "2000-01-01"); // Default date
            profileFormData.append("dod", ""); // Empty for living person

            if (spotifyLink.trim()) {
                profileFormData.append("spotify", spotifyLink.trim());
            }

            // Upload profile using the correct endpoint (same as legacy)
            const profileResponse = await fetch(
                `${API_BASE_URL}/${id}/add_bio?is_legacy=true`,
                {
                    method: "POST",
                    headers: {
                        Authorization: `Bearer ${token}`,
                        Accept: "application/json",
                    },
                    body: profileFormData,
                }
            );

            if (!profileResponse.ok) {
                console.error("Profile creation failed:", {
                    status: profileResponse.status,
                    statusText: profileResponse.statusText,
                    url: profileResponse.url,
                });

                if (profileResponse.status === 302) {
                    throw new Error(
                        "Authentication failed - please login again"
                    );
                } else if (profileResponse.status === 401) {
                    throw new Error("Unauthorized - please check your login");
                } else if (profileResponse.status === 422) {
                    throw new Error(
                        "Validation error - please check your input"
                    );
                } else {
                    throw new Error(
                        `Failed to create profile (${profileResponse.status})`
                    );
                }
            }

            // Upload photos if any
            if (selectedFiles.length > 0) {
                // Upload photos one by one using the correct endpoint
                for (let i = 0; i < selectedFiles.length; i++) {
                    const photoFormData = new FormData();
                    photoFormData.append("image", selectedFiles[i]);

                    try {
                        const photoResponse = await fetch(
                            `${API_BASE_URL}/${id}/add_photo`,
                            {
                                method: "POST",
                                headers: {
                                    Authorization: `Bearer ${token}`,
                                },
                                body: photoFormData,
                            }
                        );

                        if (!photoResponse.ok) {
                            console.warn(`Photo ${i + 1} upload failed`);
                        }
                    } catch (error) {
                        console.warn(`Photo ${i + 1} upload error:`, error);
                    }
                }
            }

            toast.success("Christmas profile created successfully!");

            // Reset form state
            setFormData({
                name: "",
                bio: "",
            });
            // setProfilePicture(null); // This state is removed, so this line is no longer needed
            setSelectedFiles([]);
            setCurrentStep(1);
            setUploadPreview(false);
            setSpotifyLink("");
            setShowSpotifyPreview(false);

            // Close modal and show success
            setOpen(false);

            // Reset the manually closed flag since setup was completed
            if (setSetupModalManuallyClosed) {
                setSetupModalManuallyClosed(false);
            }

            setOpenSuccessModal(true);
            refetch();
        } catch (error) {
            console.error("Setup error:", error);
            toast.error("Failed to create profile. Please try again.");
        } finally {
            setIsSubmitting(false);
        }
    };

    console.log("ChristmasSetupModal - Component called with open:", open);
    if (!open) return null;

    console.log("ChristmasSetupModal - Rendering modal content");
    return (
        <div
            className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
            onClick={handleCloseModal}
        >
            <div
                className={`rounded-3xl max-w-2xl w-full max-h-[90vh] overflow-y-auto ChristmasModal transition-all duration-300 ${
                    isDarkTheme ? "bg-gray-800" : "bg-white"
                }`}
                onClick={(e) => e.stopPropagation()}
            >
                {/* Header */}
                <div className="bg-gradient-to-r from-red-600 to-green-600 text-white p-6 rounded-t-3xl relative shadow-lg">
                    <button
                        onClick={() => {
                            handleCloseModal();
                            // Fallback: force close if regular close doesn't work
                            setTimeout(() => {
                                if (open) {
                                    console.log(
                                        "Regular close failed, using force close"
                                    );
                                    forceClose();
                                }
                            }, 100);
                        }}
                        className="absolute top-4 right-4 text-white hover:text-red-200 transition-colors bg-black/20 rounded-full p-2 hover:bg-black/30"
                    >
                        <FaTimes size={20} />
                    </button>
                    <div className="text-center">
                        <div className="text-4xl mb-2">🎄</div>
                        <h2 className="text-2xl font-bold text-white">
                            Christmas Memories Setup
                        </h2>
                        <p className="text-sm text-white/95 font-medium">
                            Create your beautiful Christmas tribute
                        </p>
                    </div>
                </div>

                {/* Progress Bar */}
                <div
                    className={`px-6 py-4 border-b transition-all duration-300 ${
                        isDarkTheme
                            ? "bg-gray-800 border-gray-600"
                            : "bg-gray-100 border-gray-200"
                    }`}
                >
                    <div className="flex items-center justify-between mb-2">
                        <span
                            className={`text-sm font-semibold transition-colors duration-300 ${
                                isDarkTheme ? "text-white" : "text-gray-800"
                            }`}
                        >
                            Step {currentStep} of 4
                        </span>
                        <span
                            className={`text-sm font-medium transition-colors duration-300 ${
                                isDarkTheme ? "text-gray-300" : "text-gray-700"
                            }`}
                        >
                            {Math.round((currentStep / 4) * 100)}% Complete
                        </span>
                    </div>
                    <div
                        className={`w-full rounded-full h-3 shadow-inner transition-all duration-300 ${
                            isDarkTheme ? "bg-gray-600" : "bg-gray-300"
                        }`}
                    >
                        <div
                            className="bg-gradient-to-r from-red-500 to-green-500 h-3 rounded-full transition-all duration-300 shadow-sm"
                            style={{ width: `${(currentStep / 4) * 100}%` }}
                        ></div>
                    </div>
                </div>

                {/* Step Content */}
                <div className="p-6">
                    {currentStep === 1 && (
                        <div className="space-y-6">
                            <div className="text-center mb-6">
                                <div className="text-3xl mb-2">👤</div>
                                <h3
                                    className={`text-xl font-semibold transition-colors duration-300 ${
                                        isDarkTheme
                                            ? "text-white"
                                            : "text-gray-900"
                                    }`}
                                >
                                    Basic Information
                                </h3>
                                <p
                                    className={`font-medium transition-colors duration-300 ${
                                        isDarkTheme
                                            ? "text-gray-300"
                                            : "text-gray-700"
                                    }`}
                                >
                                    Let's start with the basics
                                </p>
                            </div>

                            <div className="space-y-4">
                                <div>
                                    <label
                                        className={`block text-sm font-medium mb-2 transition-colors duration-300 ${
                                            isDarkTheme
                                                ? "text-gray-200"
                                                : "text-gray-700"
                                        }`}
                                    >
                                        <FaUser className="inline mr-2" />
                                        Full Name *
                                    </label>
                                    <input
                                        type="text"
                                        name="name"
                                        value={formData.name}
                                        onChange={handleInputChange}
                                        placeholder="Enter the person's full name"
                                        className={`w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all ${
                                            isDarkTheme
                                                ? "bg-gray-700 text-white placeholder-gray-400 border-gray-600"
                                                : "bg-white text-gray-900 placeholder-gray-500"
                                        }`}
                                        required
                                    />
                                    {formData.name.trim().length > 0 &&
                                        formData.name.trim().length < 2 && (
                                            <p className="text-red-500 text-sm mt-1">
                                                Name must be at least 2
                                                characters long
                                            </p>
                                        )}
                                </div>

                                {/* Date of Birth and Death removed */}
                            </div>
                        </div>
                    )}

                    {currentStep === 2 && (
                        <div className="space-y-6">
                            <div className="text-center mb-6">
                                <div className="text-3xl mb-2">💝</div>
                                <h3
                                    className={`text-xl font-semibold transition-colors duration-300 ${
                                        isDarkTheme
                                            ? "text-white"
                                            : "text-gray-900"
                                    }`}
                                >
                                    Personal Memories
                                </h3>
                                <p
                                    className={`font-medium transition-colors duration-300 ${
                                        isDarkTheme
                                            ? "text-gray-300"
                                            : "text-gray-700"
                                    }`}
                                >
                                    Share special moments
                                </p>
                            </div>

                            <div>
                                <label
                                    className={`block text-sm font-medium mb-2 transition-colors duration-300 ${
                                        isDarkTheme
                                            ? "text-gray-200"
                                            : "text-gray-700"
                                    }`}
                                >
                                    <FaHeart className="inline mr-2" />
                                    Write your message here (Optional)
                                </label>
                                <textarea
                                    name="bio"
                                    value={formData.bio}
                                    onChange={handleInputChange}
                                    placeholder="Tell us about their special moments and milestones..."
                                    rows={6}
                                    className={`w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all resize-none ${
                                        isDarkTheme
                                            ? "bg-gray-700 text-white placeholder-gray-400 border-gray-600"
                                            : "bg-white text-gray-900 placeholder-gray-500"
                                    }`}
                                />
                                <p
                                    className={`text-sm mt-1 font-medium transition-colors duration-300 ${
                                        isDarkTheme
                                            ? "text-gray-400"
                                            : "text-gray-600"
                                    }`}
                                ></p>
                            </div>
                        </div>
                    )}

                    {currentStep === 3 && (
                        <div className="space-y-6">
                            <div className="text-center mb-6">
                                <div className="text-3xl mb-2">🎵</div>
                                <h3
                                    className={`text-xl font-semibold transition-colors duration-300 ${
                                        isDarkTheme
                                            ? "text-white"
                                            : "text-gray-900"
                                    }`}
                                >
                                    Spotify Link
                                </h3>
                                <p
                                    className={`font-medium transition-colors duration-300 ${
                                        isDarkTheme
                                            ? "text-gray-300"
                                            : "text-gray-700"
                                    }`}
                                >
                                    Share their favorite song (optional)
                                </p>
                            </div>

                            <div>
                                <label
                                    className={`block text-sm font-medium mb-2 transition-colors duration-300 ${
                                        isDarkTheme
                                            ? "text-gray-200"
                                            : "text-gray-700"
                                    }`}
                                >
                                    Spotify Link
                                </label>
                                <input
                                    type="url"
                                    name="spotifyLink"
                                    value={spotifyLink}
                                    onChange={handleSpotifyLinkChange}
                                    placeholder="https://open.spotify.com/track/..."
                                    className={`w-full px-4 py-3 border-2 rounded-xl focus:ring-2 transition-all ${
                                        isDarkTheme
                                            ? "bg-gray-700 text-white placeholder-gray-400"
                                            : "bg-white text-gray-900"
                                    } ${
                                        spotifyLink.trim()
                                            ? isValidSpotifyUrl(spotifyLink)
                                                ? "border-green-500 focus:ring-green-500 focus:border-green-500"
                                                : "border-red-500 focus:ring-red-500 focus:border-red-500"
                                            : "border-gray-300 focus:ring-red-500 focus:border-red-500"
                                    }`}
                                />
                                {spotifyLink.trim() &&
                                    isValidSpotifyUrl(spotifyLink) && (
                                        <p className="text-green-500 text-sm mt-1">
                                            ✅ Valid Spotify URL
                                        </p>
                                    )}
                                {spotifyLink.trim() &&
                                    !isValidSpotifyUrl(spotifyLink) && (
                                        <p className="text-red-500 text-sm mt-1">
                                            ❌ Invalid Spotify URL. Please enter
                                            a valid Spotify track or playlist
                                            URL.
                                        </p>
                                    )}

                                {/* Spotify Preview Player */}
                                {spotifyLink.trim() &&
                                    isValidSpotifyUrl(spotifyLink) && (
                                        <div
                                            className={`mt-4 p-4 rounded-xl border transition-all duration-300 ${
                                                isDarkTheme
                                                    ? "bg-gray-700 border-green-600"
                                                    : "bg-gray-50 border-green-200"
                                            }`}
                                        >
                                            <div className="flex items-center justify-between mb-3">
                                                <h4
                                                    className={`text-sm font-semibold transition-colors duration-300 ${
                                                        isDarkTheme
                                                            ? "text-white"
                                                            : "text-gray-800"
                                                    }`}
                                                >
                                                    🎵 Preview Player
                                                </h4>
                                                <button
                                                    onClick={() =>
                                                        setShowSpotifyPreview(
                                                            !showSpotifyPreview
                                                        )
                                                    }
                                                    className={`text-sm font-medium transition-colors duration-300 ${
                                                        isDarkTheme
                                                            ? "text-green-400 hover:text-green-300"
                                                            : "text-green-600 hover:text-green-700"
                                                    }`}
                                                >
                                                    {showSpotifyPreview
                                                        ? "Hide Preview"
                                                        : "Show Preview"}
                                                </button>
                                            </div>

                                            {showSpotifyPreview && (
                                                <div className="w-full">
                                                    <Spotify
                                                        wide
                                                        className="w-full"
                                                        link={spotifyLink}
                                                    />
                                                </div>
                                            )}
                                        </div>
                                    )}

                                <p
                                    className={`text-sm mt-2 transition-colors duration-300 ${
                                        isDarkTheme
                                            ? "text-gray-400"
                                            : "text-gray-500"
                                    }`}
                                >
                                    Share their favorite song or playlist. This
                                    will be displayed on their profile.
                                </p>
                            </div>
                        </div>
                    )}

                    {currentStep === 4 && (
                        <div className="space-y-6">
                            <div className="text-center mb-6">
                                <div className="text-3xl mb-2">🖼️</div>
                                <h3
                                    className={`text-xl font-semibold transition-colors duration-300 ${
                                        isDarkTheme
                                            ? "text-white"
                                            : "text-gray-900"
                                    }`}
                                >
                                    Christmas Photos
                                </h3>
                                <p
                                    className={`font-medium transition-colors duration-300 ${
                                        isDarkTheme
                                            ? "text-gray-300"
                                            : "text-gray-700"
                                    }`}
                                >
                                    Add beautiful memories (optional)
                                </p>
                            </div>

                            {!uploadPreview ? (
                                <div className="text-center">
                                    <div
                                        className={`border-2 border-dashed rounded-xl p-8 transition-colors ${
                                            isDarkTheme
                                                ? "border-gray-600 hover:border-red-400"
                                                : "border-gray-300 hover:border-red-400"
                                        }`}
                                    >
                                        <FaUpload
                                            size={48}
                                            className={`mx-auto mb-4 transition-colors duration-300 ${
                                                isDarkTheme
                                                    ? "text-gray-500"
                                                    : "text-gray-400"
                                            }`}
                                        />
                                        <p
                                            className={`text-lg font-medium mb-2 transition-colors duration-300 ${
                                                isDarkTheme
                                                    ? "text-gray-200"
                                                    : "text-gray-700"
                                            }`}
                                        >
                                            Upload Christmas Memories
                                        </p>
                                        <p
                                            className={`mb-4 transition-colors duration-300 ${
                                                isDarkTheme
                                                    ? "text-gray-400"
                                                    : "text-gray-500"
                                            }`}
                                        >
                                            Select up to 5 photos to add to your
                                            Christmas tribute. Additional photos
                                            can be added later.
                                        </p>
                                        <input
                                            type="file"
                                            multiple
                                            accept="image/jpeg,image/jpg,image/png,image/gif"
                                            onChange={(e) =>
                                                handleFileSelection(
                                                    e.target.files
                                                )
                                            }
                                            className="hidden"
                                            id="photo-upload"
                                        />
                                        <label
                                            htmlFor="photo-upload"
                                            className="bg-gradient-to-r from-red-500 to-green-500 text-white px-6 py-3 rounded-lg hover:from-red-600 hover:to-green-600 transition-all cursor-pointer inline-block"
                                        >
                                            Choose Photos
                                        </label>
                                    </div>
                                    {selectedFiles.length > 0 && (
                                        <div className="mt-4">
                                            <p
                                                className={`text-sm mb-2 transition-colors duration-300 ${
                                                    isDarkTheme
                                                        ? "text-gray-400"
                                                        : "text-gray-600"
                                                }`}
                                            >
                                                {selectedFiles.length} photo(s)
                                                selected
                                            </p>
                                            <button
                                                onClick={() =>
                                                    setUploadPreview(true)
                                                }
                                                className="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors"
                                            >
                                                Preview & Continue
                                            </button>
                                        </div>
                                    )}
                                </div>
                            ) : (
                                <div className="space-y-4">
                                    <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
                                        {selectedFiles.map((file, index) => (
                                            <div
                                                key={index}
                                                className="relative group"
                                            >
                                                <div
                                                    className={`aspect-square rounded-lg overflow-hidden transition-colors duration-300 ${
                                                        isDarkTheme
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
                                                <button
                                                    onClick={() =>
                                                        removeSelectedFile(
                                                            index
                                                        )
                                                    }
                                                    className="absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full p-2 transition-colors opacity-0 group-hover:opacity-100"
                                                >
                                                    <FaTimes size={12} />
                                                </button>
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
                                            </div>
                                        ))}
                                    </div>
                                    <button
                                        onClick={() => setUploadPreview(false)}
                                        className="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors"
                                    >
                                        Back to Selection
                                    </button>
                                </div>
                            )}
                        </div>
                    )}

                    {/* Navigation Buttons */}
                    <div
                        className={`flex justify-between pt-6 border-t transition-colors duration-300 ${
                            isDarkTheme ? "border-gray-600" : "border-gray-200"
                        }`}
                    >
                        {currentStep > 1 && (
                            <button
                                onClick={prevStep}
                                className={`px-6 py-3 border rounded-lg transition-colors ${
                                    isDarkTheme
                                        ? "border-gray-600 text-gray-300 hover:bg-gray-700"
                                        : "border-gray-300 text-gray-700 hover:bg-gray-50"
                                }`}
                            >
                                Previous
                            </button>
                        )}

                        <div className="ml-auto">
                            {currentStep < 4 ? (
                                <button
                                    onClick={nextStep}
                                    className="px-6 py-3 bg-gradient-to-r from-red-500 to-green-500 text-white rounded-lg hover:from-red-600 hover:to-green-600 transition-all shadow-md hover:shadow-lg font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    Next Step →
                                </button>
                            ) : (
                                <button
                                    onClick={handleSubmit}
                                    disabled={isSubmitting}
                                    className="px-8 py-3 bg-gradient-to-r from-red-500 to-green-500 text-white rounded-lg hover:from-red-600 hover:to-green-600 transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-md hover:shadow-lg font-semibold"
                                >
                                    {isSubmitting ? (
                                        <div className="flex items-center space-x-2">
                                            <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                                            <span>Creating...</span>
                                        </div>
                                    ) : (
                                        "🎄 Create Christmas Profile"
                                    )}
                                </button>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};
