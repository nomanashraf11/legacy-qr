import { useEffect, useRef, useState } from "react";
import { ErrorMessage, Field, Form, Formik } from "formik";
import * as Yup from "yup";
import { IoClose } from "react-icons/io5";
import { CiCamera } from "react-icons/ci";
import { TbPencil } from "react-icons/tb";
import { MapComponent } from "./Map";
import { useParams } from "react-router-dom";
import {
    appendLegacyMediaFields,
    formatDateStrToDate,
    hasProfileMapCoords,
    hasRecordedDeathDate,
    objectToFormData,
    profileCoordinatesForPayload,
    regex,
} from "../utils";
import {
    initialStockCoverFromUrl,
    STOCK_COVERS,
    stockCoverPreviewUrl,
} from "../constants/stockCovers";
import { FileUploader } from "react-drag-drop-files";
import "./imagedrag.css";
import {
    getAuthToken,
    getRemembered,
    getUserData,
    useAppSelector,
} from "../redux";
import { ImageCropModal } from "./ImageCropModal";
import { MdDone } from "react-icons/md";
import { GrLinkPrevious } from "react-icons/gr";
import { FormikDatePicker } from "./FormikDatePicker";
import { DeathDateChoice } from "./DeathDateChoice";
import { toast } from "react-toastify";
import { API_BASE_URL } from "../config";
import { format } from "date-fns";
import { v4 as uuidv4 } from "uuid";

/** Empty optional social/URL fields must stay valid (Yup .matches() fails on ""). */
const optionalSocialUrl = (pattern, invalidMessage) =>
    Yup.string()
        .nullable()
        .test("optional-url", invalidMessage, (value) => {
            if (value == null || String(value).trim() === "") {
                return true;
            }
            return pattern.test(String(value));
        });

function nonEmptyStr(x) {
    return typeof x === "string" && x.trim() !== "";
}

const services = [
    { name: "Army", value: "army" },
    { name: "Navy", value: "navy" },
    { name: "Air Force", value: "air_force" },
    { name: "Coast Guard", value: "coast_guard" },
    { name: "Space Force", value: "space_force" },
    { name: "Marines", value: "marine_corps" },
    { name: "Police", value: "police" },
    { name: "Paramedic", value: "paramedic" },
    { name: "Firefighter", value: "fire" },
];

const validationSchemaStep1 = Yup.object().shape({
    name: Yup.string()
        .min(2, "Name should be at least 2 characters long")
        .required("Name is required"),
    dob: Yup.mixed().required("Date of birth is required"),
    dod: Yup.mixed().notRequired(),
    spouseDob: Yup.mixed().notRequired("Date of birth is required"),
    spouseName: Yup.string()
        .notRequired()
        .min(2, "Name should be at least 2 characters long"),
});

const validationSchemaBio = Yup.object().shape({
    bio: Yup.string().required("Bio is required"),
});

const validationSchemaPics = Yup.object()
    .shape({
        cover_picture: Yup.mixed().nullable(),
        profile_picture: Yup.mixed().nullable(),
        coverUrl: Yup.string().nullable(),
        profileUrl: Yup.string().nullable(),
        stock_cover: Yup.string().nullable(),
        // Must be in .shape() so Formik/Yup keep them through prepareDataForValidation.
        // Use mixed (not boolean) to avoid Yup 1.x boolean edge cases.
        remove_profile_picture: Yup.mixed().nullable(),
        remove_cover_picture: Yup.mixed().nullable(),
    })
    .test(
        "legacy-profile-photo",
        "Add a profile photo or remove the current one.",
        function (vals) {
            const v = vals || {};
            if (v.remove_profile_picture === true) {
                return true;
            }
            if (v.profile_picture instanceof File) {
                return true;
            }
            if (nonEmptyStr(v.profileUrl)) {
                return true;
            }
            if (
                nonEmptyStr(v.profile_picture) &&
                !(v.profile_picture instanceof File)
            ) {
                return true;
            }
            return this.createError({
                path: "profileUrl",
                message: "Profile image is required",
            });
        }
    )
    .test(
        "legacy-cover",
        "Add a cover (upload or stock) or remove the current one.",
        function (vals) {
            const v = vals || {};
            if (v.remove_cover_picture === true) {
                return true;
            }
            if (v.cover_picture instanceof File) {
                return true;
            }
            if (nonEmptyStr(v.stock_cover)) {
                return true;
            }
            if (nonEmptyStr(v.coverUrl)) {
                return true;
            }
            if (
                nonEmptyStr(v.cover_picture) &&
                !(v.cover_picture instanceof File)
            ) {
                return true;
            }
            return this.createError({
                path: "coverUrl",
                message:
                    "Cover image is required (upload or choose a stock cover)",
            });
        }
    );

/**
 * API/legacy forms often store persisted images as URL/path strings on
 * `profile_picture` / `cover_picture`, not only on *Url fields or File objects.
 * Yup previously only allowed File + *Url, so “valid” required touching cover
 * to set coverUrl/File — fixed here and in validationSchemaPics.
 */
function legacyMediaStepIsComplete(v) {
    if (!v) {
        return false;
    }
    const profileOk =
        v.remove_profile_picture === true ||
        v.profile_picture instanceof File ||
        nonEmptyStr(v.profileUrl) ||
        (nonEmptyStr(v.profile_picture) &&
            !(v.profile_picture instanceof File));
    const coverOk =
        v.remove_cover_picture === true ||
        v.cover_picture instanceof File ||
        nonEmptyStr(v.stock_cover) ||
        nonEmptyStr(v.coverUrl) ||
        (nonEmptyStr(v.cover_picture) && !(v.cover_picture instanceof File));
    return profileOk && coverOk;
}

/** Preview / crop: prefer *Url fields; fallback to API URL string on *_picture */
function legacyProfileDisplayUrl(v) {
    if (!v) {
        return "";
    }
    if (nonEmptyStr(v.profileUrl)) {
        return v.profileUrl;
    }
    if (
        nonEmptyStr(v.profile_picture) &&
        !(v.profile_picture instanceof File)
    ) {
        return v.profile_picture;
    }
    return "";
}

function legacyCoverDisplayUrl(v) {
    if (!v) {
        return "";
    }
    if (nonEmptyStr(v.coverUrl)) {
        return v.coverUrl;
    }
    if (nonEmptyStr(v.cover_picture) && !(v.cover_picture instanceof File)) {
        return v.cover_picture;
    }
    return "";
}

const validationSchemaStep2 = Yup.object().shape({
    spotify: Yup.string()
        .nullable()
        .test("spotify-or-empty", "Invalid Spotify URL", (value) => {
            if (value == null || String(value).trim() === "") {
                return true;
            }
            return regex.spotify.test(String(value));
        }),
});

const validationSchemaStep3 = Yup.object().shape({
    facebook: optionalSocialUrl(regex.facebook, "Invalid Facebook URL"),
    instagram: optionalSocialUrl(regex.instagram, "Invalid Instagram URL"),
    twitter: optionalSocialUrl(regex.twitter, "Invalid Twitter URL"),
});
const validationSchemaStep5 = Yup.object().shape({
    spouse_facebook: optionalSocialUrl(regex.facebook, "Invalid Facebook URL"),
    spouse_instagram: optionalSocialUrl(
        regex.instagram,
        "Invalid Instagram URL"
    ),
    spouse_twitter: optionalSocialUrl(regex.twitter, "Invalid Twitter URL"),
});
const fileTypes = [
    "JPG",
    "JPEG",
    "PNG",
    "GIF",
    "WEBP",
    "SVG",
    "BMP",
    "TIFF",
    "TIF",
    "ICO",
    "HEIC",
    "HEIF",
    "AVIF",
];

