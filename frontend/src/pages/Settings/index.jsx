import { useEffect, useRef, useState } from "react";
import { toast } from "react-toastify";
import { format } from "date-fns";
import { MdDone } from "react-icons/md";
import { useNavigate, useParams } from "react-router-dom";
import { ErrorMessage, Field, Form, Formik } from "formik";
import { TbPencil } from "react-icons/tb";
import { CiCamera } from "react-icons/ci";
import { v4 as uuidv4 } from "uuid";
import * as Yup from "yup";
import { FileUploader } from "react-drag-drop-files";
import {
    Accordian,
    DeathDateChoice,
    FormikDatePicker,
    ImageCropModal,
    MapComponent,
} from "../../components";
import { useFetchTributeData } from "../../services";
import {
    appendLegacyMediaFields,
    formatDateStrToDate,
    hasProfileMapCoords,
    hasRecordedDeathDate,
    objectToFormData,
    profileCoordinatesForPayload,
    regex,
} from "../../utils";
import {
    initialStockCoverFromUrl,
    STOCK_COVERS,
    stockCoverPreviewUrl,
} from "../../constants/stockCovers";
import {
    mergeTabVisibility,
    TAB_VISIBILITY_ITEMS,
} from "../../constants/tabVisibility";
import { updateTabVisibilityAsync } from "../../services/api";
import { API_BASE_URL } from "../../config";
import { getAuthToken, getUserData, useAppSelector } from "../../redux";

function nonEmptyStr(x) {
    return typeof x === "string" && x.trim() !== "";
}

