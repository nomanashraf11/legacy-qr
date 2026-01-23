import { useState } from "react";
import { FaPlay, FaPause, FaChevronLeft, FaChevronRight } from "react-icons/fa";

export const ChristmasDemo = () => {
    const [currentSlide, setCurrentSlide] = useState(0);
    const [isPlaying, setIsPlaying] = useState(true);

    // Demo photos for Christmas slider
    const demoPhotos = [
        {
            id: 1,
            image: "https://via.placeholder.com/600x400/ff6b6b/ffffff?text=Christmas+Tree+2020",
            caption: "Our last Christmas tree together",
        },
        {
            id: 2,
            image: "https://via.placeholder.com/600x400/4ecdc4/ffffff?text=Cookie+Baking",
            caption: "Making Christmas cookies",
        },
        {
            id: 3,
            image: "https://via.placeholder.com/600x400/45b7d1/ffffff?text=Family+Gathering",
            caption: "Christmas family gathering",
        },
        {
            id: 4,
            image: "https://via.placeholder.com/600x400/96ceb4/ffffff?text=Fireplace+Stories",
            caption: "Telling stories by the fireplace",
        },
    ];

    const nextSlide = () => {
        setCurrentSlide((prev) => (prev + 1) % demoPhotos.length);
    };

    const prevSlide = () => {
        setCurrentSlide(
            (prev) => (prev - 1 + demoPhotos.length) % demoPhotos.length
        );
    };

    const togglePlayPause = () => {
        setIsPlaying(!isPlaying);
    };

    return (
        <div className="max-w-4xl mx-auto p-6">
            <div className="bg-white rounded-2xl shadow-xl overflow-hidden">
                {/* Header */}
                <div className="bg-gradient-to-r from-red-600 to-green-600 text-white p-6">
                    <div className="flex items-center space-x-3">
                        <div className="text-3xl">🎄</div>
                        <div>
                            <h1 className="text-2xl font-bold">
                                Christmas Memories Demo
                            </h1>
                            <p className="text-sm opacity-90">
                                Photo slider functionality preview
                            </p>
                        </div>
                    </div>
                </div>

                {/* Profile Section */}
                <div className="p-6 text-center border-b">
                    <div className="w-24 h-24 rounded-full mx-auto mb-4 bg-gradient-to-br from-red-400 to-green-400 flex items-center justify-center text-white text-2xl font-bold">
                        GR
                    </div>
                    <h2 className="text-3xl font-bold text-gray-800 mb-2">
                        Grandma Rose
                    </h2>
                    <p className="text-lg text-gray-600 mb-4">1940 - 2020</p>
                    <p className="text-gray-700 max-w-2xl mx-auto">
                        A loving grandmother who made the best Christmas cookies
                        and always had a warm smile for everyone. She loved
                        decorating the Christmas tree and telling stories by the
                        fireplace.
                    </p>
                </div>

                {/* Photo Slider */}
                <div className="relative">
                    {/* Main Photo Display */}
                    <div className="relative h-96 bg-gray-100">
                        <img
                            src={demoPhotos[currentSlide].image}
                            alt={demoPhotos[currentSlide].caption}
                            className="w-full h-full object-cover"
                        />

                        {/* Navigation Arrows */}
                        <button
                            onClick={prevSlide}
                            className="absolute left-4 top-1/2 transform -translate-y-1/2 bg-black/50 hover:bg-black/70 text-white rounded-full p-3 transition-colors"
                        >
                            <FaChevronLeft size={16} />
                        </button>
                        <button
                            onClick={nextSlide}
                            className="absolute right-4 top-1/2 transform -translate-y-1/2 bg-black/50 hover:bg-black/70 text-white rounded-full p-3 transition-colors"
                        >
                            <FaChevronRight size={16} />
                        </button>

                        {/* Play/Pause Button */}
                        <button
                            onClick={togglePlayPause}
                            className="absolute top-4 right-4 bg-black/50 hover:bg-black/70 text-white rounded-full p-2 transition-colors"
                        >
                            {isPlaying ? (
                                <FaPause size={14} />
                            ) : (
                                <FaPlay size={14} />
                            )}
                        </button>

                        {/* Photo Counter */}
                        <div className="absolute bottom-4 left-1/2 transform -translate-x-1/2 bg-black/50 text-white px-3 py-1 rounded-full text-sm">
                            {currentSlide + 1} / {demoPhotos.length}
                        </div>

                        {/* Caption */}
                        <div className="absolute bottom-4 left-4 right-4">
                            <div className="bg-black/50 text-white px-3 py-2 rounded-lg">
                                <p className="text-sm">
                                    {demoPhotos[currentSlide].caption}
                                </p>
                            </div>
                        </div>
                    </div>

                    {/* Thumbnail Navigation */}
                    <div className="p-4 bg-gray-50">
                        <div className="flex space-x-2 overflow-x-auto">
                            {demoPhotos.map((photo, index) => (
                                <button
                                    key={photo.id}
                                    onClick={() => setCurrentSlide(index)}
                                    className={`flex-shrink-0 w-16 h-16 rounded-lg overflow-hidden border-2 transition-colors ${
                                        index === currentSlide
                                            ? "border-red-500"
                                            : "border-gray-200 hover:border-gray-300"
                                    }`}
                                >
                                    <img
                                        src={photo.image}
                                        alt={`Thumbnail ${index + 1}`}
                                        className="w-full h-full object-cover"
                                    />
                                </button>
                            ))}
                        </div>
                    </div>
                </div>

                {/* Christmas Message */}
                <div className="p-6 text-center">
                    <div className="text-4xl mb-4">🎄</div>
                    <h3 className="text-xl font-bold text-gray-800 mb-4">
                        Remembering Grandma Rose This Christmas
                    </h3>
                    <p className="text-gray-600 max-w-2xl mx-auto">
                        As we gather with family and friends this holiday
                        season, we hold dear the memories of Grandma Rose and
                        the joy she brought to our lives. Her spirit lives on in
                        our hearts and in the stories we share.
                    </p>
                </div>

                {/* Features List */}
                <div className="p-6 bg-gray-50 border-t">
                    <h3 className="text-lg font-bold text-gray-800 mb-4">
                        Christmas Page Features:
                    </h3>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div className="space-y-2">
                            <div className="flex items-center space-x-2">
                                <span className="text-green-500">✅</span>
                                <span>Automatic photo slider</span>
                            </div>
                            <div className="flex items-center space-x-2">
                                <span className="text-green-500">✅</span>
                                <span>Thumbnail navigation</span>
                            </div>
                            <div className="flex items-center space-x-2">
                                <span className="text-green-500">✅</span>
                                <span>Play/pause controls</span>
                            </div>
                            <div className="flex items-center space-x-2">
                                <span className="text-green-500">✅</span>
                                <span>Christmas theme</span>
                            </div>
                        </div>
                        <div className="space-y-2">
                            <div className="flex items-center space-x-2">
                                <span className="text-green-500">✅</span>
                                <span>No navigation bar</span>
                            </div>
                            <div className="flex items-center space-x-2">
                                <span className="text-green-500">✅</span>
                                <span>Edit modal</span>
                            </div>
                            <div className="flex items-center space-x-2">
                                <span className="text-green-500">✅</span>
                                <span>Share functionality</span>
                            </div>
                            <div className="flex items-center space-x-2">
                                <span className="text-green-500">✅</span>
                                <span>Mobile responsive</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};
