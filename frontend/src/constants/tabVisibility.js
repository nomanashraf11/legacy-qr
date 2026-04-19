/** Keys stored in profile.tab_visibility (Legacy is always shown). */
export const TAB_VISIBILITY_DEFAULTS = {
    family_tree: true,
    gallery: true,
    timeline: true,
    tribute: true,
};

export function mergeTabVisibility(raw) {
    return {
        ...TAB_VISIBILITY_DEFAULTS,
        ...(raw && typeof raw === "object" ? raw : {}),
    };
}

/** Labels for settings toggles (Legacy is not listed — always on). */
export const TAB_VISIBILITY_ITEMS = [
    { key: "family_tree", label: "Family Tree" },
    { key: "gallery", label: "Gallery" },
    { key: "timeline", label: "Timeline" },
    { key: "tribute", label: "Tribute" },
];
