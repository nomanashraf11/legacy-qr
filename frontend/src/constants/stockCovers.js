/** Filenames must match `config/living_legacy.php` stock_covers and `public/images/stock-covers/`. */
export const STOCK_COVERS = [
    { file: "cover-sky.svg", label: "Sky" },
    { file: "cover-meadow.svg", label: "Meadow" },
    { file: "cover-dusk.svg", label: "Dusk" },
    { file: "cover-serenity.svg", label: "Serenity" },
];

/**
 * Public assets live under `frontend/public/images/stock-covers/` so Vite dev serves them.
 * Production Laravel also serves the same path from `public/images/stock-covers/`.
 */
export function stockCoverPreviewUrl(file) {
    const base = import.meta.env.BASE_URL || "/";
    const path = `images/stock-covers/${file}`;
    return base.endsWith("/") ? `${base}${path}` : `${base}/${path}`;
}

/** Derive stock filename from API URL when cover is stored as `stock/{file}`. */
export function initialStockCoverFromUrl(url) {
    if (typeof url !== "string" || !url.trim()) {
        return "";
    }
    const m = url.match(/stock-covers\/([^/?#]+)/);
    return m ? m[1] : "";
}
