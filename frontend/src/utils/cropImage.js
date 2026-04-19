/**
 * High-quality crop export for profile (square) and cover (16:9) images.
 * Uses integer pixels, high-quality downscaling only, and JPEG to avoid huge PNGs.
 */

const AVATAR_MAX_PX = 1024;
const COVER_MAX_W = 1920;
const COVER_MAX_H = 1080;
const JPEG_QUALITY = 0.92;

function normalizeCropPixels(pixels, imageWidth, imageHeight) {
    if (!pixels || !imageWidth || !imageHeight) return null;
    let x = Math.round(pixels.x);
    let y = Math.round(pixels.y);
    let w = Math.max(1, Math.round(pixels.width));
    let h = Math.max(1, Math.round(pixels.height));

    x = Math.max(0, Math.min(x, imageWidth - 1));
    y = Math.max(0, Math.min(y, imageHeight - 1));
    w = Math.min(w, imageWidth - x);
    h = Math.min(h, imageHeight - y);
    if (w < 1 || h < 1) return null;
    return { x, y, width: w, height: h };
}

function outputFileName(originalName, mime) {
    const base =
        typeof originalName === "string" && originalName.length > 0
            ? originalName.replace(/\.[^/.]+$/, "")
            : "image";
    const ext = mime === "image/png" ? "png" : "jpg";
    return `${base}.${ext}`;
}

/**
 * Draw `sourceCanvas` into `destCanvas` scaled to fit max bounds (high-quality downscale).
 */
function drawScaled(
    sourceCanvas,
    destWidth,
    destHeight,
    isOpaqueBackground = true
) {
    const out = document.createElement("canvas");
    out.width = destWidth;
    out.height = destHeight;
    const octx = out.getContext("2d");
    if (!octx) return sourceCanvas;
    octx.imageSmoothingEnabled = true;
    octx.imageSmoothingQuality = "high";
    if (isOpaqueBackground) {
        octx.fillStyle = "#ffffff";
        octx.fillRect(0, 0, destWidth, destHeight);
    }
    octx.drawImage(
        sourceCanvas,
        0,
        0,
        sourceCanvas.width,
        sourceCanvas.height,
        0,
        0,
        destWidth,
        destHeight
    );
    return out;
}

function computeTargetSize(mode, srcW, srcH) {
    if (mode === "avatar") {
        if (srcW <= AVATAR_MAX_PX && srcH <= AVATAR_MAX_PX) return null;
        const scale = AVATAR_MAX_PX / Math.max(srcW, srcH);
        return {
            w: Math.round(srcW * scale),
            h: Math.round(srcH * scale),
        };
    }
    if (mode === "cover") {
        if (srcW <= COVER_MAX_W && srcH <= COVER_MAX_H) return null;
        const scale = Math.min(COVER_MAX_W / srcW, COVER_MAX_H / srcH, 1);
        return {
            w: Math.round(srcW * scale),
            h: Math.round(srcH * scale),
        };
    }
    return null;
}

export default async function getCroppedImageFile(
    imageURL,
    croppedAreaPixels,
    name,
    options = {}
) {
    const mode = options.mode === "cover" ? "cover" : "avatar";

    const image = new Image();
    image.crossOrigin = "anonymous";
    image.src = imageURL;

    await new Promise((resolve, reject) => {
        image.onload = () => resolve();
        image.onerror = () =>
            reject(new Error("Failed to load image for crop"));
    });

    const pixels = normalizeCropPixels(
        croppedAreaPixels,
        image.naturalWidth,
        image.naturalHeight
    );
    if (!pixels) {
        throw new Error("Invalid crop region");
    }

    const canvas = document.createElement("canvas");
    canvas.width = pixels.width;
    canvas.height = pixels.height;
    const ctx = canvas.getContext("2d");
    if (!ctx) {
        throw new Error("Canvas not supported");
    }
    ctx.imageSmoothingEnabled = true;
    ctx.imageSmoothingQuality = "high";

    ctx.drawImage(
        image,
        pixels.x,
        pixels.y,
        pixels.width,
        pixels.height,
        0,
        0,
        pixels.width,
        pixels.height
    );

    let exportCanvas = canvas;
    const target = computeTargetSize(mode, canvas.width, canvas.height);
    if (target) {
        exportCanvas = drawScaled(canvas, target.w, target.h, true);
    }

    const mime = "image/jpeg";
    const blob = await new Promise((resolve, reject) => {
        exportCanvas.toBlob(
            (b) => {
                if (b) resolve(b);
                else reject(new Error("Could not encode image"));
            },
            mime,
            JPEG_QUALITY
        );
    });

    const fileName = outputFileName(name, mime);
    return new File([blob], fileName, { type: mime });
}
