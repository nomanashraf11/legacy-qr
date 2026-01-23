import { useState, useEffect } from "react";
import { useParams } from "react-router-dom";
import { useFetchTributeData } from "../services";
import { useAppSelector, getUserData } from "../redux";

export const ChristmasTest = () => {
    const { id } = useParams();
    const { data, isLoading, refetch } = useFetchTributeData(id);
    const userData = useAppSelector(getUserData);

    const [testResults, setTestResults] = useState([]);
    const [isRunning, setIsRunning] = useState(false);

    const addTestResult = (test, status, message) => {
        setTestResults((prev) => [
            ...prev,
            {
                test,
                status,
                message,
                timestamp: new Date().toLocaleTimeString(),
            },
        ]);
    };

    const runTests = async () => {
        setIsRunning(true);
        setTestResults([]);

        // Test 1: Check if data is loaded
        addTestResult(
            "Data Loading",
            data ? "PASS" : "FAIL",
            data ? "Profile data loaded successfully" : "No profile data found"
        );

        // Test 2: Check version_type
        const versionType = data?.Details?.version_type;
        addTestResult(
            "Version Type Check",
            versionType === "christmas" ? "PASS" : "FAIL",
            versionType === "christmas"
                ? "Christmas version detected correctly"
                : `Expected 'christmas', got '${versionType}'`
        );

        // Test 3: Check profile data
        const hasProfile = data?.Details?.name;
        addTestResult(
            "Profile Data",
            hasProfile ? "PASS" : "FAIL",
            hasProfile
                ? `Profile found: ${data.Details.name}`
                : "No profile name found"
        );

        // Test 4: Check photos
        const photos = data?.Details?.Photos || [];
        addTestResult(
            "Photos Data",
            photos.length > 0 ? "PASS" : "WARN",
            photos.length > 0
                ? `Found ${photos.length} photos`
                : "No photos found (this is okay for testing)"
        );

        // Test 5: Check Redux state
        const reduxData = userData;
        addTestResult(
            "Redux State",
            reduxData ? "PASS" : "FAIL",
            reduxData ? "User data in Redux state" : "No user data in Redux"
        );

        // Test 6: Check API response structure
        const hasVersionType = data?.Details?.version_type !== undefined;
        addTestResult(
            "API Response Structure",
            hasVersionType ? "PASS" : "FAIL",
            hasVersionType
                ? "version_type field present in API response"
                : "version_type field missing"
        );

        // Test 7: Check if Christmas page should be shown
        const shouldShowChristmas = versionType === "christmas";
        addTestResult(
            "Christmas Page Logic",
            shouldShowChristmas ? "PASS" : "FAIL",
            shouldShowChristmas
                ? "Should show Christmas page (version_type = christmas)"
                : "Should show regular page (version_type != christmas)"
        );

        setIsRunning(false);
    };

    const clearResults = () => {
        setTestResults([]);
    };

    const getStatusColor = (status) => {
        switch (status) {
            case "PASS":
                return "text-green-600 bg-green-100";
            case "FAIL":
                return "text-red-600 bg-red-100";
            case "WARN":
                return "text-yellow-600 bg-yellow-100";
            default:
                return "text-gray-600 bg-gray-100";
        }
    };

    const getStatusIcon = (status) => {
        switch (status) {
            case "PASS":
                return "✅";
            case "FAIL":
                return "❌";
            case "WARN":
                return "⚠️";
            default:
                return "ℹ️";
        }
    };

    return (
        <div className="min-h-screen bg-gray-50 p-6">
            <div className="max-w-4xl mx-auto">
                {/* Header */}
                <div className="bg-white rounded-lg shadow-lg p-6 mb-6">
                    <div className="flex items-center space-x-3 mb-4">
                        <div className="text-3xl">🧪</div>
                        <div>
                            <h1 className="text-2xl font-bold text-gray-800">
                                Christmas Frontend Test
                            </h1>
                            <p className="text-gray-600">
                                Testing Christmas version functionality
                            </p>
                        </div>
                    </div>

                    <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h3 className="font-semibold text-blue-800 mb-2">
                            Test Information:
                        </h3>
                        <p className="text-blue-700 text-sm">
                            <strong>QR Code ID:</strong>{" "}
                            {id || "No ID provided"}
                            <br />
                            <strong>Current URL:</strong> {window.location.href}
                            <br />
                            <strong>Expected Christmas URL:</strong>{" "}
                            {id ? `/${id}/christmas` : "N/A"}
                        </p>
                    </div>
                </div>

                {/* Test Controls */}
                <div className="bg-white rounded-lg shadow-lg p-6 mb-6">
                    <h2 className="text-xl font-bold text-gray-800 mb-4">
                        Test Controls
                    </h2>
                    <div className="flex space-x-4">
                        <button
                            onClick={runTests}
                            disabled={isRunning || isLoading}
                            className="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white px-6 py-2 rounded-lg transition-colors flex items-center space-x-2"
                        >
                            {isRunning ? (
                                <>
                                    <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                                    <span>Running Tests...</span>
                                </>
                            ) : (
                                <span>Run Tests</span>
                            )}
                        </button>

                        <button
                            onClick={clearResults}
                            className="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition-colors"
                        >
                            Clear Results
                        </button>

                        <button
                            onClick={refetch}
                            disabled={isLoading}
                            className="bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white px-6 py-2 rounded-lg transition-colors"
                        >
                            {isLoading ? "Loading..." : "Refresh Data"}
                        </button>
                    </div>
                </div>

                {/* Data Preview */}
                <div className="bg-white rounded-lg shadow-lg p-6 mb-6">
                    <h2 className="text-xl font-bold text-gray-800 mb-4">
                        Data Preview
                    </h2>

                    {isLoading ? (
                        <div className="text-center py-8">
                            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto mb-4"></div>
                            <p className="text-gray-600">Loading data...</p>
                        </div>
                    ) : data ? (
                        <div className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="bg-gray-50 p-4 rounded-lg">
                                    <h3 className="font-semibold text-gray-700 mb-2">
                                        Profile Info
                                    </h3>
                                    <p>
                                        <strong>Name:</strong>{" "}
                                        {data.Details?.name || "Not set"}
                                    </p>
                                    <p>
                                        <strong>Version Type:</strong>{" "}
                                        {data.Details?.version_type ||
                                            "Not set"}
                                    </p>
                                    <p>
                                        <strong>Bio:</strong>{" "}
                                        {data.Details?.bio
                                            ? "Present"
                                            : "Not set"}
                                    </p>
                                </div>

                                <div className="bg-gray-50 p-4 rounded-lg">
                                    <h3 className="font-semibold text-gray-700 mb-2">
                                        Photos
                                    </h3>
                                    <p>
                                        <strong>Count:</strong>{" "}
                                        {data.Details?.Photos?.length || 0}
                                    </p>
                                    <p>
                                        <strong>First Photo:</strong>{" "}
                                        {data.Details?.Photos?.[0]?.image
                                            ? "Present"
                                            : "None"}
                                    </p>
                                </div>
                            </div>

                            <div className="bg-gray-50 p-4 rounded-lg">
                                <h3 className="font-semibold text-gray-700 mb-2">
                                    Raw API Response
                                </h3>
                                <pre className="text-xs bg-white p-3 rounded border overflow-auto max-h-40">
                                    {JSON.stringify(data, null, 2)}
                                </pre>
                            </div>
                        </div>
                    ) : (
                        <div className="text-center py-8">
                            <p className="text-gray-600">
                                No data available. Make sure the QR code exists
                                and has a profile.
                            </p>
                        </div>
                    )}
                </div>

                {/* Test Results */}
                {testResults.length > 0 && (
                    <div className="bg-white rounded-lg shadow-lg p-6">
                        <h2 className="text-xl font-bold text-gray-800 mb-4">
                            Test Results
                        </h2>

                        <div className="space-y-3">
                            {testResults.map((result, index) => (
                                <div
                                    key={index}
                                    className="flex items-start space-x-3 p-3 rounded-lg border"
                                >
                                    <span className="text-lg">
                                        {getStatusIcon(result.status)}
                                    </span>
                                    <div className="flex-1">
                                        <div className="flex items-center space-x-2 mb-1">
                                            <h3 className="font-semibold text-gray-800">
                                                {result.test}
                                            </h3>
                                            <span
                                                className={`px-2 py-1 rounded-full text-xs font-medium ${getStatusColor(
                                                    result.status
                                                )}`}
                                            >
                                                {result.status}
                                            </span>
                                        </div>
                                        <p className="text-gray-600 text-sm">
                                            {result.message}
                                        </p>
                                        <p className="text-gray-400 text-xs mt-1">
                                            Time: {result.timestamp}
                                        </p>
                                    </div>
                                </div>
                            ))}
                        </div>

                        {/* Summary */}
                        <div className="mt-6 p-4 bg-gray-50 rounded-lg">
                            <h3 className="font-semibold text-gray-800 mb-2">
                                Test Summary
                            </h3>
                            <div className="flex space-x-4 text-sm">
                                <span className="text-green-600">
                                    ✅ Passed:{" "}
                                    {
                                        testResults.filter(
                                            (r) => r.status === "PASS"
                                        ).length
                                    }
                                </span>
                                <span className="text-red-600">
                                    ❌ Failed:{" "}
                                    {
                                        testResults.filter(
                                            (r) => r.status === "FAIL"
                                        ).length
                                    }
                                </span>
                                <span className="text-yellow-600">
                                    ⚠️ Warnings:{" "}
                                    {
                                        testResults.filter(
                                            (r) => r.status === "WARN"
                                        ).length
                                    }
                                </span>
                            </div>
                        </div>
                    </div>
                )}

                {/* Instructions */}
                <div className="bg-white rounded-lg shadow-lg p-6 mt-6">
                    <h2 className="text-xl font-bold text-gray-800 mb-4">
                        How to Test
                    </h2>
                    <div className="space-y-3 text-gray-700">
                        <p>
                            <strong>1. For Christmas Version:</strong>
                        </p>
                        <ul className="list-disc list-inside ml-4 space-y-1">
                            <li>
                                Create a QR code with version_type = 'christmas'
                                in admin panel
                            </li>
                            <li>
                                Visit:{" "}
                                <code className="bg-gray-100 px-2 py-1 rounded">
                                    /{id}
                                </code>
                            </li>
                            <li>
                                Should auto-redirect to:{" "}
                                <code className="bg-gray-100 px-2 py-1 rounded">
                                    /{id}/christmas
                                </code>
                            </li>
                            <li>
                                Should show Christmas page with photo slider
                            </li>
                        </ul>

                        <p className="mt-4">
                            <strong>2. For Regular Version:</strong>
                        </p>
                        <ul className="list-disc list-inside ml-4 space-y-1">
                            <li>
                                Create a QR code with version_type = 'full'
                                (default)
                            </li>
                            <li>
                                Visit:{" "}
                                <code className="bg-gray-100 px-2 py-1 rounded">
                                    /{id}
                                </code>
                            </li>
                            <li>
                                Should auto-redirect to:{" "}
                                <code className="bg-gray-100 px-2 py-1 rounded">
                                    /{id}/legacy
                                </code>
                            </li>
                            <li>
                                Should show regular page with full navigation
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    );
};