const validationSchema = Yup.object()
    .shape({
        name: Yup.string()
            .min(2, "Name should be at least 2 characters long")
            .required("Name is required"),
        dob: Yup.date("Select valid date of birth").required(
            "Date of birth is required"
        ),
        dod: Yup.date("Select valid date of death").nullable(),
        bio: Yup.string().required("Bio is required"),
        cover_picture: Yup.mixed().nullable(),
        coverUrl: Yup.string().nullable(),
        profileUrl: Yup.string().nullable(),
        profile_picture: Yup.mixed().nullable(),
        stock_cover: Yup.string().nullable(),
        remove_profile_picture: Yup.mixed().nullable(),
        remove_cover_picture: Yup.mixed().nullable(),
        spotify: Yup.string()
            .nullable()
            .test("spotify-or-empty", "Invalid Spotify URL", (value) => {
                if (value == null || String(value).trim() === "") {
                    return true;
                }
                return regex.spotify.test(String(value));
            }),
        facebook: Yup.string()
            .nullable()
            .test("facebook-or-empty", "Invalid Facebook URL", (value) => {
                if (value == null || String(value).trim() === "") {
                    return true;
                }
                return regex.facebook.test(String(value));
            }),
        instagram: Yup.string()
            .nullable()
            .test("instagram-or-empty", "Invalid Instagram URL", (value) => {
                if (value == null || String(value).trim() === "") {
                    return true;
                }
                return regex.instagram.test(String(value));
            }),
        twitter: Yup.string()
            .nullable()
            .test("twitter-or-empty", "Invalid Twitter URL", (value) => {
                if (value == null || String(value).trim() === "") {
                    return true;
                }
                return regex.twitter.test(String(value));
            }),
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
export const Settings = () => {
    const [isPresent, setIsPresent] = useState(true);
    const [isPresentSpouse, setIsPresentSpouse] = useState(true);
    const [isSpouse, setIsSpouse] = useState(false);
    const appToken = useAppSelector(getAuthToken);
    const userData = useAppSelector(getUserData);
    const mapRef = useRef(null);
    const { id } = useParams();
    const navigate = useNavigate();

    const [mapVisible, setMapVisible] = useState(false);
    const [openProfileCropModal, setOpenProfileCropModal] = useState(false);
    const [openCoverCropModal, setOpenCoverCropModal] = useState(false);
    const [tabVisibility, setTabVisibility] = useState(() =>
        mergeTabVisibility(userData?.tab_visibility)
    );
    const [savingTabVisibility, setSavingTabVisibility] = useState(false);

    const { refetch } = useFetchTributeData(id);

    useEffect(() => {
        setTabVisibility(mergeTabVisibility(userData?.tab_visibility));
    }, [userData?.tab_visibility]);

    const handleSaveTabVisibility = async () => {
        setSavingTabVisibility(true);
        try {
            const res = await updateTabVisibilityAsync(id, tabVisibility);
            if (res.data?.status === 200) {
                toast.success("Tab visibility saved");
                refetch();
            } else {
                toast.error("Could not save tab settings");
            }
        } catch {
            toast.error("Could not save tab settings");
        } finally {
            setSavingTabVisibility(false);
        }
    };

    const handleSpouseToggle = (setFieldValue) => {
        setIsSpouse(!isSpouse);
        if (!isSpouse) {
            setFieldValue("spouseName", ""); // Clear the Date of Death if Present is selected
            setFieldValue("spouseDob", null);
            setFieldValue("spouseDod", null);
        }
    };
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
        spouse_facebook: userData?.spouse_facebook || "",
        spouse_instagram: userData?.spouse_instagram || "",
        spouse_twitter: userData?.spouse_twitter || "",

        spouse_badge: userData?.spouse_badge || "",
        badge: userData?.badge,

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

        setIsPresent(!hasRecordedDeathDate(userData?.dod));

        const spouseDodRaw = userData?.relations?.find(
            (value) => value.name.toLowerCase() === "spouse"
        )?.dod;
        setIsPresentSpouse(!hasRecordedDeathDate(spouseDodRaw));

        if (spouse?.person_name && spouse?.is_legacy) {
            setIsSpouse(true);
        }
        if (userData?.latitude) {
            setMapVisible(true);
        } else {
            setMapVisible(false);
        }
    }, [userData]);

    useEffect(() => {
        if (!appToken) {
            navigate("/" + id);
        }
    }, [appToken]);

    return (
        <Formik
            validateOnMount
            enableReinitialize
            validateOnChange
            validateOnBlur
            initialValues={initialValues}
            onSubmit={handleSubmit}
            validationSchema={validationSchema}
        >
            {({
                values,
                setFieldValue,
                setValues,
                isValid,
                isSubmitting,
                handleBlur,
                errors,
            }) => {
                return (
                    <Form>
                        <div className="space-y-10 mt-4 w-full">
                            <div className="flex items-center justify-content-between w-full">
                                <p className="text-2xl sm:text-3xl flex-grow">
                                    Settings
                                </p>
                                <button
                                    type="submit"
                                    disabled={!isValid || isSubmitting}
                                    className="disabled:pointer-events-none select-none disabled:bg-neutral-700 disabled:text-neutral-400 flex items-center justify-center gap-2 disabled:opacity-50 sm:min-w-40 text-white bg-blue-400 active:scale-90 transition-all duration-200 text-sm sm:text-lg font-medium rounded-lg px-3 sm:px-5 py-2 text-center"
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
                                            className={"text-base sm:text-xl"}
                                        />
                                    )}
                                    Update Legacy Page
                                </button>
                            </div>

                            <div className="space-y-10">
                                <Accordian title={"Tab visibility"}>
                                    <p
                                        className={`text-sm mb-4 ${
                                            userData?.dark_theme
                                                ? "text-white/80"
                                                : "text-neutral-600"
                                        }`}
                                    >
                                        Choose which sections appear in the top
                                        navigation for this tribute.{" "}
                                        <strong>Legacy</strong> is always
                                        available.
                                    </p>
                                    <div className="space-y-4 max-w-md">
                                        {TAB_VISIBILITY_ITEMS.map(
                                            ({ key, label }) => (
                                                <div
                                                    key={key}
                                                    className="flex items-center justify-between gap-4"
                                                >
                                                    <span
                                                        className={`text-sm font-medium ${
                                                            userData?.dark_theme
                                                                ? "text-white"
                                                                : "text-neutral-800"
                                                        }`}
                                                    >
                                                        {label}
                                                    </span>
                                                    <label className="relative inline-flex cursor-pointer items-center">
                                                        <input
                                                            type="checkbox"
                                                            className="peer sr-only"
                                                            checked={
                                                                tabVisibility[
                                                                    key
                                                                ] !== false
                                                            }
                                                            onChange={(e) =>
                                                                setTabVisibility(
                                                                    (prev) => ({
                                                                        ...prev,
                                                                        [key]: e
                                                                            .target
                                                                            .checked,
                                                                    })
                                                                )
                                                            }
                                                        />
                                                        <span
                                                            className={`relative h-6 w-11 rounded-full transition-colors after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition-transform after:content-[''] peer-checked:bg-blue-400 peer-checked:after:translate-x-5 ${
                                                                userData?.dark_theme
                                                                    ? "bg-neutral-600"
                                                                    : "bg-neutral-300"
                                                            }`}
                                                        />
                                                    </label>
                                                </div>
                                            )
                                        )}
                                    </div>
                                    <button
                                        type="button"
                                        onClick={handleSaveTabVisibility}
                                        disabled={savingTabVisibility}
                                        className={`mt-6 text-sm font-medium rounded-lg px-4 py-2 transition ${
                                            userData?.dark_theme
                                                ? "bg-blue-500 text-white hover:bg-blue-600 disabled:opacity-50"
                                                : "bg-blue-400 text-white hover:bg-blue-500 disabled:opacity-50"
                                        }`}
                                    >
                                        {savingTabVisibility
                                            ? "Saving…"
                                            : "Save tab settings"}
                                    </button>
                                </Accordian>
                                {/* Step 1: Basic Info */}
                                <Accordian
                                    title={"Add Legacy information below"}
                                >
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
                                                honor badge, if applicable
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
                                                    --Please choose an option--
                                                </option>
                                                {services.map((service) => (
                                                    <option
                                                        key={service.value}
                                                        value={service.value}
                                                    >
                                                        {service.name}
                                                    </option>
                                                ))}
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
                                                Add loved one’s Date of Birth
                                            </label>

                                            <Field
                                                name={"dob"}
                                                id={"dob"}
                                                component={FormikDatePicker}
                                            />

                                            {errors.dob && (
                                                <div className="text-red-400 text-xs">
                                                    {errors.dob}
                                                </div>
                                            )}
                                        </div>
                                        <label
                                            className={` ${
                                                userData?.dark_theme
                                                    ? "text-white"
                                                    : "text-black"
                                            }block mb-2 text-sm font-medium `}
                                            htmlFor="dod"
                                        >
                                            Add loved one’s date of death
                                        </label>
                                        <p
                                            className={`text-sm mb-3 ${
                                                userData?.dark_theme
                                                    ? "text-white/70"
                                                    : "text-gray-600"
                                            }`}
                                        >
                                            Choose <strong>Still living</strong>{" "}
                                            if there is no date of death, or{" "}
                                            <strong>Has passed</strong> to enter
                                            the date.
                                        </p>
                                        <DeathDateChoice
                                            idPrefix="settings-principal-dod"
                                            isLiving={isPresent}
                                            darkTheme={userData?.dark_theme}
                                            fieldName="dod"
                                            livingHelp="No date of death to enter for this loved one."
                                            deceasedHelp="Enter their full date of death below."
                                            pickerClassName="border outline-none text-sm rounded-lg block w-full p-2.5 bg-gray-700 border-gray-600 placeholder-gray-400 text-white focus:ring-blue-500 focus:border-blue-500"
                                            onLiving={() => {
                                                setIsPresent(true);
                                                setFieldValue("dod", null);
                                            }}
                                            onDeceased={() =>
                                                setIsPresent(false)
                                            }
                                        />
                                        <ErrorMessage
                                            name="dod"
                                            component="div"
                                            className="text-red-500 text-sm mt-2"
                                        />{" "}
                                        <div className="space-y-4">
                                            <div>
                                                <label
                                                    className={` ${
                                                        userData?.dark_theme
                                                            ? "text-white"
                                                            : "text-black"
                                                    }block mb-2 text-sm font-medium `}
                                                >
                                                    <input
                                                        type="checkbox"
                                                        checked={isSpouse}
                                                        onChange={() =>
                                                            handleSpouseToggle(
                                                                setFieldValue
                                                            )
                                                        }
                                                        className="w-4 h-4 mr-3 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
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
                                                                Add Spouse’s
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
                                                                Add spouse’s
                                                                honor badge, if
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
                                                                    choose an
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
                                                                Add Spouse’s
                                                                Date of Birth
                                                            </label>
                                                            <Field
                                                                name={
                                                                    "spouseDob"
                                                                }
                                                                id={"bio-dob"}
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
                                                                Date of Death
                                                            </label>
                                                            <p
                                                                className={`text-sm mb-3 ${
                                                                    userData?.dark_theme
                                                                        ? "text-white/70"
                                                                        : "text-gray-600"
                                                                }`}
                                                            >
                                                                For your spouse:{" "}
                                                                <strong>
                                                                    Still living
                                                                </strong>{" "}
                                                                or{" "}
                                                                <strong>
                                                                    Has passed
                                                                </strong>{" "}
                                                                with a date.
                                                            </p>
                                                            <DeathDateChoice
                                                                idPrefix="settings-spouse-dod"
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
                                </Accordian>
                                {/* Step 2: Add bio */}
                                <Accordian title={"Add Legacy Story"}>
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
                                                "loved one") + "’s"}{" "}
                                            story or copy/paste their obituary
                                            here.
                                        </label>
                                        <textarea
                                            rows={4}
                                            maxLength={4000}
                                            className={`${
                                                userData?.dark_theme
                                                    ? "bg-[#333333] border-white/20 placeholder-gray-400 text-white"
                                                    : "bg-[#F1F1F1] border-white/20 placeholder text-black"
                                            } border outline-none text-sm rounded-lg block w-full p-2.5 `}
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
                                </Accordian>
                                {userData?.relations?.find(
                                    (value) =>
                                        value.name.toLowerCase() === "spouse" &&
                                        value.is_legacy
                                )?.person_name ||
                                (values?.spouseName && values.is_legacy) ? (
                                    <Accordian
                                        title={"Add Legacy Story of Spouse"}
                                    >
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
                                                        (value) =>
                                                            value.name.toLowerCase() ===
                                                            "spouse"
                                                    )?.person_name || "Spouse"
                                                }’s story or copy/paste their obituary here.`}
                                            </label>
                                            <textarea
                                                rows={4}
                                                maxLength={4000}
                                                className={`${
                                                    userData?.dark_theme
                                                        ? "bg-[#333333] border-white/20 placeholder-gray-400 text-white"
                                                        : "bg-[#F1F1F1] border-white/20 placeholder text-black"
                                                } border outline-none text-sm rounded-lg block w-full p-2.5 `}
                                                placeholder="write spouse bio here"
                                                name="spouseBio"
                                                onChange={(e) =>
                                                    setFieldValue(
                                                        "spouseBio",
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
                                    </Accordian>
                                ) : (
                                    ""
                                )}
                                {/* Step 3: Profile and cover */}
                                <Accordian
                                    title={
                                        "Add or update your loved one’s profile and cover photo"
                                    }
                                >
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
                                                    src={
                                                        values.profileUrl || ""
                                                    }
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
                                                        handleChange={(file) =>
                                                            handleChange(
                                                                setFieldValue,
                                                                file,
                                                                "profile"
                                                            )
                                                        }
                                                        name="profile_picture"
                                                        types={fileTypes}
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
                                                    setValues({
                                                        ...values,
                                                        profile_picture: "",
                                                        profileUrl: "",
                                                        remove_profile_picture: true,
                                                    });
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
                                                    ({ file, label }) => (
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
                                                            aria-label={label}
                                                        >
                                                            <img
                                                                src={stockCoverPreviewUrl(
                                                                    file
                                                                )}
                                                                alt={label}
                                                                className="w-full h-full object-cover"
                                                            />
                                                        </button>
                                                    )
                                                )}
                                            </div>
                                            {(values.coverUrl ||
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
                                                            setValues({
                                                                ...values,
                                                                cover_picture:
                                                                    "",
                                                                coverUrl: "",
                                                                stock_cover: "",
                                                                remove_cover_picture: true,
                                                            });
                                                        }}
                                                    >
                                                        Remove cover image
                                                    </button>
                                                </div>
                                            )}
                                            {values?.coverUrl ? (
                                                <div
                                                    className={`relative w-full overflow-hidden rounded-md ${
                                                        userData?.dark_theme
                                                            ? "border border-white/10"
                                                            : "border border-neutral-200 bg-neutral-50/30"
                                                    }`}
                                                >
                                                    <img
                                                        className="relative z-0 block w-full h-auto min-h-40 object-cover pointer-events-none"
                                                        src={values.coverUrl}
                                                        alt=""
                                                    />
                                                    <label className="image-container absolute inset-0 z-[2] flex cursor-pointer flex-col items-center justify-center gap-1 bg-black/20 p-2 text-center hover:bg-black/30">
                                                        <div className="flex items-center justify-center">
                                                            <p className="mr-2 text-sm text-white drop-shadow sm:mr-5">
                                                                Drag & Drop
                                                                Cover Picture
                                                                Here Or click
                                                                here
                                                            </p>
                                                            <CiCamera
                                                                size={40}
                                                                className="text-white drop-shadow"
                                                            />
                                                        </div>
                                                        <FileUploader
                                                            classes="opacity-0 !absolute !inset-0 !h-full !w-full"
                                                            multiple={false}
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
                                                            types={fileTypes}
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
                                                                Drag & Drop
                                                                Cover Picture
                                                                Here Or click
                                                                here
                                                            </p>
                                                            <CiCamera
                                                                size={40}
                                                                className={
                                                                    userData?.dark_theme
                                                                        ? "text-neutral-300"
                                                                        : "text-neutral-500"
                                                                }
                                                            />
                                                        </div>
                                                        <FileUploader
                                                            classes="opacity-0 !absolute !inset-0 !h-full !w-full"
                                                            multiple={false}
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
                                                            types={fileTypes}
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
                                </Accordian>
                                {/* Step 4: spotify */}
                                <Accordian title={"Add Spotify Playlist"}>
                                    <div>
                                        <div className={"mb-10 sm:text-lg"}>
                                            <p>
                                                Would you like to add a song or
                                                playlist to the legacy page?
                                            </p>
                                            <p>
                                                If yes, open Spotify, copy the
                                                link of the song or playlist,
                                                and paste it here.
                                            </p>
                                            <p>
                                                If not available at the moment,
                                                you can move on to the next step
                                                and add it later.
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
                                </Accordian>
                                {/* Step 5: Social Links */}
                                <Accordian
                                    title={`Add ${
                                        values.name ||
                                        userData?.name ||
                                        "your loved one"
                                    }’s social media links`}
                                >
                                    <div className="space-y-4">
                                        <div className="mb-6 sm:text-lg">
                                            <p>
                                                Copy and paste
                                                {` ${
                                                    values.name ||
                                                    userData?.name ||
                                                    "your loved one"
                                                }`}
                                                ’s social media links into the
                                                respective area below
                                            </p>
                                            <p>
                                                If not available at the moment,
                                                you can move on to the next step
                                                and add social media links
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
                                                htmlFor="facebook"
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
                                                htmlFor="instagram"
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
                                                htmlFor="twitter"
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
                                </Accordian>

                                {userData?.relations?.find(
                                    (value) =>
                                        value.name.toLowerCase() === "spouse" &&
                                        value.is_legacy
                                )?.person_name ||
                                (values?.spouseName && values.is_legacy) ? (
                                    <Accordian
                                        title={`Add ${
                                            values?.spouseName
                                                ? values?.spouseName
                                                : "spouse"
                                        } social media links`}
                                    >
                                        <div className="space-y-4">
                                            <div className="mb-6 sm:text-lg">
                                                <p>
                                                    Copy and paste
                                                    {` ${
                                                        values?.spouseName
                                                            ? values?.spouseName
                                                            : "spouse"
                                                    } `}
                                                    social media links into the
                                                    respective area below.
                                                </p>
                                                <p>
                                                    If not available at the
                                                    moment, you can move on to
                                                    the next step and add social
                                                    media links later.
                                                </p>
                                            </div>
                                            <div>
                                                <label
                                                    className={` ${
                                                        userData?.dark_theme
                                                            ? "text-white"
                                                            : "text-black"
                                                    }block mb-2 text-sm font-medium `}
                                                    htmlFor="facebook"
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
                                                    htmlFor="instagram"
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
                                                    {errors.spouse_instagram}
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
                                    </Accordian>
                                ) : (
                                    ""
                                )}

                                {/* Step 6: Map */}
                                <Accordian title={"Add Final Resting Place"}>
                                    <div>
                                        <label
                                            className={` ${
                                                userData?.dark_theme
                                                    ? "text-white"
                                                    : "text-black"
                                            }block mb-2 text-sm font-medium `}
                                            htmlFor="location"
                                        >
                                            Search for your loved one’s final
                                            resting place by location or name.
                                            For exact resting place, drop the
                                            pin at the plot of your loved one.
                                            <br />
                                            <br />
                                            <span className="font-normal">
                                                Note: If you’re unable to find
                                                the cemetery by name, search
                                                using the physical address of
                                                the cemetery.
                                            </span>
                                        </label>
                                        <div className="relative space-y-3">
                                            <MapComponent
                                                key={`map-${String(
                                                    values.location?.lat
                                                )}-${String(
                                                    values.location?.lng
                                                )}`}
                                                locationValue={values.location}
                                                mapRef={mapRef}
                                                mapVisible={mapVisible}
                                                setMapVisible={setMapVisible}
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
                                                        values.location?.lat ===
                                                            "" &&
                                                        values.location?.lng ===
                                                            ""
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
                                                            { lat: "", lng: "" }
                                                        );
                                                        setMapVisible(true);
                                                    }}
                                                >
                                                    Remove map location
                                                </button>
                                            )}
                                            {values.location?.lat === "" &&
                                                values.location?.lng === "" &&
                                                userData?.latitude &&
                                                userData?.longitude && (
                                                    <p
                                                        className={`text-xs ${
                                                            userData?.dark_theme
                                                                ? "text-amber-400/90"
                                                                : "text-amber-800"
                                                        }`}
                                                    >
                                                        Save your changes to
                                                        remove the map from this
                                                        legacy page.
                                                    </p>
                                                )}
                                        </div>
                                    </div>
                                </Accordian>
                            </div>
                            <button
                                type="submit"
                                disabled={!isValid || isSubmitting}
                                className="disabled:pointer-events-none select-none w-full disabled:bg-neutral-700 disabled:text-neutral-400 flex items-center justify-center gap-2 disabled:opacity-50 sm:min-w-40 text-white bg-blue-400 active:scale-90 transition-all duration-200 text-sm sm:text-lg font-medium rounded-lg px-5 py-2 text-center"
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
                                        className={"text-base sm:text-xl"}
                                    />
                                )}
                                Update Legacy Page
                            </button>
                        </div>

                        {/* Modals */}

                        <ImageCropModal
                            imageURL={values.profileUrl}
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
                            imageURL={values.coverUrl}
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
