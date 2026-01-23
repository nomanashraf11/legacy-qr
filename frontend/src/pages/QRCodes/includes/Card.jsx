import { Link } from "react-router-dom";

export const Card = ({ qrData }) => {
    // Determine the route based on version_type
    const getRoute = () => {
        if (qrData.version_type === "christmas") {
            return `/${qrData.uuid}/christmas`;
        }
        return `/${qrData.uuid}/legacy`;
    };

    return (
        <Link
            to={getRoute()}
            className="flex flex-col items-center space-y-4 p-6 min-h-[340px] group border rounded-lg shadow md:hover:scale-110 bg-[#242526] border-white/20 transition-all duration-300 ease-in-out"
        >
            <div className="relative">
                <img className="w-[200px] h-[200px]" src={qrData.image} />
                {/* Christmas Version Badge */}
                {qrData.version_type === "christmas" && (
                    <div className="absolute top-2 right-2 bg-gradient-to-r from-red-500 to-green-500 text-white px-2 py-1 rounded-full text-xs font-bold flex items-center space-x-1">
                        <span>🎄</span>
                        <span>Christmas</span>
                    </div>
                )}
            </div>
            <div className="text-center pt-2">
                <span className="inline-flex items-center justify-center max-w-40 text-center text-white opacity-50 group-active:opacity-40 font-medium text-sm group-hover:opacity-90">
                    Click on QR code for setup and future updates
                    <svg
                        className="rtl:rotate-180 min-w-3.5 min-h-3.5 max-w-3.5 max-h-3.5"
                        aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 14 10"
                    >
                        <path
                            stroke="currentColor"
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth="2"
                            d="M1 5h12m0 0L9 1m4 4L9 9"
                        />
                    </svg>
                </span>
            </div>
            <p className="text-white/80 text-center">
                {qrData?.profile?.name || ""}
            </p>
        </Link>
    );
};
