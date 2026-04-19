import { StaticMap } from "../../components/StaticMap";
import { FaDirections } from "react-icons/fa";
import { getOrbitStatus, getUserData, useAppSelector } from "../../redux";
import { EmptyData } from "../../components";
import Linkify from "react-linkify";

export const Bio = () => {
    const spouseOrbt = useAppSelector(getOrbitStatus);
    const userData = useAppSelector(getUserData);

    if (!userData) {
        return <EmptyData message={"No bio added yet."} />;
    }

    const spouseRelation = userData.relations?.find(
        (value) => value.name.toLowerCase() === "spouse"
    );

    const mainBioText = (userData.bio ?? "").trim();
    const spouseBioText = (spouseRelation?.bio ?? "").trim();
    const displayedBio = spouseOrbt === "main" ? mainBioText : spouseBioText;
    const hasBioBlock = Boolean(displayedBio);
    const hasMap = Boolean(userData.latitude && userData.longitude);

    const bioSurface = userData.dark_theme
        ? "bg-[#1D1D1F] border-white/[0.08] text-neutral-100 shadow-[0_1px_0_rgba(255,255,255,0.06)_inset]"
        : "bg-[#E8E8ED] border-black/[0.06] text-neutral-900 shadow-sm";

    return (
        <div
            className={`w-full grid grid-cols-1 gap-6 md:gap-8 lg:gap-10 ${
                hasBioBlock ? "md:grid-cols-2 md:items-start" : ""
            }`}
        >
            {hasBioBlock ? (
                <div className="min-w-0">
                    <Linkify>
                        <div
                            className={`rounded-xl border font-roboto w-full px-4 py-5 sm:px-5 sm:py-6 md:px-6 md:py-7 text-sm sm:text-base leading-relaxed md:leading-7 whitespace-pre-line break-words ${bioSurface}`}
                        >
                            {displayedBio}
                        </div>
                    </Linkify>
                </div>
            ) : null}

            {hasMap ? (
                <div
                    className={`min-w-0 flex flex-col gap-4 md:gap-5 ${
                        hasBioBlock
                            ? "md:pt-0.5"
                            : "max-w-3xl md:max-w-4xl mx-auto w-full"
                    }`}
                >
                    <div className="w-full [&_#map]:mt-0 [&_#map]:rounded-xl">
                        <StaticMap />
                    </div>
                    <a
                        rel="noreferrer"
                        target="_blank"
                        className={`inline-flex w-full sm:w-auto sm:self-start items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-medium transition-colors duration-200 ${
                            userData.dark_theme
                                ? "bg-[#2C2C2E] text-white hover:bg-[#3A3A3C] border border-white/10"
                                : "bg-neutral-900 text-white hover:bg-neutral-800"
                        }`}
                        href={`https://www.google.com/maps?q=${userData.latitude},${userData.longitude}`}
                    >
                        <FaDirections color="#1A73E8" className="shrink-0" />
                        Get Directions
                    </a>
                </div>
            ) : null}
        </div>
    );
};
