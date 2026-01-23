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
  const timeZone = momenttz.tz.guess(true);
  const date = moment.tz(providedDate, timeZone).toDate();
  return moment(date).format("MMM D, YYYY");
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
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
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
    MATERNALGRANDFATHER: "Grandfather",
    MATERNALGRANDMOTHER: "Grandmother",
    PATERNALGRANDFATHER: "Grandfather",
    PATERNALGRANDMOTHER: "Grandmother",
    GRANDDAUGHTER: "Granddaughter",
    GRANDSON: "Grandson",
    SONINLAW: "Son In Law",
    DAUGHTERINLAW: "Daughter In Law",
  };

  return mapping[relation.toUpperCase()] || relation; // Convert input to uppercase & return mapped value
}
