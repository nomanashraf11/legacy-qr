import momenttz from "moment-timezone";
import moment from "moment";

// Format Date Helper Functions

const getMaxCharsPerLine = () => {
    if (window?.innerWidth < 480) return 15; // Small screens
    if (window?.innerWidth < 768) return 35; // Medium screens
    return 30; // Large screens
};

export const textWillWrap = (text) => text?.length > getMaxCharsPerLine();

export const dateMMDDYYYYFormat = (providedDate) => {
    if (providedDate == null || providedDate === "") {
        return "";
    }
    const timeZone = momenttz.tz.guess(true);
    const m = momenttz.tz(providedDate, timeZone);
    if (!m.isValid()) {
        return "";
    }
    return m.format("MMM D, YYYY");
};

export const dateYYYYMMDDFormat = (providedDate) => {
    const timeZone = momenttz.tz.guess(true);
    const date = moment.tz(providedDate, timeZone).toDate();
    return moment(date).format("YYYY-MM-DD");
};

export const formatDateStrToDate = (providedDate) => {
    const timeZone = momenttz.tz.guess(true);
    const formattedDate = moment.tz(providedDate, timeZone).toDate();
    return formattedDate;
};

// End Format Date Helper Functions

export const isValidDate = (date) => {
    return date && !isNaN(date.getTime());
};

/** True when the API returned a real death date; empty/null means still living. */
export function hasRecordedDeathDate(dod) {
    if (dod == null) return false;
    if (typeof dod === "string" && dod.trim() === "") return false;
    return true;
}

/**
 * Birth – death (or Present) for family tree cards. Omits "Invalid date" when DOB/DOD is missing.
 * "Present" is only shown when a birth date exists; otherwise there is no range to anchor "still living".
 * No placeholder characters when dates are absent (empty string).
 * @param {string} [presentLabel="Present"] - e.g. lowercase "present" for the root profile node
 */
export function formatRelationLifeSpan(dob, dod, presentLabel = "Present") {
    const birthFormatted = dateMMDDYYYYFormat(dob);
    const hasBirth = birthFormatted !== "";

    if (!hasRecordedDeathDate(dod)) {
        if (!hasBirth) {
            return "";
        }
        return `${birthFormatted} - ${presentLabel}`;
    }

    const deathFormatted = dateMMDDYYYYFormat(dod);
    if (!hasBirth) {
        return deathFormatted || "";
    }
    return `${birthFormatted} - ${deathFormatted}`;
}

export const calculateTimeAgo = (givenDate) => {
    const currentDate = new Date();
    const timeDifference = currentDate - new Date(givenDate);
    const seconds = Math.floor(timeDifference / 1000);
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);
    const months = Math.floor(days / 30);
    const years = Math.floor(months / 12);
    if (years > 0) {
        return years === 1 ? "a year ago" : `${years} years ago`;
    } else if (months > 0) {
        return months === 1 ? "a month ago" : `${months} months ago`;
    } else if (days > 0) {
        return days === 1 ? "a day ago" : `${days} days ago`;
    } else if (hours > 0) {
        return hours === 1 ? "an hour ago" : `${hours} hours ago`;
    } else if (minutes > 0) {
        return minutes === 1 ? "a minute ago" : `${minutes} minutes ago`;
    } else {
        return seconds === 1 ? "a second ago" : `${seconds} seconds ago`;
    }
};

export function checkFileType(url) {
    const extension = url?.split(".")?.pop()?.toLowerCase?.();
    if (imageExtensions.includes(extension)) {
        return "image";
    }
    if (videoExtensions.includes(extension)) {
        return "video";
    }
    return null;
}

const imageExtensions = ["jpg", "jpeg", "png", "gif", "bmp"];
const videoExtensions = [
    "3g2",
    "3gp",
    "asf",
    "avi",
    "flv",
    "m4v",
    "mov",
    "mp4",
    "mpg",
    "mpeg",
    "mkv",
    "rm",
    "swf",
    "vob",
    "wmv",
    "webm",
    "ogg",
    "ogv",
    "mts",
    "m2ts",
    "ts",
];

