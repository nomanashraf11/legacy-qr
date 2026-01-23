import { useState, useEffect } from "react";
import { FaTimes, FaSave } from "react-icons/fa";
import { toast } from "react-toastify";
import { Spotify } from "react-spotify-embed";

export const ChristmasEditModal = ({
    isOpen,
    onClose,
    profile,
    onSave,
    isDarkTheme = false,
}) => {
    const [formData, setFormData] = useState({
        name: profile?.name || "",
        title: profile?.title || "",
        bio: profile?.bio || "",
        spotify: profile?.spotify || "",
    });

    const [isLoading, setIsLoading] = useState(false);
    const [showSpotifyPreview, setShowSpotifyPreview] = useState(false);

    // Spotify URL validation - more comprehensive to handle various Spotify URL formats
    const spotifyRegex =
        /^https:\/\/open\.spotify\.com\/(track|album|playlist|artist|episode|show)\/[a-zA-Z0-9]+(\?.*)?$/;
    const isValidSpotifyUrl = (url) => {
        if (!url.trim()) return true; // Empty is valid (optional)

        // Debug logging to help identify validation issues
        console.log("Validating Spotify URL:", url);

        // More flexible validation - check if it's a Spotify URL
        const spotifyUrlPattern = /^https:\/\/open\.spotify\.com\//;
        if (!spotifyUrlPattern.test(url)) {
            console.log("❌ Not a Spotify URL");
            return false;
        }

        // Check if it contains valid Spotify content types
        const validContentTypes =
            /(track|album|playlist|artist|episode|show)\//;
        const isValid = validContentTypes.test(url);
        console.log("✅ Spotify URL validation result:", isValid);
        return isValid;
    };

    // Reset Spotify preview when modal opens/closes
    useEffect(() => {
        if (isOpen) {
            setShowSpotifyPreview(false);
        }
    }, [isOpen]);

    // Reset Spotify preview when Spotify field changes
    useEffect(() => {
        setShowSpotifyPreview(false);
    }, [formData.spotify]);

    // Initialize form data when profile changes
    useEffect(() => {
        if (profile) {
            setFormData({
                name: profile.name || "",
                title: profile.title || "",
                bio: profile.bio || "",
                spotify: profile.spotify || "",
            });
        }
    }, [profile]);

    const handleInputChange = (e) => {
        const { name, value, files } = e.target;
        setFormData((prev) => ({
            ...prev,
            [name]: files ? files[0] : value,
        }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setIsLoading(true);

        try {
            // Debug: Log the form data being sent
            console.log("ChristmasEditModal - Form data being sent:", formData);

            // Add dummy values for backend requirements
            const formDataWithDummies = {
                ...formData,
                // Add dummy values for backend requirements
                profile_picture: null, // Will be handled by backend with existing image
                cover_picture: null, // Will be handled by backend with existing image
                dob: "2000-01-01", // Default date for backend requirement
                dod: "", // Empty for living person
            };

            // Call the onSave function passed from parent component
            await onSave(formDataWithDummies);
            toast.success("Christmas page updated successfully!");
            setShowSpotifyPreview(false); // Reset Spotify preview
            onClose();
        } catch (error) {
            toast.error("Failed to update Christmas page");
        } finally {
            setIsLoading(false);
        }
    };

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div
                className={`rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto ChristmasModal transition-all duration-300 ${
                    isDarkTheme ? "bg-gray-800" : "bg-white"
                }`}
            >
                {/* Header */}
                <div
                    className={`flex items-center justify-between p-6 border-b transition-all duration-300 ${
                        isDarkTheme ? "border-gray-600" : "border-gray-200"
                    }`}
                >
                    <div className="flex items-center space-x-3">
                        <div className="text-2xl">🎄</div>
                        <h2
                            className={`text-2xl font-bold transition-colors duration-300 ${
                                isDarkTheme ? "text-white" : "text-gray-800"
                            }`}
                        >
                            Edit Christmas Page
                        </h2>
                    </div>
                    <button
                        onClick={onClose}
                        className={`transition-colors duration-300 ${
                            isDarkTheme
                                ? "text-gray-400 hover:text-gray-300"
                                : "text-gray-400 hover:text-gray-600"
                        }`}
                    >
                        <FaTimes size={20} />
                    </button>
                </div>

                {/* Form */}
                <form onSubmit={handleSubmit} className="p-6 space-y-6">
                    {/* Name */}
                    <div>
                        <label
                            className={`block text-sm font-medium mb-2 transition-colors duration-300 ${
                                isDarkTheme ? "text-gray-200" : "text-gray-700"
                            }`}
                        >
                            Name *
                        </label>
                        <input
                            type="text"
                            name="name"
                            value={formData.name}
                            onChange={handleInputChange}
                            required
                            className={`w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all ${
                                isDarkTheme
                                    ? "border-gray-600 text-white bg-gray-700 placeholder-gray-400"
                                    : "border-gray-300 text-gray-900 bg-white placeholder-gray-500"
                            }`}
                            placeholder="Enter name"
                        />
                    </div>

                    {/* Custom Title */}
                    <div>
                        <label
                            className={`block text-sm font-medium mb-2 transition-colors duration-300 ${
                                isDarkTheme ? "text-gray-200" : "text-gray-700"
                            }`}
                        >
                            Custom Title (Optional)
                        </label>
                        <input
                            type="text"
                            name="title"
                            value={formData.title}
                            onChange={handleInputChange}
                            className={`w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all ${
                                isDarkTheme
                                    ? "border-gray-600 text-white bg-gray-700 placeholder-gray-400"
                                    : "border-gray-300 text-gray-900 bg-white placeholder-gray-500"
                            }`}
                            placeholder="e.g., 'Beloved Father', 'Christmas Memories', 'Family Tribute'"
                        />
                        <p
                            className={`text-xs mt-1 ${
                                isDarkTheme ? "text-gray-400" : "text-gray-500"
                            }`}
                        >
                            This will appear under the name. Leave empty for
                            default title.
                        </p>
                    </div>

                    {/* Bio */}
                    <div>
                        <label
                            className={`block text-sm font-medium mb-2 transition-colors duration-300 ${
                                isDarkTheme ? "text-gray-200" : "text-gray-700"
                            }`}
                        >
                            Christmas Message
                        </label>
                        <textarea
                            name="bio"
                            value={formData.bio}
                            onChange={handleInputChange}
                            rows={4}
                            maxLength={12000}
                            className={`w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent resize-none transition-all ${
                                isDarkTheme
                                    ? "border-gray-600 text-white bg-gray-700 placeholder-gray-400"
                                    : "border-gray-300 text-gray-900 bg-white placeholder-gray-500"
                            }`}
                            placeholder="Share a special memory or message..."
                        />
                        <div className="flex justify-between items-center mt-2">
                            <p
                                className={`text-xs transition-colors duration-300 ${
                                    isDarkTheme
                                        ? "text-gray-400"
                                        : "text-gray-500"
                                }`}
                            >
                                Share your special memories and messages
                            </p>
                            <span
                                className={`text-xs transition-colors duration-300 ${
                                    formData.bio.length >= 11000
                                        ? "text-red-500"
                                        : formData.bio.length >= 8000
                                        ? "text-yellow-500"
                                        : isDarkTheme
                                        ? "text-gray-400"
                                        : "text-gray-500"
                                }`}
                            >
                                {formData.bio.length}/12,000
                            </span>
                        </div>
                    </div>

                    {/* Photo upload section removed - photos are managed separately */}

                    {/* Spotify Link */}
                    <div>
                        <label
                            className={`block text-sm font-medium mb-2 transition-colors duration-300 ${
                                isDarkTheme ? "text-gray-200" : "text-gray-700"
                            }`}
                        >
                            🎵 Spotify Music Link
                        </label>
                        <input
                            type="url"
                            name="spotify"
                            value={formData.spotify}
                            onChange={handleInputChange}
                            placeholder="https://open.spotify.com/track/..."
                            className={`w-full px-4 py-3 border-2 rounded-lg focus:ring-2 transition-all ${
                                isDarkTheme
                                    ? "text-white bg-gray-700 placeholder-gray-400"
                                    : "text-gray-900 bg-white placeholder-gray-500"
                            } ${
                                formData.spotify.trim()
                                    ? isValidSpotifyUrl(formData.spotify)
                                        ? "border-green-500 focus:ring-green-500 focus:border-green-500"
                                        : "border-red-500 focus:ring-red-500 focus:border-red-500"
                                    : "border-gray-300 focus:ring-red-500 focus:border-red-500"
                            }`}
                        />
                        {formData.spotify.trim() && (
                            <div className="mt-3 flex items-center justify-between">
                                <div className="flex items-center space-x-2">
                                    {isValidSpotifyUrl(formData.spotify) ? (
                                        <span className="text-green-600 text-sm">
                                            ✅ Valid Spotify URL
                                        </span>
                                    ) : (
                                        <span className="text-red-600 text-sm">
                                            ❌ Invalid Spotify URL
                                        </span>
                                    )}
                                </div>
                                {isValidSpotifyUrl(formData.spotify) && (
                                    <button
                                        type="button"
                                        onClick={() =>
                                            setShowSpotifyPreview(
                                                !showSpotifyPreview
                                            )
                                        }
                                        className={`text-sm font-medium px-3 py-1 rounded-lg border transition-all ${
                                            isDarkTheme
                                                ? "text-green-400 hover:text-green-300 bg-gray-700 border-green-600 hover:border-green-500"
                                                : "text-green-600 hover:text-green-700 bg-green-50 border-green-200 hover:border-green-300"
                                        }`}
                                    >
                                        {showSpotifyPreview
                                            ? "Hide Preview"
                                            : "Show Preview"}
                                    </button>
                                )}
                            </div>
                        )}

                        {showSpotifyPreview &&
                            formData.spotify &&
                            isValidSpotifyUrl(formData.spotify) && (
                                <div
                                    className={`mt-4 p-4 rounded-xl border transition-all duration-300 ${
                                        isDarkTheme
                                            ? "bg-gradient-to-r from-green-900 to-emerald-900 border-green-600"
                                            : "bg-gradient-to-r from-green-50 to-emerald-50 border-green-200"
                                    }`}
                                >
                                    <h4
                                        className={`text-sm font-semibold mb-3 transition-colors duration-300 ${
                                            isDarkTheme
                                                ? "text-white"
                                                : "text-gray-800"
                                        }`}
                                    >
                                        🎵 Preview Player
                                    </h4>
                                    <div className="w-full">
                                        <Spotify
                                            wide
                                            className="w-full"
                                            link={formData.spotify}
                                        />
                                    </div>
                                </div>
                            )}

                        <p
                            className={`text-sm mt-2 transition-colors duration-300 ${
                                isDarkTheme ? "text-gray-400" : "text-gray-500"
                            }`}
                        >
                            Add a Spotify track, album, or playlist to share
                            music memories
                        </p>
                    </div>

                    {/* Photo upload section removed - photos are managed separately via the main interface */}

                    {/* Action Buttons */}
                    <div
                        className={`flex space-x-4 pt-6 border-t transition-all duration-300 ${
                            isDarkTheme ? "border-gray-600" : "border-gray-200"
                        }`}
                    >
                        <button
                            type="button"
                            onClick={onClose}
                            className={`flex-1 py-3 px-6 rounded-lg transition-colors ${
                                isDarkTheme
                                    ? "bg-gray-700 text-gray-200 hover:bg-gray-600"
                                    : "bg-gray-200 text-gray-800 hover:bg-gray-300"
                            }`}
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            disabled={isLoading}
                            className="flex-1 bg-red-600 text-white py-3 px-6 rounded-lg hover:bg-red-700 disabled:opacity-50 transition-colors flex items-center justify-center space-x-2"
                        >
                            {isLoading ? (
                                <>
                                    <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                                    <span>Saving...</span>
                                </>
                            ) : (
                                <>
                                    <FaSave size={16} />
                                    <span>Save Christmas Page</span>
                                </>
                            )}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
};