export const EditBioModal = ({
    open,
    setOpen,
    userData,
    refetch,
    step,
    setStep,
    setOpenSuccessModal,
}) => {
    const { id } = useParams();
    const appToken = useAppSelector(getAuthToken);
    const data = useAppSelector(getUserData);
    const remember = useAppSelector(getRemembered);

    const mapRef = useRef(null);

    const [openProfileCropModal, setOpenProfileCropModal] = useState(false);
    const [openCoverCropModal, setOpenCoverCropModal] = useState(false);

    const [mapVisible, setMapVisible] = useState(false);

    let initialValues = {
        name: userData?.name || "",
        cover_picture: userData?.cover_picture || "",
        coverUrl: userData?.cover_picture || "",
        profile_picture: userData?.profile_picture || "",
        profileUrl: userData?.profile_picture || "",
        stock_cover: initialStockCoverFromUrl(userData?.cover_picture) || "",
        remove_profile_picture: false,
        remove_cover_picture: false,
        dob: userData?.dob ? formatDateStrToDate(userData?.dob) : null,
        dod: userData?.dod ? formatDateStrToDate(userData?.dod) : null,
        bio: userData?.bio || "",
        facebook: userData?.facebook || "",
        instagram: userData?.instagram || "",
        twitter: userData?.twitter || "",
        spouse_facebook: userData?.spouse_facebook || "",
        spouse_instagram: userData?.spouse_instagram || "",
        spouse_twitter: userData?.spouse_twitter || "",
        spouse_badge: userData?.spouse_badge || "",
        spotify: userData?.spotify || "",
        youtube: userData?.youtube || "",
        // firefighter_badge: userData?.firefighter_badge || false,
        // police_badge: userData?.police_badge || false,
        // paramedic_badge: userData?.paramedic_badge || false,
        badge: userData?.badge,

        location: {
            lat:
                userData?.latitude != null &&
                String(userData.latitude).trim() !== ""
                    ? userData.latitude
                    : "",
            lng:
                userData?.longitude != null &&
                String(userData.longitude).trim() !== ""
                    ? userData.longitude
                    : "",
        },
        spouseDob: userData?.relations?.find(
            (value) => value.name.toLowerCase() === "spouse"
        )?.dob
            ? formatDateStrToDate(
                  userData.relations.find(
                      (value) => value.name.toLowerCase() === "spouse"
                  )?.dob
              )
            : null,
        spouseDod: userData?.relations?.find(
            (value) => value.name.toLowerCase() === "spouse"
        )?.dod
            ? formatDateStrToDate(
                  userData?.relations?.find(
                      (value) => value.name.toLowerCase() === "spouse"
                  )?.dod
              )
            : null,
        spouseBio:
            userData?.relations?.find(
                (value) => value.name.toLowerCase() === "spouse"
            )?.bio || "",
        spouseName:
            userData?.relations?.find(
                (value) => value.name.toLowerCase() === "spouse"
            )?.person_name || "",
    };

    const handleClose = () => {
        if (mapRef.current) {
            const map = mapRef.current;
            window.google.maps.event.clearInstanceListeners(map);
            mapRef.current = null;
        }
        setOpen(false);
        setStep(1);
    };

    const handleChange = (setFieldValue, file, key) => {
        if (key === "cover") {
            setFieldValue("cover_picture", file);
            setFieldValue("coverUrl", URL.createObjectURL(file));
            setFieldValue("stock_cover", "");
            setFieldValue("remove_cover_picture", false);
            setOpenCoverCropModal(true);
        } else {
            setFieldValue("profile_picture", file);
            setFieldValue("profileUrl", URL.createObjectURL(file));
            setFieldValue("remove_profile_picture", false);
            setOpenProfileCropModal(true);
        }
    };

    const [isPresent, setIsPresent] = useState(true);
    const [isPresentSpouse, setIsPresentSpouse] = useState(true);
    const [isSpouse, setIsSpouse] = useState(false);

    const handleSpouseToggle = (setFieldValue) => {
        setIsSpouse(!isSpouse);
        if (!isSpouse) {
            setFieldValue("spouseName", ""); // Clear the Date of Death if Present is selected
            setFieldValue("spouseDob", null);
            setFieldValue("spouseDod", null);
        }
    };

    const handleSubmit = async (values, { setSubmitting }) => {
        setSubmitting(true);
        try {
            const {
                location,
                cover_picture,
                profile_picture,
                dob,
                dod,
                remove_profile_picture,
                remove_cover_picture,
                stock_cover,
                ...bio
            } = values;
            let relations = userData?.relations || [];

            if (isSpouse) {
                // Check if "SPOUSE" entry exists
                const spouseIndex = relations.findIndex(
                    (relation) => relation.name.toUpperCase() === "SPOUSE"
                );

                if (spouseIndex >= 0) {
                    // Update "SPOUSE" entry with new data
                    relations = relations.map((relation) => {
                        if (relation.name.toUpperCase() === "SPOUSE") {
                            return {
                                ...relation,
                                uuid: uuidv4(),
                                is_legacy: true,
                                person_name: bio?.spouseName || "",
                                bio: bio?.spouseBio || "",
                                dod: bio?.spouseDod
                                    ? format(bio.spouseDod, "yyyy-MM-dd")
                                    : null,
                                dob: bio?.spouseDob
                                    ? format(bio.spouseDob, "yyyy-MM-dd")
                                    : null,
                            };
                        }
                        return relation; // Keep other relations unchanged
                    });
                } else {
                    // Add "SPOUSE" entry if it doesn't exist
                    relations = [
                        ...relations,
                        {
                            uuid: uuidv4(),
                            name: "SPOUSE",
                            is_legacy: true,
                            person_name: bio?.spouseName || "",
                            bio: bio?.spouseBio || "",
                            dod: bio?.spouseDod
                                ? format(bio.spouseDod, "yyyy-MM-dd")
                                : null,
                            dob: bio?.spouseDob
                                ? format(bio.spouseDob, "yyyy-MM-dd")
                                : null,
                        },
                    ];
                }
            } else {
                // Remove "SPOUSE" entry if isSpouse is false
                relations = relations.filter(
                    (relation) => relation.name.toUpperCase() !== "SPOUSE"
                );
            }

            const payload = {
                ...bio,
                dark_theme: userData?.dark_theme,
                ...profileCoordinatesForPayload(location),
                dob: format(dob, "yyyy-MM-dd"),
                relations: JSON.stringify(relations),
            };
            if (dod) {
                payload.dod = dod ? format(dod, "yyyy-MM-dd") : null;
            }

            appendLegacyMediaFields(payload, values);

            const body = objectToFormData(payload);

            const response = await fetch(
                `${API_BASE_URL}/${id}/add_bio?is_legacy=true`,
                {
                    method: "POST",
                    body,
                    headers: {
                        Accept: "application/json",
                        Authorization: `Bearer ${appToken}`,
                        "X-Custom-Header": "header value",
                    },
                }
            ).then((res) => res.json());

            if (response.status === 200) {
                toast.success("Profile updated successfully");
                setStep(1);
                refetch();
                handleClose();
                if (!remember) {
                    setOpenSuccessModal(true);
                }
            } else {
                toast.error("Profile update failed");
            }
        } catch (error) {
            console.log("error", error);
        } finally {
            setSubmitting(false);
        }
    };

    const handleSubmitSteps = async (values, { setSubmitting }) => {
        setSubmitting(true);
        try {
            const {
                location,
                cover_picture,
                profile_picture,
                dob,
                dod,
                remove_profile_picture,
                remove_cover_picture,
                stock_cover,
                ...bio
            } = values;
            let relations = userData?.relations || [];

            if (isSpouse) {
                // Check if "SPOUSE" entry exists
                const spouseIndex = relations.findIndex(
                    (relation) => relation.name.toUpperCase() === "SPOUSE"
                );

                if (spouseIndex >= 0) {
                    // Update "SPOUSE" entry with new data
                    relations = relations.map((relation) => {
                        if (relation.name.toUpperCase() === "SPOUSE") {
                            return {
                                ...relation,
                                uuid: uuidv4(),
                                is_legacy: true,
                                person_name: bio?.spouseName || "",
                                bio: bio?.spouseBio || "",
                                dod: bio?.spouseDod
                                    ? format(bio.spouseDod, "yyyy-MM-dd")
                                    : null,
                                dob: bio?.spouseDob
                                    ? format(bio.spouseDob, "yyyy-MM-dd")
                                    : null,
                            };
                        }
                        return relation; // Keep other relations unchanged
                    });
                } else {
                    // Add "SPOUSE" entry if it doesn't exist
                    relations = [
                        ...relations,
                        {
                            uuid: uuidv4(),
                            name: "SPOUSE",
                            is_legacy: true,
                            person_name: bio?.spouseName || "",
                            bio: bio?.spouseBio || "",
                            dod: bio?.spouseDod
                                ? format(bio.spouseDod, "yyyy-MM-dd")
                                : null,
                            dob: bio?.spouseDob
                                ? format(bio.spouseDob, "yyyy-MM-dd")
                                : null,
                        },
                    ];
                }
            } else {
                // Remove "SPOUSE" entry if isSpouse is false
                relations = relations.filter(
                    (relation) => relation.name.toUpperCase() !== "SPOUSE"
                );
            }
            const payload = {
                ...bio,
                dark_theme: userData?.dark_theme,
                ...profileCoordinatesForPayload(location),
                relations: JSON.stringify(relations),
                dob: format(dob, "yyyy-MM-dd"),
            };

            if (dod) {
                payload.dod = dod ? format(dod, "yyyy-MM-dd") : null;
            }

            appendLegacyMediaFields(payload, values);

            const body = objectToFormData(payload);

            const response = await fetch(
                `${API_BASE_URL}/${id}/add_bio?is_legacy=true`,
                {
                    method: "POST",
                    body,
                    headers: {
                        Accept: "application/json",
                        Authorization: `Bearer ${appToken}`,
                        "X-Custom-Header": "header value",
                    },
                }
            ).then((res) => res.json());

            if (response.status === 200) {
                toast.success("Profile updated successfully");
                refetch();
            } else {
                toast.error("Profile update failed");
            }
        } catch (error) {
            console.log("error", error);
        } finally {
            setSubmitting(false);
        }
    };

    useEffect(() => {
        const spouse = userData?.relations?.filter(
            (value) => value.name.toLowerCase() === "spouse"
        )[0];

        const principalDod = userData?.dod ?? data?.dod;
        setIsPresent(!hasRecordedDeathDate(principalDod));

        const spouseDodRaw = userData?.relations?.find(
            (value) => value.name.toLowerCase() === "spouse"
        )?.dod;
        setIsPresentSpouse(!hasRecordedDeathDate(spouseDodRaw));

        if (spouse?.person_name && spouse?.is_legacy) {
            setIsSpouse(true);
        }
        if (data?.latitude) {
            setMapVisible(true);
        } else {
            setMapVisible(false);
        }
    }, [data, userData]);

    useEffect(() => {
        const spouse = userData?.relations?.filter(
            (value) => value.name.toLowerCase() === "spouse"
        );
        initialValues = {
            name: userData?.name || "",
            spouseName: spouse?.person_name || "",
            cover_picture: userData?.cover_picture || "",
            coverUrl: userData?.cover_picture || "",
            profile_picture: userData?.profile_picture || "",
            profileUrl: userData?.profile_picture || "",
            stock_cover:
                initialStockCoverFromUrl(userData?.cover_picture) || "",
            remove_profile_picture: false,
            remove_cover_picture: false,
            dob: userData?.dob ? formatDateStrToDate(userData?.dob) : null,
            spouseDob: spouse?.dob ? formatDateStrToDate(spouse.dob) : null,
            spouseDod: spouse?.dod ? formatDateStrToDate(userData?.dod) : null,
            dod: userData?.dod ? formatDateStrToDate(userData?.dod) : null,
            bio: userData?.bio || "",
            spouseBio: spouse?.bio || "",
            facebook: userData?.facebook || "",
            instagram: userData?.instagram || "",
            twitter: userData?.twitter || "",
            spouse_facebook: userData?.spouse_facebook || "",
            spouse_instagram: userData?.spouse_instagram || "",
            spouse_twitter: userData?.spouse_twitter || "",
            spotify: userData?.spotify || "",
            youtube: userData?.youtube || "",
            location: {
                lat:
                    userData?.latitude != null &&
                    String(userData.latitude).trim() !== ""
                        ? userData.latitude
                        : "",
                lng:
                    userData?.longitude != null &&
                    String(userData.longitude).trim() !== ""
                        ? userData.longitude
                        : "",
            },
        };
    }, [open]);

    useEffect(() => {
        if (!open) {
            setStep(1);
        }
    }, [open]);
    return (
        <Formik
            validateOnMount
            enableReinitialize
            validateOnChange
            validateOnBlur
            initialValues={initialValues}
            onSubmit={handleSubmit}
            validationSchema={
                step === 1
                    ? validationSchemaStep1
                    : step === 2
                    ? validationSchemaBio
                    : step === 3
                    ? validationSchemaPics
                    : step === 4
                    ? validationSchemaStep2
                    : step === 5
                    ? validationSchemaStep3
                    : step === 6
                    ? validationSchemaStep5
                    : validationSchemaStep3
            }
        >
            {({
                values,
                setFieldValue,
                setValues,
                isValid,
                isSubmitting,
                handleBlur,
                errors,
                submitForm,
                setSubmitting,
            }) => {
                // Step 3: don’t use `errors` or global `isValid` — both lag async Yup / can stay stale.
                const step3MediaBlocked =
                    step === 3 && !legacyMediaStepIsComplete(values);
                const primaryActionDisabled =
                    isSubmitting || (step === 3 ? step3MediaBlocked : !isValid);
                const nextStepDisabled =
                    step === 3 ? step3MediaBlocked : !isValid;

                return (
                    <Form>
                        <div
                            className={
                                "bg-black backdrop-filter backdrop-blur-sm bg-opacity-60 overflow-x-hidden fixed top-0 right-0 left-0 bottom-0 max-h-full !z-50 flex justify-center items-center w-full md:inset-0 transition-all duration-200 ease-in-out " +
                                (open ? "block" : "hidden")
                            }
                        >
                            <div className="relative p-4 w-full max-w-[500px] sm:max-w-2xl max-h-full">
                                <div
                                    className={`${
                                        userData?.dark_theme
                                            ? "bg-[#242526]"
                                            : "bg-white"
                                    } relative  rounded-lg shadow`}
                                >
                                    <div className="flex items-center justify-between p-4 border-b relative border-white/5 rounded-t">
                                        <h3 className="sm:text-xl font-semibold text-center w-full pr-8 sm:pr-0">
                                            {step === 1
                                                ? "Add Legacy information below"
                                                : step === 2
                                                ? "Add Legacy Story"
                                                : step === 3
                                                ? `Add or update profile and cover photo`
                                                : step === 4
                                                ? "Add Spotify Playlist"
                                                : step === 5
                                                ? `Add ${
                                                      values.name ||
                                                      userData?.name ||
                                                      "your loved one"
                                                  }’s social media links`
                                                : step === 6
                                                ? `Add ${
                                                      values.spouseName ||
                                                      userData?.relations?.find(
                                                          (value) =>
                                                              value.name.toLowerCase() ===
                                                              "spouse"
                                                      )?.person_name ||
                                                      "spouse"
                                                  }’s social media links`
                                                : "Add final resting place"}
                                        </h3>

                                        <div className="absolute top-0 right-0 left-0 bottom-0 flex items-center justify-end p-4">
                                            <button
                                                onClick={handleClose}
                                                type="button"
                                                className="text-gray-400 transition-all duration-200 ease-in-out bg-white bg-opacity-20 hover:bg-opacity-25 active:bg-opacity-30 rounded-full text-sm w-8 h-8 ms-auto inline-flex justify-center items-center"
                                            >
                                                <IoClose size={24} />
                                            </button>
                                        </div>
                                    </div>
                                    <div
                                        onScroll={(e) => e.stopPropagation()}
                                        className="z-1000 h-[400px] overflow-auto p-4 md:p-6 relative"
                                    >
                                        {/* Step 1: Basic Info */}
                                        {step === 1 && (
                                            <div>
                                                <div className="space-y-4">
                                                    <div>
                                                        <label
                                                            className={` ${
                                                                userData?.dark_theme
                                                                    ? "text-white"
                                                                    : "text-black"
                                                            }block mb-2 text-sm font-medium `}
                                                            htmlFor="name"
                                                        >
                                                            Add loved one’s name
                                                        </label>
                                                        <Field
                                                            onBlur={handleBlur}
                                                            name="name"
                                                            className={`${
                                                                userData?.dark_theme
                                                                    ? "bg-[#333333] border-white/20 placeholder-gray-400 text-white"
                                                                    : "bg-[#F1F1F1] border-white/20 placeholder text-black"
                                                            } border outline-none text-sm rounded-lg block w-full p-2.5 `}
                                                            placeholder="Full Name"
                                                        />
                                                        <ErrorMessage
                                                            className="text-red-400 text-xs"
                                                            name="name"
                                                            component="div"
                                                        />
                                                    </div>
                                                    <div className="flex  flex-col gap-3">
                                                        <label>
                                                            Add{" "}
                                                            {values.name ||
                                                                userData?.name ||
                                                                "your loved one"}{" "}
                                                            honor badge, if
                                                            applicable
                                                        </label>
                                                        <Field
                                                            as="select"
                                                            value={values.badge}
                                                            onBlur={handleBlur}
                                                            name={`badge`}
                                                            className={`${
                                                                userData?.dark_theme
                                                                    ? "bg-[#333333] border-white/20 placeholder-gray-400 text-white"
                                                                    : "bg-[#F1F1F1] border-white/20 placeholder text-black"
                                                            } border outline-none text-sm rounded-lg block w-full p-2.5 `}
                                                        >
                                                            <option value="">
                                                                --Please choose
                                                                an option--
                                                            </option>
                                                            {services.map(
                                                                (service) => (
                                                                    <option
                                                                        key={
                                                                            service.value
                                                                        }
                                                                        value={
                                                                            service.value
                                                                        }
                                                                    >
                                                                        {
                                                                            service.name
                                                                        }
                                                                    </option>
                                                                )
                                                            )}
                                                        </Field>
                                                    </div>
                                                    <div className="relative z-[1]">
                                                        <label
                                                            className={` ${
                                                                userData?.dark_theme
                                                                    ? "text-white"
                                                                    : "text-black"
                                                            }block mb-2 text-sm font-medium `}
                                                            htmlFor="dob"
                                                        >
                                                            Add{" "}
                                                            {values.name ||
                                                                userData?.name ||
                                                                "loved one"}
                                                            ’s Date of Birth
                                                        </label>
                                                        <Field
                                                            name={"dob"}
                                                            id={"bio-dob"}
                                                            component={
                                                                FormikDatePicker
                                                            }
                                                        />
                                                        {errors.dob && (
                                                            <div className="text-red-400 text-xs">
                                                                {errors.dob}
                                                            </div>
                                                        )}
                                                    </div>

                                                    <div>
                                                        <label
                                                            className={` ${
                                                                userData?.dark_theme
                                                                    ? "text-white"
                                                                    : "text-black"
                                                            }block mb-2 text-sm font-medium `}
                                                            htmlFor="dod"
                                                        >
                                                            Date of Death
                                                        </label>
                                                        <p
                                                            className={`text-sm mb-3 ${
                                                                userData?.dark_theme
                                                                    ? "text-white/70"
                                                                    : "text-gray-600"
                                                            }`}
                                                        >
                                                            Choose whether this
                                                            person has passed.
                                                            If they are still
                                                            living, pick{" "}
                                                            <strong>
                                                                Still living
                                                            </strong>
                                                            . Otherwise choose{" "}
                                                            <strong>
                                                                Has passed
                                                            </strong>{" "}
                                                            and enter the date.
                                                        </p>
                                                        <DeathDateChoice
                                                            idPrefix="editbio-principal-dod"
                                                            isLiving={isPresent}
                                                            darkTheme={
                                                                userData?.dark_theme
                                                            }
                                                            fieldName="dod"
                                                            livingHelp="No date of death to enter for this loved one."
                                                            deceasedHelp="Enter their full date of death below."
                                                            pickerClassName="border outline-none text-sm rounded-lg block w-full p-2.5 bg-gray-700 border-gray-600 placeholder-gray-400 text-white focus:ring-blue-500 focus:border-blue-500"
                                                            onLiving={() => {
                                                                setIsPresent(
                                                                    true
                                                                );
                                                                setFieldValue(
                                                                    "dod",
                                                                    null
                                                                );
                                                            }}
                                                            onDeceased={() =>
                                                                setIsPresent(
                                                                    false
                                                                )
                                                            }
                                                        />
                                                        <ErrorMessage
                                                            name="dod"
                                                            component="div"
                                                            className="text-red-500 text-sm mt-2"
                                                        />
                                                    </div>
                                                </div>
                                                <div className="space-y-4">
                                                    <div>
                                                        <label className="flex items-center gap-2 mt-3">
                                                            <input
                                                                type="checkbox"
                                                                checked={
                                                                    isSpouse
                                                                }
                                                                onChange={() =>
                                                                    handleSpouseToggle(
                                                                        setFieldValue
                                                                    )
                                                                }
                                                                className="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                                                            />
                                                            Add Spouse
                                                        </label>
                                                        {isSpouse && (
                                                            <div className="flex flex-col mt-4 border-t -2 py-2 gap-2">
                                                                <div>
                                                                    <label
                                                                        className={` ${
                                                                            userData?.dark_theme
                                                                                ? "text-white"
                                                                                : "text-black"
                                                                        }block mb-2 text-sm font-medium `}
                                                                        htmlFor="name"
                                                                    >
                                                                        Add
                                                                        Spouse’s
                                                                        name
                                                                    </label>
                                                                    <Field
                                                                        onBlur={
                                                                            handleBlur
                                                                        }
                                                                        name="spouseName"
                                                                        className={`${
                                                                            userData?.dark_theme
                                                                                ? "bg-[#333333] border-white/20 placeholder-gray-400 text-white"
                                                                                : "bg-[#F1F1F1] border-white/20 placeholder text-black"
                                                                        } border outline-none text-sm rounded-lg block w-full p-2.5 `}
                                                                        placeholder="Full Name"
                                                                    />
                                                                    <ErrorMessage
                                                                        className="text-red-400 text-xs"
                                                                        name="name"
                                                                        component="div"
                                                                    />
                                                                </div>

                                                                <div className="flex  flex-col gap-3">
                                                                    <label>
                                                                        Add
                                                                        spouse’s
                                                                        honor
                                                                        badge,
                                                                        if
                                                                        applicable
                                                                    </label>
                                                                    <Field
                                                                        as="select"
                                                                        value={
                                                                            values.spouse_badge
                                                                        }
                                                                        onBlur={
                                                                            handleBlur
                                                                        }
                                                                        name={`spouse_badge`}
                                                                        className={`${
                                                                            userData?.dark_theme
                                                                                ? "bg-[#333333] border-white/20 placeholder-gray-400 text-white"
                                                                                : "bg-[#F1F1F1] border-white/20 placeholder text-black"
                                                                        } border outline-none text-sm rounded-lg block w-full p-2.5 `}
                                                                    >
                                                                        <option value="">
                                                                            --Please
                                                                            choose
                                                                            an
                                                                            option--
                                                                        </option>
                                                                        {services.map(
                                                                            (
                                                                                service
                                                                            ) => (
                                                                                <option
                                                                                    key={
                                                                                        service.value
                                                                                    }
                                                                                    value={
                                                                                        service.value
                                                                                    }
                                                                                >
                                                                                    {
                                                                                        service.name
                                                                                    }
                                                                                </option>
                                                                            )
                                                                        )}
                                                                    </Field>
                                                                </div>
                                                                <div className="relative z-[1]">
                                                                    <label
                                                                        className={` ${
                                                                            userData?.dark_theme
                                                                                ? "text-white"
                                                                                : "text-black"
                                                                        }block mb-2 text-sm font-medium `}
                                                                        htmlFor="dob"
                                                                    >
                                                                        Add
                                                                        Spouse’s
                                                                        Date of
                                                                        Birth
                                                                    </label>
                                                                    <Field
                                                                        name={
                                                                            "spouseDob"
                                                                        }
                                                                        id={
                                                                            "bio-dob"
                                                                        }
                                                                        component={
                                                                            FormikDatePicker
                                                                        }
                                                                    />
                                                                    {errors.spouseDob && (
                                                                        <div className="text-red-400 text-xs">
                                                                            {
                                                                                errors.spouseDob
                                                                            }
                                                                        </div>
                                                                    )}
                                                                </div>

                                                                <div>
                                                                    <label
                                                                        className={` ${
                                                                            userData?.dark_theme
                                                                                ? "text-white"
                                                                                : "text-black"
                                                                        }block mb-2 text-sm font-medium `}
                                                                        htmlFor="spouseDod"
                                                                    >
                                                                        Date of
                                                                        Death
                                                                    </label>
                                                                    <p
                                                                        className={`text-sm mb-3 ${
                                                                            userData?.dark_theme
                                                                                ? "text-white/70"
                                                                                : "text-gray-600"
                                                                        }`}
                                                                    >
                                                                        Same
                                                                        choice
                                                                        for your
                                                                        spouse:{" "}
                                                                        <strong>
                                                                            Still
                                                                            living
                                                                        </strong>{" "}
                                                                        or{" "}
                                                                        <strong>
                                                                            Has
                                                                            passed
                                                                        </strong>{" "}
                                                                        with a
                                                                        date.
                                                                    </p>
                                                                    <DeathDateChoice
                                                                        idPrefix="editbio-spouse-dod"
                                                                        isLiving={
                                                                            isPresentSpouse
                                                                        }
                                                                        darkTheme={
                                                                            userData?.dark_theme
                                                                        }
                                                                        fieldName="spouseDod"
                                                                        livingHelp="No date of death for your spouse (they are living)."
                                                                        deceasedHelp="Enter your spouse's full date of death below."
                                                                        pickerClassName="border outline-none text-sm rounded-lg block w-full p-2.5 bg-gray-700 border-gray-600 placeholder-gray-400 text-white focus:ring-blue-500 focus:border-blue-500"
                                                                        onLiving={() => {
                                                                            setIsPresentSpouse(
                                                                                true
                                                                            );
                                                                            setFieldValue(
                                                                                "spouseDod",
                                                                                null
                                                                            );
                                                                        }}
                                                                        onDeceased={() =>
                                                                            setIsPresentSpouse(
                                                                                false
                                                                            )
                                                                        }
                                                                    />
                                                                    <ErrorMessage
                                                                        name="spouseDod"
                                                                        component="div"
                                                                        className="text-red-500 text-sm mt-2"
                                                                    />
                                                                </div>
                                                            </div>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>
                                        )}

                                        {/* Step 2: Add bio */}
                                        {step === 2 && (
                                            <div>
                                                <label
                                                    className={` ${
                                                        userData?.dark_theme
                                                            ? "text-white"
                                                            : "text-black"
                                                    }block mb-2 text-sm font-medium `}
                                                    htmlFor="bio"
                                                >
                                                    Add{" "}
                                                    {(values?.name ||
                                                        userData?.name ||
                                                        "loved one") +
                                                        "’s"}{" "}
                                                    story or copy/paste their
                                                    obituary here.
                                                </label>
                                                <div>
                                                    <textarea
                                                        rows={4}
                                                        maxLength={12000}
                                                        className={`${
                                                            userData?.dark_theme
                                                                ? "bg-[#333333] text-white "
                                                                : "bg-[#F1F1F1] text-black "
                                                        }text-base block rounded border border-white/20 p-3 w-full outline-none`}
                                                        placeholder="Write bio here..."
                                                        value={values.bio}
                                                        name="bio"
                                                        onChange={(e) =>
                                                            setFieldValue(
                                                                "bio",
                                                                e.target.value
                                                            )
                                                        }
                                                        onBlur={handleBlur}
                                                    />
                                                    <ErrorMessage
                                                        className="text-red-400 text-xs"
                                                        name="bio"
                                                        component="div"
                                                    />
                                                </div>

                                                <div className="mt-4">
                                                    {userData?.relations?.find(
                                                        (value) =>
                                                            value.name.toLowerCase() ===
                                                                "spouse" &&
                                                            value.is_legacy
                                                    )?.person_name ||
                                                    (values?.spouseName &&
                                                        values?.is_legacy) ? (
                                                        <div>
                                                            <label
                                                                className={`${
                                                                    userData?.dark_theme
                                                                        ? "text-white"
                                                                        : "text-black"
                                                                } block mb-2 text-sm font-medium`}
                                                                htmlFor="bio"
                                                            >
                                                                {`Add ${
                                                                    userData?.relations?.find(
                                                                        (
                                                                            value
                                                                        ) =>
                                                                            value.name.toLowerCase() ===
                                                                            "spouse"
                                                                    )
                                                                        ?.person_name ||
                                                                    "Spouse"
                                                                }’s story or copy/paste their obituary here.`}
                                                            </label>

                                                            <div>
                                                                <textarea
                                                                    rows={4}
                                                                    maxLength={
                                                                        12000
                                                                    }
                                                                    className={`${
                                                                        userData?.dark_theme
                                                                            ? "bg-[#333333] text-white "
                                                                            : "bg-[#F1F1F1] text-black"
                                                                    }text-base block rounded border border-white/20 p-3 w-full outline-none`}
                                                                    placeholder="Write Spouse bio here..."
                                                                    value={
                                                                        values.spouseBio
                                                                    }
                                                                    name="spouseBio"
                                                                    onChange={(
                                                                        e
                                                                    ) =>
                                                                        setFieldValue(
                                                                            "spouseBio",
                                                                            e
                                                                                .target
                                                                                .value
                                                                        )
                                                                    }
                                                                    onBlur={
                                                                        handleBlur
                                                                    }
                                                                />
                                                                <ErrorMessage
                                                                    className="text-red-400 text-xs"
                                                                    name="bio"
                                                                    component="div"
                                                                />
                                                            </div>
                                                        </div>
                                                    ) : (
                                                        ""
                                                    )}
                                                </div>
                                            </div>
                                        )}

                                        {/* Step 3: Profile and cover */}
                                        {step === 3 && (
                                            <div className="space-y-4">
                                                <div className="flex flex-col items-center justify-center">
                                                    <label
                                                        className={`block mb-2 text-sm font-medium ${
                                                            userData?.dark_theme
                                                                ? "text-white"
                                                                : "text-neutral-800"
                                                        }`}
                                                        htmlFor="name"
                                                    >
                                                        Profile Picture
                                                    </label>
                                                    <div
                                                        className={`relative w-32 h-32 border-2 rounded-full overflow-hidden group ${
                                                            userData?.dark_theme
                                                                ? "border-white/20"
                                                                : "border-neutral-300 bg-neutral-50 ring-1 ring-neutral-200/90 shadow-sm"
                                                        }`}
                                                    >
                                                        {/* Profile Image */}
                                                        <img
                                                            className="w-full h-full object-cover rounded-full"
                                                            src={legacyProfileDisplayUrl(
                                                                values
                                                            )}
                                                            alt="Profile"
                                                        />

                                                        {/* Drag & Drop / Clickable Label */}
                                                        <label className="absolute inset-0 bg-black/40 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 group-hover:grayscale transition-all duration-300 cursor-pointer rounded-full border-2 border-dashed border-white">
                                                            <TbPencil
                                                                size={24}
                                                                className="text-white mb-2"
                                                            />
                                                            <span className="text-white text-sm"></span>

                                                            <FileUploader
                                                                classes="opacity-0"
                                                                multiple={false}
                                                                handleChange={(
                                                                    file
                                                                ) =>
                                                                    handleChange(
                                                                        setFieldValue,
                                                                        file,
                                                                        "profile"
                                                                    )
                                                                }
                                                                name="profile_picture"
                                                                types={
                                                                    fileTypes
                                                                }
                                                            />
                                                        </label>
                                                    </div>
                                                    <button
                                                        type="button"
                                                        className={`mt-3 text-sm underline ${
                                                            userData?.dark_theme
                                                                ? "text-blue-300"
                                                                : "text-blue-600"
                                                        }`}
                                                        onClick={() => {
                                                            setValues(
                                                                (prev) => ({
                                                                    ...prev,
                                                                    profile_picture:
                                                                        "",
                                                                    profileUrl:
                                                                        "",
                                                                    remove_profile_picture: true,
                                                                })
                                                            );
                                                        }}
                                                    >
                                                        Remove profile photo
                                                    </button>
                                                    <ErrorMessage
                                                        className="text-red-400 text-xs block mt-1"
                                                        name="profileUrl"
                                                        component="div"
                                                    />
                                                </div>

                                                <div className="relative w-full">
                                                    <label
                                                        className={`block mb-2 text-sm font-medium ${
                                                            userData?.dark_theme
                                                                ? "text-white"
                                                                : "text-neutral-800"
                                                        }`}
                                                        htmlFor="name"
                                                    >
                                                        Cover Picture
                                                    </label>
                                                    <p
                                                        className={`text-sm mb-2 ${
                                                            userData?.dark_theme
                                                                ? "text-neutral-400"
                                                                : "text-neutral-600"
                                                        }`}
                                                    >
                                                        Or choose a stock cover:
                                                    </p>
                                                    <div className="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-4">
                                                        {STOCK_COVERS.map(
                                                            ({
                                                                file,
                                                                label,
                                                            }) => (
                                                                <button
                                                                    key={file}
                                                                    type="button"
                                                                    className={`relative rounded-md overflow-hidden border-2 aspect-[3/1] ${
                                                                        values.stock_cover ===
                                                                        file
                                                                            ? "border-blue-400 ring-2 ring-blue-400/50"
                                                                            : userData?.dark_theme
                                                                            ? "border-white/20"
                                                                            : "border-neutral-200 bg-neutral-50/80 hover:border-neutral-300"
                                                                    }`}
                                                                    onClick={() => {
                                                                        setFieldValue(
                                                                            "stock_cover",
                                                                            file
                                                                        );
                                                                        setFieldValue(
                                                                            "coverUrl",
                                                                            stockCoverPreviewUrl(
                                                                                file
                                                                            )
                                                                        );
                                                                        setFieldValue(
                                                                            "cover_picture",
                                                                            ""
                                                                        );
                                                                        setFieldValue(
                                                                            "remove_cover_picture",
                                                                            false
                                                                        );
                                                                    }}
                                                                    aria-label={
                                                                        label
                                                                    }
                                                                >
                                                                    <img
                                                                        src={stockCoverPreviewUrl(
                                                                            file
                                                                        )}
                                                                        alt={
                                                                            label
                                                                        }
                                                                        className="w-full h-full object-cover"
                                                                    />
                                                                </button>
                                                            )
                                                        )}
                                                    </div>
                                                    {(legacyCoverDisplayUrl(
                                                        values
                                                    ) ||
                                                        values.stock_cover) && (
                                                        <div className="relative z-30 mb-3">
                                                            <button
                                                                type="button"
                                                                className={`text-sm underline pointer-events-auto ${
                                                                    userData?.dark_theme
                                                                        ? "text-blue-300"
                                                                        : "text-blue-600"
                                                                }`}
                                                                onClick={() => {
                                                                    setValues(
                                                                        (
                                                                            prev
                                                                        ) => ({
                                                                            ...prev,
                                                                            cover_picture:
                                                                                "",
                                                                            coverUrl:
                                                                                "",
                                                                            stock_cover:
                                                                                "",
                                                                            remove_cover_picture: true,
                                                                        })
                                                                    );
                                                                }}
                                                            >
                                                                Remove cover
                                                                image
                                                            </button>
                                                        </div>
                                                    )}
                                                    {legacyCoverDisplayUrl(
                                                        values
                                                    ) ? (
                                                        <div
                                                            className={`relative w-full overflow-hidden rounded-md ${
                                                                userData?.dark_theme
                                                                    ? "border border-white/10"
                                                                    : "border border-neutral-200 bg-neutral-50/30"
                                                            }`}
                                                        >
                                                            <img
                                                                className="relative z-0 block w-full h-auto min-h-40 object-cover pointer-events-none"
                                                                src={legacyCoverDisplayUrl(
                                                                    values
                                                                )}
                                                                alt=""
                                                            />
                                                            <label className="image-container absolute inset-0 z-[2] flex cursor-pointer flex-col items-center justify-center gap-1 bg-black/20 p-2 text-center hover:bg-black/30">
                                                                <div className="flex items-center justify-center">
                                                                    <p className="mr-2 text-sm text-white drop-shadow sm:mr-5">
                                                                        Drag &
                                                                        Drop
                                                                        Cover
                                                                        Picture
                                                                        Here Or
                                                                        click
                                                                        here
                                                                    </p>
                                                                    <CiCamera
                                                                        size={
                                                                            40
                                                                        }
                                                                        className="text-white drop-shadow"
                                                                    />
                                                                </div>
                                                                <FileUploader
                                                                    classes="opacity-0 !absolute !inset-0 !h-full !w-full"
                                                                    multiple={
                                                                        false
                                                                    }
                                                                    handleChange={(
                                                                        file
                                                                    ) =>
                                                                        handleChange(
                                                                            setFieldValue,
                                                                            file,
                                                                            "cover"
                                                                        )
                                                                    }
                                                                    name="cover_picture"
                                                                    types={
                                                                        fileTypes
                                                                    }
                                                                />
                                                            </label>
                                                        </div>
                                                    ) : (
                                                        <div
                                                            className={`relative h-[200px] w-full overflow-hidden rounded-md border-2 border-dashed ${
                                                                userData?.dark_theme
                                                                    ? "border-white/25 bg-white/[0.06]"
                                                                    : "border-neutral-300 bg-neutral-50"
                                                            }`}
                                                        >
                                                            <label className="image-container absolute inset-0 z-[2] flex cursor-pointer flex-col items-center justify-center px-4 text-center">
                                                                <div className="flex flex-wrap items-center justify-center gap-2 sm:gap-3">
                                                                    <p
                                                                        className={`text-sm font-medium ${
                                                                            userData?.dark_theme
                                                                                ? "text-neutral-200"
                                                                                : "text-neutral-600"
                                                                        }`}
                                                                    >
                                                                        Drag &
                                                                        Drop
                                                                        Cover
                                                                        Picture
                                                                        Here Or
                                                                        click
                                                                        here
                                                                    </p>
                                                                    <CiCamera
                                                                        size={
                                                                            40
                                                                        }
                                                                        className={
                                                                            userData?.dark_theme
                                                                                ? "text-neutral-300"
                                                                                : "text-neutral-500"
                                                                        }
                                                                    />
                                                                </div>
                                                                <FileUploader
                                                                    classes="opacity-0 !absolute !inset-0 !h-full !w-full"
                                                                    multiple={
                                                                        false
                                                                    }
                                                                    handleChange={(
                                                                        file
                                                                    ) =>
                                                                        handleChange(
                                                                            setFieldValue,
                                                                            file,
                                                                            "cover"
                                                                        )
                                                                    }
                                                                    name="cover_picture"
                                                                    types={
                                                                        fileTypes
                                                                    }
                                                                />
                                                            </label>
                                                        </div>
                                                    )}
                                                    <ErrorMessage
                                                        className="text-red-400 text-xs block mt-1"
                                                        name="coverUrl"
                                                        component="div"
                                                    />
                                                </div>
                                            </div>
                                        )}

                                        {/* Step 4: spotify */}
                                        {step === 4 && (
                                            <div>
                                                <div
                                                    className={
                                                        "mb-10 sm:text-lg"
                                                    }
                                                >
                                                    <p>
                                                        Would you like to add a
                                                        song or playlist to the
                                                        legacy page?
                                                    </p>
                                                    <p>
                                                        If yes, open Spotify,
                                                        copy the link of the
                                                        song or playlist, and
                                                        paste it here.
                                                    </p>
                                                    <p>
                                                        If not available at the
                                                        moment, you can move on
                                                        to the next step and add
                                                        it later.
                                                    </p>
                                                </div>
                                                <label
                                                    className={` ${
                                                        userData?.dark_theme
                                                            ? "text-white"
                                                            : "text-black"
                                                    }block mb-2 text-sm font-medium `}
                                                    htmlFor="spotify"
                                                >
                                                    Spotify Link
                                                </label>
                                                <Field
                                                    name="spotify"
                                                    onBlur={handleBlur}
                                                    placeholder="Spotify link here"
                                                    className={`${
                                                        userData?.dark_theme
                                                            ? "bg-[#333333] border-white/20 placeholder-gray-400 text-white"
                                                            : "bg-[#F1F1F1] border-white/20 placeholder text-black"
                                                    } border outline-none text-sm rounded-lg block w-full p-2.5 `}
                                                />
                                                <div className="text-red-400 text-xs">
                                                    {errors.spotify}
                                                </div>
                                            </div>
                                        )}

                                        {/* Step 5: Social Links */}
                                        {step === 6 && (
                                            <div className="space-y-4">
                                                <div className="mb-6 sm:text-lg">
                                                    <p>
                                                        Copy and paste{" "}
                                                        {values.spouseName ||
                                                            userData?.spouseName ||
                                                            "your loved one"}
                                                        ’s social media links
                                                        into the respective area
                                                        below.
                                                    </p>
                                                    <p>
                                                        If not available at the
                                                        moment, you can move on
                                                        to the next step and add
                                                        social media links
                                                        later.
                                                    </p>
                                                </div>
                                                <div>
                                                    <label
                                                        className={` ${
                                                            userData?.dark_theme
                                                                ? "text-white"
                                                                : "text-black"
                                                        }block mb-2 text-sm font-medium `}
                                                        htmlFor="spotify"
                                                    >
                                                        Facebook Link
                                                    </label>
                                                    <Field
                                                        name="spouse_facebook"
                                                        onBlur={handleBlur}
                                                        placeholder="Facebook link here"
                                                        className={`${
                                                            userData?.dark_theme
                                                                ? "bg-[#333333] border-white/20 placeholder-gray-400 text-white"
                                                                : "bg-[#F1F1F1] border-white/20 placeholder text-black"
                                                        } border outline-none text-sm rounded-lg block w-full p-2.5 `}
                                                    />
                                                    <div className="text-red-400 text-xs">
                                                        {errors.spouse_facebook}
                                                    </div>
                                                </div>
                                                <div>
                                                    <label
                                                        className={` ${
                                                            userData?.dark_theme
                                                                ? "text-white"
                                                                : "text-black"
                                                        }block mb-2 text-sm font-medium `}
                                                        htmlFor="spotify"
                                                    >
                                                        Instagram Link
                                                    </label>
                                                    <Field
                                                        name="spouse_instagram"
                                                        onBlur={handleBlur}
                                                        placeholder="Instagram link here"
                                                        className={`${
                                                            userData?.dark_theme
                                                                ? "bg-[#333333] border-white/20 placeholder-gray-400 text-white"
                                                                : "bg-[#F1F1F1] border-white/20 placeholder text-black"
                                                        } border outline-none text-sm rounded-lg block w-full p-2.5 `}
                                                    />
                                                    <div className="text-red-400 text-xs">
                                                        {
                                                            errors.spouse_instagram
                                                        }
                                                    </div>
                                                </div>
                                                <div>
                                                    <label
                                                        className={` ${
                                                            userData?.dark_theme
                                                                ? "text-white"
                                                                : "text-black"
                                                        }block mb-2 text-sm font-medium `}
                                                        htmlFor="spouse_twitter"
                                                    >
                                                        X Link
                                                    </label>
                                                    <Field
                                                        name="spouse_twitter"
                                                        onBlur={handleBlur}
                                                        placeholder="Twitter link here"
                                                        className={`${
                                                            userData?.dark_theme
                                                                ? "bg-[#333333] border-white/20 placeholder-gray-400 text-white"
                                                                : "bg-[#F1F1F1] border-white/20 placeholder text-black"
                                                        } border outline-none text-sm rounded-lg block w-full p-2.5 `}
                                                    />
                                                    <div className="text-red-400 text-xs">
                                                        {errors.Spouse_twitter}
                                                    </div>
                                                </div>
                                            </div>
                                        )}
                                        {step === 5 && (
                                            <div className="space-y-4">
                                                <div className="mb-6 sm:text-lg">
                                                    <p>
                                                        Copy and paste{" "}
                                                        {values.name ||
                                                            userData?.name ||
                                                            "your loved one"}
                                                        ’s social media links
                                                        into the respective area
                                                        below.
                                                    </p>
                                                    <p>
                                                        If not available at the
                                                        moment, you can move on
                                                        to the next step and add
                                                        social media links
                                                        later.
                                                    </p>
                                                </div>
                                                <div>
                                                    <label
                                                        className={` ${
                                                            userData?.dark_theme
                                                                ? "text-white"
                                                                : "text-black"
                                                        }block mb-2 text-sm font-medium `}
                                                        htmlFor="spotify"
                                                    >
                                                        Facebook Link
                                                    </label>
                                                    <Field
                                                        name="facebook"
                                                        onBlur={handleBlur}
                                                        placeholder="Facebook link here"
                                                        className={`${
                                                            userData?.dark_theme
                                                                ? "bg-[#333333] border-white/20 placeholder-gray-400 text-white"
                                                                : "bg-[#F1F1F1] border-white/20 placeholder text-black"
                                                        } border outline-none text-sm rounded-lg block w-full p-2.5 `}
                                                    />
                                                    <div className="text-red-400 text-xs">
                                                        {errors.facebook}
                                                    </div>
                                                </div>
                                                <div>
                                                    <label
                                                        className={` ${
                                                            userData?.dark_theme
                                                                ? "text-white"
                                                                : "text-black"
                                                        }block mb-2 text-sm font-medium `}
                                                        htmlFor="spotify"
                                                    >
                                                        Instagram Link
                                                    </label>
                                                    <Field
                                                        name="instagram"
                                                        onBlur={handleBlur}
                                                        placeholder="Instagram link here"
                                                        className={`${
                                                            userData?.dark_theme
                                                                ? "bg-[#333333] border-white/20 placeholder-gray-400 text-white"
                                                                : "bg-[#F1F1F1] border-white/20 placeholder text-black"
                                                        } border outline-none text-sm rounded-lg block w-full p-2.5 `}
                                                    />
                                                    <div className="text-red-400 text-xs">
                                                        {errors.instagram}
                                                    </div>
                                                </div>
                                                <div>
                                                    <label
                                                        className={` ${
                                                            userData?.dark_theme
                                                                ? "text-white"
                                                                : "text-black"
                                                        }block mb-2 text-sm font-medium `}
                                                        htmlFor="spotify"
                                                    >
                                                        X Link
                                                    </label>
                                                    <Field
                                                        name="twitter"
                                                        onBlur={handleBlur}
                                                        placeholder="Twitter link here"
                                                        className={`${
                                                            userData?.dark_theme
                                                                ? "bg-[#333333] border-white/20 placeholder-gray-400 text-white"
                                                                : "bg-[#F1F1F1] border-white/20 placeholder text-black"
                                                        } border outline-none text-sm rounded-lg block w-full p-2.5 `}
                                                    />
                                                    <div className="text-red-400 text-xs">
                                                        {errors.twitter}
                                                    </div>
                                                </div>
                                            </div>
                                        )}

                                        {/* Step 6: Map */}
                                        {step === 7 && (
                                            <div>
                                                <label
                                                    className={` ${
                                                        userData?.dark_theme
                                                            ? "text-white"
                                                            : "text-black"
                                                    }block mb-2 text-sm font-medium `}
                                                    htmlFor="location"
                                                >
                                                    Search for{" "}
                                                    {values.name ||
                                                        userData?.name ||
                                                        "your loved one"}
                                                    ’s final resting place by
                                                    location or name. For exact
                                                    resting place, drop the pin
                                                    at the plot of{" "}
                                                    {values.name ||
                                                        userData?.name ||
                                                        "your loved one"}
                                                    .
                                                    <br />
                                                    <br />
                                                    <span className="font-normal">
                                                        Note: If you’re unable
                                                        to find the cemetery by
                                                        name, search using the
                                                        physical address of the
                                                        cemetery.
                                                    </span>
                                                </label>
                                                <div className="relative space-y-3">
                                                    <MapComponent
                                                        key={`map-${String(
                                                            values.location?.lat
                                                        )}-${String(
                                                            values.location?.lng
                                                        )}`}
                                                        locationValue={
                                                            values.location
                                                        }
                                                        mapRef={mapRef}
                                                        mapVisible={mapVisible}
                                                        setMapVisible={
                                                            setMapVisible
                                                        }
                                                        error={errors.location}
                                                        handleBlur={handleBlur}
                                                        onUpdateLocation={(
                                                            location
                                                        ) => {
                                                            setFieldValue(
                                                                "location",
                                                                location
                                                            );
                                                        }}
                                                    />
                                                    {(hasProfileMapCoords(
                                                        values.location
                                                    ) ||
                                                        (userData?.latitude &&
                                                            userData?.longitude &&
                                                            !(
                                                                values.location
                                                                    ?.lat ===
                                                                    "" &&
                                                                values.location
                                                                    ?.lng === ""
                                                            ))) && (
                                                        <button
                                                            type="button"
                                                            className={`text-sm font-medium underline-offset-2 hover:underline ${
                                                                userData?.dark_theme
                                                                    ? "text-red-400 hover:text-red-300"
                                                                    : "text-red-600 hover:text-red-700"
                                                            }`}
                                                            onClick={() => {
                                                                setFieldValue(
                                                                    "location",
                                                                    {
                                                                        lat: "",
                                                                        lng: "",
                                                                    }
                                                                );
                                                                setMapVisible(
                                                                    true
                                                                );
                                                            }}
                                                        >
                                                            Remove map location
                                                        </button>
                                                    )}
                                                    {values.location?.lat ===
                                                        "" &&
                                                        values.location?.lng ===
                                                            "" &&
                                                        userData?.latitude &&
                                                        userData?.longitude && (
                                                            <p
                                                                className={`text-xs ${
                                                                    userData?.dark_theme
                                                                        ? "text-amber-400/90"
                                                                        : "text-amber-800"
                                                                }`}
                                                            >
                                                                Save your
                                                                changes to
                                                                remove the map
                                                                from this legacy
                                                                page.
                                                            </p>
                                                        )}
                                                </div>
                                            </div>
                                        )}
                                    </div>

                                    {step === 1 && (
                                        <div>
                                            <p
                                                className={`${
                                                    userData?.dark_theme
                                                        ? " text-white/70 "
                                                        : "text-black"
                                                }text-center md:text-end md:pr-6`}
                                            >
                                                For bio and other info, click
                                                Next.
                                            </p>
                                        </div>
                                    )}

                                    {/* Navigation Buttons */}
                                    <div
                                        className={
                                            "flex flex-wrap sm:flex-nowrap items-center pb-4 pt-2 px-6 mt-4 justify-between "
                                        }
                                    >
                                        {/* Previous Button */}
                                        <span
                                            onClick={() => setStep(step - 1)}
                                            className={
                                                `${
                                                    userData?.dark_theme
                                                        ? " text-white/70"
                                                        : "text-black/70"
                                                }
                      flex select-none cursor-pointer items-center justify-center gap-2  active:scale-90 transition-all duration-200 text-sm sm:text-lg font-medium rounded-lg sm:ml-5 text-center ` +
                                                (step > 1
                                                    ? "visible"
                                                    : "invisible")
                                            }
                                        >
                                            <GrLinkPrevious className="mt-[2px]" />{" "}
                                            Previous
                                        </span>

                                        {step < 7 && (
                                            <button
                                                onClick={() =>
                                                    handleSubmitSteps(values, {
                                                        setSubmitting,
                                                    })
                                                }
                                                type="button"
                                                disabled={primaryActionDisabled}
                                                className="disabled:pointer-events-none hidden disabled:opacity-40 select-none mx-auto md:flex items-center justify-center gap-2 w-full  max-w-60 text-white bg-blue-400 active:scale-90 transition-all duration-200 text-sm sm:text-lg font-medium rounded-lg px-5 py-2 text-center"
                                            >
                                                {isSubmitting ? (
                                                    <svg
                                                        aria-hidden="true"
                                                        className="inline w-4 h-4 text-gray-200 animate-spin dark:text-gray-600 fill-blue-600"
                                                        viewBox="0 0 100 101"
                                                        fill="none"
                                                        xmlns="http://www.w3.org/2000/svg"
                                                    >
                                                        <path
                                                            d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                                                            fill="currentColor"
                                                        />
                                                        <path
                                                            d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                                                            fill="currentFill"
                                                        />
                                                    </svg>
                                                ) : (
                                                    <MdDone
                                                        className={
                                                            "text-base sm:text-xl"
                                                        }
                                                    />
                                                )}
                                                Update Legacy Page
                                            </button>
                                        )}

                                        {/* Next or Skip Button */}
                                        {step < 7 ? (
                                            <button
                                                type="button"
                                                disabled={nextStepDisabled}
                                                onClick={() => {
                                                    console.log(
                                                        isSpouse,
                                                        step,
                                                        "***************",
                                                        step == 5 && !isSpouse
                                                    );
                                                    if (
                                                        step == 5 &&
                                                        !isSpouse
                                                    ) {
                                                        setStep(step + 2);
                                                        return;
                                                    }
                                                    setStep(step + 1);
                                                }}
                                                className={`${
                                                    userData?.dark_theme
                                                        ? " text-white/70"
                                                        : "text-black/70"
                                                } flex flex-row select-none cursor-pointer items-center disabled:opacity-30 disabled:pointer-events-none justify-center gap-2 active:scale-90 transition-all duration-200 text-sm sm:text-lg font-medium rounded-lg sm:mr-5 text-center`}
                                            >
                                                Next{" "}
                                                <GrLinkPrevious className="mt-[2px] rotate-180" />
                                            </button>
                                        ) : (
                                            <button
                                                onClick={step > 5 && submitForm}
                                                type="button"
                                                disabled={
                                                    !isValid ||
                                                    isSubmitting ||
                                                    step < 6
                                                }
                                                className="disabled:pointer-events-none mt-4 sm:mt-0 select-none flex items-center justify-center gap-2 disabled:opacity-50  sm:min-w-40 text-white bg-blue-400 active:scale-90 transition-all duration-200 text-sm sm:text-lg font-medium rounded-lg px-5 py-2 text-center"
                                            >
                                                {isSubmitting ? (
                                                    <svg
                                                        aria-hidden="true"
                                                        className="inline w-4 h-4 text-gray-200 animate-spin dark:text-gray-600 fill-blue-600"
                                                        viewBox="0 0 100 101"
                                                        fill="none"
                                                        xmlns="http://www.w3.org/2000/svg"
                                                    >
                                                        <path
                                                            d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                                                            fill="currentColor"
                                                        />
                                                        <path
                                                            d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                                                            fill="currentFill"
                                                        />
                                                    </svg>
                                                ) : (
                                                    <MdDone
                                                        className={
                                                            "text-base sm:text-xl"
                                                        }
                                                    />
                                                )}
                                                Update Legacy Page
                                            </button>
                                        )}
                                    </div>
                                    <div className="pb-6">
                                        {step < 7 && (
                                            <button
                                                onClick={() =>
                                                    handleSubmitSteps(values, {
                                                        setSubmitting,
                                                    })
                                                }
                                                type="button"
                                                disabled={primaryActionDisabled}
                                                className="disabled:pointer-events-none md:hidden disabled:opacity-40 select-none mx-auto flex items-center justify-center gap-2 w-full  max-w-60 text-white bg-blue-400 active:scale-90 transition-all duration-200 text-sm sm:text-lg font-medium rounded-lg px-5 py-2 text-center"
                                            >
                                                {isSubmitting ? (
                                                    <svg
                                                        aria-hidden="true"
                                                        className="inline w-4 h-4 text-gray-200 animate-spin dark:text-gray-600 fill-blue-600"
                                                        viewBox="0 0 100 101"
                                                        fill="none"
                                                        xmlns="http://www.w3.org/2000/svg"
                                                    >
                                                        <path
                                                            d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                                                            fill="currentColor"
                                                        />
                                                        <path
                                                            d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                                                            fill="currentFill"
                                                        />
                                                    </svg>
                                                ) : (
                                                    <MdDone
                                                        className={
                                                            "text-base sm:text-xl"
                                                        }
                                                    />
                                                )}
                                                Update Legacy Page
                                            </button>
                                        )}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <ImageCropModal
                            imageURL={legacyProfileDisplayUrl(values)}
                            isAvatar={true}
                            name={values.profile_picture?.name}
                            open={openProfileCropModal}
                            setOpen={setOpenProfileCropModal}
                            ratio={"dp"}
                            setCroppedImage={(file) => {
                                setFieldValue("profile_picture", file);
                                setFieldValue(
                                    "profileUrl",
                                    URL.createObjectURL(file)
                                );
                                setFieldValue("remove_profile_picture", false);
                            }}
                        />

                        {/* Cover Crop */}

                        <ImageCropModal
                            imageURL={legacyCoverDisplayUrl(values)}
                            name={values.cover_picture?.name}
                            open={openCoverCropModal}
                            setOpen={setOpenCoverCropModal}
                            ratio={"cover"}
                            setCroppedImage={(file) => {
                                setFieldValue("cover_picture", file);
                                setFieldValue(
                                    "coverUrl",
                                    URL.createObjectURL(file)
                                );
                                setFieldValue("stock_cover", "");
                                setFieldValue("remove_cover_picture", false);
                            }}
                        />
                    </Form>
                );
            }}
        </Formik>
    );
};