export function objectToFormData(obj) {
    const formData = new FormData();
    for (const key in obj) {
        if (obj.hasOwnProperty(key)) {
            formData.append(key, obj[key]);
        }
    }
    return formData;
}

/** Merge profile/cover file uploads, removals, and stock cover into add_bio payload. */
export function appendLegacyMediaFields(payload, values) {
    const {
        cover_picture,
        profile_picture,
        remove_profile_picture,
        remove_cover_picture,
        stock_cover,
    } = values;
    if (profile_picture instanceof File) {
        payload.profile_picture = profile_picture;
    }
    if (cover_picture instanceof File) {
        payload.cover_picture = cover_picture;
    }
    if (remove_profile_picture) {
        payload.remove_profile_picture = "1";
    }
    if (remove_cover_picture) {
        payload.remove_cover_picture = "1";
    }
    if (stock_cover && !(cover_picture instanceof File)) {
        payload.stock_cover = stock_cover;
    }
    return payload;
}

/** True when `location` has usable coordinates (not cleared). Accepts strings or numbers from Formik / Maps. */
export function hasProfileMapCoords(location) {
    if (!location) return false;
    const { lat, lng } = location;
    if (lat === "" || lng === "" || lat == null || lng == null) return false;
    const pLat = typeof lat === "number" ? lat : parseFloat(String(lat).trim());
    const pLng = typeof lng === "number" ? lng : parseFloat(String(lng).trim());
    return !Number.isNaN(pLat) && !Number.isNaN(pLng);
}

/** Payload fields for add_bio: empty strings clear saved coordinates on the server. */
export function profileCoordinatesForPayload(location) {
    if (!hasProfileMapCoords(location)) {
        return { latitude: "", longitude: "" };
    }
    return { latitude: String(location.lat), longitude: String(location.lng) };
}

export const regex = {
    facebook: /^(https?:\/\/)?(www\.)?(m\.)?facebook.com\/[a-zA-Z0-9(\.\?)?]/,
    twitter: /^(https?:\/\/)?(www\.)?(x\.com|twitter\.com)\/[a-zA-Z0-9(\.\?)?]/,
    instagram: /^(https?:\/\/)?(www\.)?instagram.com\/[a-zA-Z0-9(\.\?)?]/,
    spotify:
        /^(https?:\/\/)?(www\.)?open.spotify.com\/(?:track|playlist)\/[a-zA-Z0-9]+/,
    youtube:
        /^(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/,
    password: /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/,
};

export function capitalizeFirstLetter(str) {
    if (!str) return "";
    if (str === "grandDaughter") {
        return "Granddaughter";
    }
    if (str === "grandSon") {
        return "Grandson";
    }
    // Convert camelCase and mixed-case words like "grandDAUGHTER" to "grand daughter"
    str = str.replace(/([a-z])([A-Z])/g, "$1 $2");

    // Ensure lowercase for all words except first letter

    return str
        .split(" ")
        .map(
            (word) => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()
        )
        .join(" ");
}

export function formatRelations(relation) {
    const mapping = {
        SON: "Son",
        DAUGHTER: "Daughter",
        BROTHER: "Brother",
        SISTER: "Sister",
        FATHER: "Father",
        MOTHER: "Mother",
        SPOUSE: "Spouse",
        MATERNALGRANDFATHER: "Maternal Grandfather",
        MATERNALGRANDMOTHER: "Maternal Grandmother",
        PATERNALGRANDFATHER: "Paternal Grandfather",
        PATERNALGRANDMOTHER: "Paternal Grandmother",
        PATERNALGREATGRANDFATHER: "Paternal Great-Grandfather",
        PATERNALGREATGRANDMOTHER: "Paternal Great-Grandmother",
        MATERNALGREATGRANDFATHER: "Maternal Great-Grandfather",
        MATERNALGREATGRANDMOTHER: "Maternal Great-Grandmother",
        GRANDDAUGHTER: "Granddaughter",
        GRANDSON: "Grandson",
        GREATGRANDSON: "Great-Grandson",
        GREATGRANDDAUGHTER: "Great-Granddaughter",
        SONINLAW: "Son In Law",
        DAUGHTERINLAW: "Daughter In Law",
    };

    return mapping[relation.toUpperCase()] || relation; // Convert input to uppercase & return mapped value
}
