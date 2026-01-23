import { ChristmasDemo } from "../components/ChristmasDemo";

export const ChristmasDemoPage = () => {
    return (
        <div className="min-h-screen bg-gradient-to-br from-red-50 to-green-50">
            <div className="container mx-auto py-8">
                <div className="text-center mb-8">
                    <h1 className="text-4xl font-bold text-gray-800 mb-4">
                        🎄 Christmas Version Demo
                    </h1>
                    <p className="text-lg text-gray-600 max-w-2xl mx-auto">
                        This is a preview of how the Christmas version will look
                        and function. The Christmas page is completely separate
                        from the regular version and includes a beautiful photo
                        slider with Christmas theming.
                    </p>
                </div>

                <ChristmasDemo />

                <div className="max-w-4xl mx-auto mt-8 bg-white rounded-lg shadow-lg p-6">
                    <h2 className="text-2xl font-bold text-gray-800 mb-4">
                        How It Works
                    </h2>
                    <div className="space-y-4 text-gray-700">
                        <div className="flex items-start space-x-3">
                            <div className="bg-blue-100 text-blue-800 rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold">
                                1
                            </div>
                            <div>
                                <h3 className="font-semibold">
                                    Admin Creates Christmas QR Code
                                </h3>
                                <p className="text-sm">
                                    Admin selects "Christmas Version" when
                                    creating a batch in the admin panel.
                                </p>
                            </div>
                        </div>

                        <div className="flex items-start space-x-3">
                            <div className="bg-blue-100 text-blue-800 rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold">
                                2
                            </div>
                            <div>
                                <h3 className="font-semibold">
                                    User Scans QR Code
                                </h3>
                                <p className="text-sm">
                                    When someone scans the QR code, the system
                                    checks the version_type field.
                                </p>
                            </div>
                        </div>

                        <div className="flex items-start space-x-3">
                            <div className="bg-blue-100 text-blue-800 rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold">
                                3
                            </div>
                            <div>
                                <h3 className="font-semibold">
                                    Automatic Redirect
                                </h3>
                                <p className="text-sm">
                                    If version_type = 'christmas', user is
                                    redirected to the Christmas page instead of
                                    the regular page.
                                </p>
                            </div>
                        </div>

                        <div className="flex items-start space-x-3">
                            <div className="bg-blue-100 text-blue-800 rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold">
                                4
                            </div>
                            <div>
                                <h3 className="font-semibold">
                                    Christmas Experience
                                </h3>
                                <p className="text-sm">
                                    User sees a beautiful Christmas-themed page
                                    with photo slider, no navigation bar, and
                                    simplified features.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="max-w-4xl mx-auto mt-6 bg-white rounded-lg shadow-lg p-6">
                    <h2 className="text-2xl font-bold text-gray-800 mb-4">
                        Key Differences
                    </h2>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 className="text-lg font-semibold text-red-600 mb-3">
                                🎄 Christmas Version
                            </h3>
                            <ul className="space-y-2 text-sm text-gray-700">
                                <li>• Single page layout</li>
                                <li>• Photo slider with auto-advance</li>
                                <li>• Christmas theme (red/green colors)</li>
                                <li>• No navigation bar</li>
                                <li>• No timeline, tribute, or family tree</li>
                                <li>• Simplified edit modal</li>
                                <li>• Share functionality</li>
                                <li>• Mobile-optimized</li>
                            </ul>
                        </div>

                        <div>
                            <h3 className="text-lg font-semibold text-blue-600 mb-3">
                                📱 Regular Version
                            </h3>
                            <ul className="space-y-2 text-sm text-gray-700">
                                <li>• Full navigation with multiple pages</li>
                                <li>
                                    • Timeline, tribute, family tree features
                                </li>
                                <li>• Social media links</li>
                                <li>• Spotify integration</li>
                                <li>• Map functionality</li>
                                <li>• Advanced editing options</li>
                                <li>• Settings page</li>
                                <li>• Complete feature set</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div className="max-w-4xl mx-auto mt-6 bg-green-50 border border-green-200 rounded-lg p-6">
                    <h2 className="text-xl font-bold text-green-800 mb-3">
                        ✅ Implementation Complete
                    </h2>
                    <p className="text-green-700">
                        The Christmas version is fully implemented and ready to
                        use! The backend supports the version_type field, the
                        frontend has separate routing, and the Christmas page
                        includes all the requested features: name display, photo
                        slider, Christmas theming, and edit functionality.
                    </p>
                </div>
            </div>
        </div>
    );
};
