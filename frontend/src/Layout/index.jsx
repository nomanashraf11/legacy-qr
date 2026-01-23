import { useEffect, useState } from "react";
import {
    AddTimelineModal,
    EditBioModal,
    EmptyModal,
    FamilyTreeModal,
    Footer,
    InstructionsModal,
    LoginModal,
    Navigation,
    PostModal,
    RegisterModal,
    ResetPasswordModal,
    ShareModal,
    UploadPhotosModal,
    Wrapper,
} from "../components";
import { Outlet, useLocation, useParams } from "react-router-dom";
import { HiOutlinePencilSquare } from "react-icons/hi2";
import {
    getAuthToken,
    setID,
    setUserData,
    useAppDispatch,
    useAppSelector,
    changeOrbit,
    getOrbitStatus,
} from "../redux";
import { useFetchQrCodesData, useFetchTributeData } from "../services";
import { IoMdShare } from "react-icons/io";
import { Helmet } from "react-helmet";
import { FaEye, FaUserEdit } from "react-icons/fa";
import { toast } from "react-toastify";
import { RiFacebookLine, RiTwitterXFill } from "react-icons/ri";
import { RxInstagramLogo } from "react-icons/rx";
import { ChangePasswordModal } from "../components/ChangePasswordModal";
import { API_BASE_URL, BASE_URL, LIVE_URL } from "../config";
import { useTour } from "@reactour/tour";
import { dateMMDDYYYYFormat, objectToFormData, textWillWrap } from "../utils";
import { Spotify } from "react-spotify-embed";
import { IoClose } from "react-icons/io5";
import { GrView } from "react-icons/gr";

export const AppLayout = () => {
    const token = useAppSelector(getAuthToken);
    const spouseOrbt = useAppSelector(getOrbitStatus);

    const dispatch = useAppDispatch();
    const { pathname } = useLocation();
    const { id } = useParams();
    const appToken = useAppSelector(getAuthToken);
    const { setIsOpen, setSteps, setCurrentStep, currentStep } = useTour();

    const [step, setStep] = useState(1);
    const [isSpotifyVisible, setSpotifyVisible] = useState(true);

    const { data, isLoading, refetch } = useFetchTributeData(id);
    const {
        data: qrCodes,
        refetch: refetchQrCodes,
        isLoading: loadingCodes,
    } = useFetchQrCodesData();
    const location = useLocation();
    const currentSection = location.pathname.split("/").pop();

    const [openPostModal, setOpenPostModal] = useState(false);
    const [openEditBioModal, setOpenEditBioModal] = useState(false);
    const [openUploadPhotosModal, setOpenUploadPhotosModal] = useState(false);
    const [openTimlineModal, setOpenTimlineModal] = useState(false);
    const [openRegisterModal, setOpenRegisterModal] = useState(false);
    const [openLoginModal, setOpenLoginModal] = useState(false);
    const [openResetPasswordModal, setOpenResetPasswordModal] = useState(false);
    const [openEmptyModal, setOpenEmptyModal] = useState(false);
    const [openShareModal, setOpenShareModal] = useState(false);
    const [openChangePasswordModal, setOpenChangePasswordModal] =
        useState(false);
    const [openFamilyTreeModal, setOpenFamilyTreeModal] = useState(false);
    const [openSuccessModal, setOpenSuccessModal] = useState(false);
    const [dark_theme, setDarkTheme] = useState(
        data?.Details?.dark_theme || false
    );

    const isAdmin = () => {
        const ids = qrCodes?.data?.map((item) => item.uuid);
        return ids?.includes(id);
    };

    const handleClickEdit = () => {
        if (pathname.includes("gallery")) {
            setOpenUploadPhotosModal(true);
        } else if (pathname.includes("timeline")) {
            setOpenTimlineModal(true);
        } else if (pathname.includes("legacy")) {
            handleOpenEditBioModal(true);
        } else if (pathname.includes("family")) {
            setOpenFamilyTreeModal(true);
        }
    };

    const getYear = (date) => {
        return new Date(date).getFullYear();
    };

    const handleOpenEditBioModal = () => {
        setOpenEditBioModal(true);
    };

    useEffect(() => {
        dispatch(setUserData(data?.Details));
        setDarkTheme(data?.Details?.dark_theme);
        if (data?.Details?.dark_theme !== false) {
            document.body.classList.add("dark-theme");
            document.body.classList.remove("light-theme");
        } else {
            document.body.classList.add("light-theme");
            document.body.classList.remove("dark-theme");
        }
    }, [data]);
    const isAnyModalOpen =
        openPostModal ||
        openEditBioModal ||
        openUploadPhotosModal ||
        openTimlineModal ||
        openRegisterModal ||
        openLoginModal ||
        openResetPasswordModal ||
        openEmptyModal ||
        openShareModal ||
        openChangePasswordModal ||
        openFamilyTreeModal ||
        openSuccessModal;

    // Disable body scroll when any modal is open
    useEffect(() => {
        if (isAnyModalOpen) {
            document.body.style.overflow = "hidden"; // Disable body scroll
        } else {
            document.body.style.overflow = "auto"; // Enable body scroll
        }

        // Cleanup function to reset the body scroll when component unmounts
        return () => {
            document.body.style.overflow = "auto";
        };
    }, [isAnyModalOpen]);

    useEffect(() => {
        if (data) {
            if (
                token &&
                !isLoading &&
                isAdmin() &&
                currentStep === 0 &&
                pathname.includes("legacy") &&
                !data?.Details?.name &&
                data?.status === 200
            ) {
                setIsOpen(true);
            } else if (!isLoading && data?.status === 404) {
                toast.error(
                    "You are trying to access a QRCode that does not exist."
                );
            } else if (
                data?.status === 203 ||
                (data?.status === 200 &&
                    !token &&
                    !isLoading &&
                    !data?.Details?.name)
            ) {
                setOpenLoginModal(true);
            } else if (
                (!token &&
                    !isLoading &&
                    data?.status !== 203 &&
                    data?.status !== 200) ||
                data?.status === 201
            ) {
                setOpenEmptyModal(true);
            }
        }
    }, [data, pathname, id, token, qrCodes]);

    useEffect(() => {
        setCurrentStep(0);
        setSteps([
            {
                selector: "#legacy-link",
                content:
                    "Welcome! Let’s set up your page. This tutorial will help guide you.",
            },
            {
                selector: "#edit",
                content:
                    "This is the edit/update button. You can access this button on any tab when logged in your account. It will allow you to make" +
                    " changes to the specific tab you are on.",
            },
            {
                selector: "#navigation",
                content: (
                    <div className="space-y-4">
                        <p>
                            The menu above is your main navigation. Visit each
                            tab to add/update features on that screen. For
                            future changes, visit{" "}
                            <a
                                className={"underline underline-offset-2"}
                                href={BASE_URL}
                            >
                                www.livinglegacyqr.com
                            </a>{" "}
                            and log in with your credentials at the top of this
                            site. Now select the “Go to initial setup” button
                            below and let’s get started!
                        </p>
                        <button
                            onClick={() => {
                                setIsOpen(false);
                                setOpenEmptyModal(false);
                                handleOpenEditBioModal();
                            }}
                            className="bg-neutral-300 hover:bg-neutral-400 transition-all duration-200 ease-in-out px-4 pb-0.5 h-8 rounded-full"
                        >
                            Go to initial setup
                        </button>
                    </div>
                ),
            },
        ]);
    }, []);

    useEffect(() => {
        refetch();
        dispatch(setID(id));
    }, [id]);

    useEffect(() => {
        if (token) {
            refetchQrCodes();
        }
    }, [token]);

    const handleSubmit = async (theme) => {
        try {
            const {
                location,
                cover_picture,
                profile_picture,
                dob,
                dod,
                ...bio
            } = data.Details;

            let relations = data.Details?.relations || [];

            const payload = {
                ...bio,
                ...(location?.lng ? { longitude: location?.lng } : {}),
                ...(location?.lat ? { latitude: location?.lat } : {}),
                dark_theme: theme,
                dob,
                dod,
                relations: JSON.stringify(relations),
            };

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
            } else {
                toast.error("Profile update failed");
            }
        } catch (error) {
            console.log("error", error);
        }
    };

    return (
        <Wrapper isLoading={isLoading || loadingCodes}>
            {data?.Details?.name && (
                <Helmet>
                    <meta
                        name="description"
                        content="Create a lasting tribute for your loved one with our user-friendly platform. Share memories, photos, and videos to celebrate their life."
                    />
                    <meta
                        name="keywords"
                        content="tribute page, memorial, legacy, remembrance, photos, videos, memories, family, friends"
                    />
                    <title>
                        {`${data?.Details?.name} Tribute Page | ${getYear(
                            data?.Details?.dob
                        )}-${getYear(
                            data?.Details?.dod
                        )} | Remembering a Beloved One`}
                    </title>
                    <meta
                        property="og:title"
                        content={`${
                            data?.Details?.name
                        } Tribute Page | ${getYear(
                            data?.Details?.dob
                        )}-${getYear(
                            data?.Details?.dod
                        )} | Remembering a Beloved One`}
                    />

                    <meta
                        property="og:description"
                        content={`Celebrate the life of ${data?.Details?.name} with videos, photos, and memories shared by family and friends on this lovingly curated tribute page. Join us in honoring his legacy.`}
                    />
                    <meta property="og:type" content="website" />
                    <meta
                        property="og:url"
                        content={LIVE_URL + id + "/legacy"}
                    />
                    <meta
                        property="og:image"
                        content={data?.Details?.profile_picture}
                    />
                    <meta
                        property="og:image:alt"
                        content={data?.Details?.name + " Tribute Page"}
                    />
                    <meta
                        property="og:site_name"
                        content="Living Legacy Project"
                    />
                    <meta
                        name="twitter:card"
                        content={data?.Details?.cover_picture}
                    />
                    <meta
                        name="twitter:site"
                        content={LIVE_URL + id + "/legacy"}
                    />
                    <meta
                        name="twitter:title"
                        content={`${
                            data?.Details?.name
                        } Tribute Page | ${getYear(
                            data?.Details?.dob
                        )}-${getYear(
                            data?.Details?.dod
                        )} | Remembering a Beloved One`}
                    />
                    <meta
                        name="twitter:description"
                        content={`Celebrate the life of ${data?.Details?.name} with videos, photos, and memories shared by family and friends on this lovingly curated tribute page. Join us in honoring his legacy.`}
                    />
                    <meta
                        name="twitter:image"
                        content={data?.Details?.profile_picture}
                    />
                </Helmet>
            )}

            <div className={`flex flex-col items-center pb-5`}>
                <div className="w-full max-w-5xl py-1">
                    <div className="w-full relative px-5">
                        <Navigation
                            refetch={refetch}
                            setOpenLoginModal={setOpenLoginModal}
                            setOpenChangePasswordModal={
                                setOpenChangePasswordModal
                            }
                        />
                        {!pathname.includes("settings") && (
                            <>
                                <div
                                    className={
                                        "min-w-full overflow-hidden border-white/10 min-h-40 md:min-h-[300px] border mt-12 md:mt-6 h-auto rounded-md flex items-center justify-center"
                                    }
                                >
                                    {data?.Details?.cover_picture &&
                                    data?.Details.cover_picture?.includes(
                                        "."
                                    ) ? (
                                        <img
                                            className="w-full h-auto min-h-[100px] max-h-[400px]"
                                            src={
                                                data?.Details?.cover_picture ||
                                                ""
                                            }
                                        />
                                    ) : (
                                        <p className="w-full h-full text-center text-white/50 flex items-center justify-center text-lg">
                                            No cover photo uploaded yet
                                        </p>
                                    )}
                                </div>
                                <div
                                    className={
                                        "w-full relative flex items-center gap-3  justify-between flex-1 flex-wrap sm:flex-nowrap -mt-3 md:-mt-6"
                                    }
                                >
                                    <div className=" flex flex-row justify-between w-full  md:justify-between ">
                                        <div className="flex items-stretch gap-2 justify-between flex-wrap md:flex-nowrap flex-grow w-full">
                                            <div
                                                className={`flex ${
                                                    data?.Details?.relations?.filter(
                                                        (el) => {
                                                            return (
                                                                el.name ===
                                                                    "SPOUSE" &&
                                                                el.is_legacy ===
                                                                    true
                                                            );
                                                        }
                                                    ).length !== 0
                                                        ? "items-center justify-center flex-col"
                                                        : "justify-start flex-row"
                                                }  gap-2 flex-grow md:flex-row md:justify-start`}
                                            >
                                                {data?.Details
                                                    ?.profile_picture ? (
                                                    <div className="border-black absolute top-[-50px] overflow-hidden border-2 bg-black rounded-full md:min-w-[160px] md:min-h-[160px] min-w-24 min-h-24 max-w-28 max-h-28 md:max-w-[135px] md:max-h-[135px]">
                                                        <img
                                                            className=" w-full h-full object-cover md:min-w-[160px] md:min-h-[160px] min-w-24 min-h-24 max-w-24 max-h-24 md:max-w-[135px] md:max-h-[135px]"
                                                            src={
                                                                data?.Details
                                                                    ?.profile_picture ||
                                                                ""
                                                            }
                                                        />
                                                    </div>
                                                ) : (
                                                    ""
                                                )}

                                                <div
                                                    className={`${
                                                        data?.Details?.relations?.filter(
                                                            (el) => {
                                                                return (
                                                                    el.name ===
                                                                        "SPOUSE" &&
                                                                    el.is_legacy ===
                                                                        true
                                                                );
                                                            }
                                                        ).length !== 0
                                                            ? "hidden"
                                                            : "block"
                                                    }   top-[-50px] overflow-hidden rounded-full md:min-w-[160px] md:min-h-[120px] min-w-24 min-h-24 max-w-28 max-h-28 md:max-w-[160px] md:max-h-[125px] md:block`}
                                                ></div>
                                                <div className=" flex flex-row gap-20 w-[100%] md:w-auto mt-2 md:mt-8  cursor-pointer md:gap-3">
                                                    <div
                                                        className={`${
                                                            data?.Details?.relations?.filter(
                                                                (el) => {
                                                                    return (
                                                                        el.name ===
                                                                            "SPOUSE" &&
                                                                        el.is_legacy ===
                                                                            true
                                                                    );
                                                                }
                                                            ).length !== 0
                                                                ? "flex-col flex-1"
                                                                : "flex-row ml-3"
                                                        } flex items-center md:items-start md:flex-row md:ml-0`}
                                                    >
                                                        <div
                                                            className={`flex-col ${
                                                                data?.Details?.relations?.filter(
                                                                    (el) => {
                                                                        return (
                                                                            el.name ===
                                                                                "SPOUSE" &&
                                                                            el.is_legacy ===
                                                                                true
                                                                        );
                                                                    }
                                                                ).length !== 0
                                                                    ? "mr-auto md:mr-0"
                                                                    : ""
                                                            }`}
                                                            onClick={() => {
                                                                dispatch(
                                                                    changeOrbit(
                                                                        "main"
                                                                    )
                                                                );
                                                            }}
                                                        >
                                                            <div>
                                                                {data?.Details
                                                                    ?.name && (
                                                                    <div className="flex-grow md:flex-grow-0">
                                                                        <div className="flex flex-col  md:justify-start md:items-center md:flex-row md:gap-4">
                                                                            <p
                                                                                className={`${
                                                                                    data?.Details?.relations?.filter(
                                                                                        (
                                                                                            el
                                                                                        ) =>
                                                                                            el.name ===
                                                                                                "SPOUSE" &&
                                                                                            el.is_legacy ===
                                                                                                true
                                                                                    )
                                                                                        .length !==
                                                                                        0 &&
                                                                                    (textWillWrap(
                                                                                        data
                                                                                            ?.Details
                                                                                            ?.name
                                                                                    ) ||
                                                                                        textWillWrap(
                                                                                            data?.Details?.relations?.filter(
                                                                                                (
                                                                                                    el
                                                                                                ) => {
                                                                                                    return (
                                                                                                        el.name ===
                                                                                                            "SPOUSE" &&
                                                                                                        el.is_legacy ===
                                                                                                            true
                                                                                                    );
                                                                                                }
                                                                                            )[0]
                                                                                                ?.person_name
                                                                                        ))
                                                                                        ? "min-h-12"
                                                                                        : ""
                                                                                } text-wrap break-words md:text-[22px] font-medium capitalize mt-3 md:mt-0 md:text-nowrap text-left ${
                                                                                    spouseOrbt ===
                                                                                        "main" &&
                                                                                    currentSection ===
                                                                                        "legacy" &&
                                                                                    data?.Details?.relations?.filter(
                                                                                        (
                                                                                            el
                                                                                        ) => {
                                                                                            return (
                                                                                                el.name ===
                                                                                                    "SPOUSE" &&
                                                                                                el.is_legacy ===
                                                                                                    true
                                                                                            );
                                                                                        }
                                                                                    )[0]
                                                                                        ?.person_name
                                                                                        ? "underline underline-offset-4"
                                                                                        : ""
                                                                                }`}
                                                                            >
                                                                                {
                                                                                    data
                                                                                        ?.Details
                                                                                        ?.name
                                                                                }
                                                                            </p>
                                                                            {/* {data?.Details?.relations?.filter(
                                        (el) => {
                                          return (
                                            el.name === "SPOUSE" &&
                                            el.is_legacy === true
                                          );
                                        }
                                      )[0]?.person_name ? (
                                        <GrView
                                          className={`${
                                            spouseOrbt === "main" &&
                                            currentSection === "legacy"
                                              ? "" // Keeps space but hides the icon
                                              : "invisible"
                                          } mr-auto mt-1`}
                                        />
                                      ) : (
                                        ""
                                      )} */}
                                                                        </div>

                                                                        <p
                                                                            className={`text-xs md:text-[14px] text-nowrap ${
                                                                                data
                                                                                    ?.Details
                                                                                    ?.dark_theme
                                                                                    ? "text-gray-300"
                                                                                    : "text-black"
                                                                            }  font-normal mt-[5px]`}
                                                                        >
                                                                            {dateMMDDYYYYFormat(
                                                                                data
                                                                                    ?.Details
                                                                                    ?.dob
                                                                            )}{" "}
                                                                            -{" "}
                                                                            {data
                                                                                ?.Details
                                                                                ?.dod &&
                                                                            data
                                                                                ?.Details
                                                                                ?.dod !==
                                                                                ""
                                                                                ? dateMMDDYYYYFormat(
                                                                                      data
                                                                                          ?.Details
                                                                                          ?.dod
                                                                                  )
                                                                                : "Present"}
                                                                        </p>
                                                                    </div>
                                                                )}
                                                            </div>

                                                            {data?.Details
                                                                ?.facebook ||
                                                            data?.Details
                                                                ?.instagram ||
                                                            data?.Details
                                                                ?.twitter ||
                                                            data?.Details
                                                                ?.spouse_facebook ||
                                                            data?.Details
                                                                ?.spouse_instagram ||
                                                            data?.Details
                                                                ?.spouse_twitter ? (
                                                                <div className="min-h-10 flex flex-row items-center  mt-1">
                                                                    {data
                                                                        ?.Details
                                                                        ?.facebook && (
                                                                        <a
                                                                            target="_blank"
                                                                            href={
                                                                                data
                                                                                    ?.Details
                                                                                    ?.facebook
                                                                            }
                                                                            className=" pr-3 min-h-8 flex items-center justify-center gap-2 transition-all duration-300 ease-in-out rounded-full hover:underline active:scale-95"
                                                                            rel="noreferrer"
                                                                        >
                                                                            <RiFacebookLine
                                                                                size={
                                                                                    18
                                                                                }
                                                                            />
                                                                        </a>
                                                                    )}
                                                                    {data
                                                                        ?.Details
                                                                        ?.instagram && (
                                                                        <a
                                                                            target="_blank"
                                                                            href={
                                                                                data
                                                                                    ?.Details
                                                                                    ?.instagram
                                                                            }
                                                                            className="pl-2 pr-3 h-8 flex items-center justify-center gap-2 transition-all duration-300 ease-in-out rounded-full hover:underline active:scale-95"
                                                                            rel="noreferrer"
                                                                        >
                                                                            <RxInstagramLogo
                                                                                size={
                                                                                    18
                                                                                }
                                                                            />
                                                                        </a>
                                                                    )}
                                                                    {data
                                                                        ?.Details
                                                                        ?.twitter && (
                                                                        <a
                                                                            target="_blank"
                                                                            href={
                                                                                data
                                                                                    ?.Details
                                                                                    ?.twitter
                                                                            }
                                                                            className="pl-2 pr-3 h-8 flex items-center justify-center gap-2 transition-all duration-300 ease-in-out rounded-full hover:underline active:scale-95"
                                                                            rel="noreferrer"
                                                                        >
                                                                            <RiTwitterXFill
                                                                                size={
                                                                                    18
                                                                                }
                                                                            />
                                                                        </a>
                                                                    )}
                                                                </div>
                                                            ) : (
                                                                ""
                                                            )}
                                                        </div>
                                                        <div
                                                            className={` ${
                                                                data?.Details?.relations?.filter(
                                                                    (el) => {
                                                                        return (
                                                                            el.name ===
                                                                                "SPOUSE" &&
                                                                            el.is_legacy ===
                                                                                true
                                                                        );
                                                                    }
                                                                ).length !==
                                                                    0 &&
                                                                data?.is_legacy ===
                                                                    true
                                                                    ? "mr-auto"
                                                                    : "md:ml-3"
                                                            } min-h-10 !mr-auto flex flex-col justify-center items-start md:justify-start md:ml-5 `}
                                                        >
                                                            {data?.Details
                                                                ?.badge ? (
                                                                <img
                                                                    src={`/${
                                                                        data
                                                                            ?.Details
                                                                            ?.badge
                                                                    }_${
                                                                        data
                                                                            ?.Details
                                                                            ?.dark_theme
                                                                            ? "dark"
                                                                            : "light"
                                                                    }.svg`}
                                                                    alt=""
                                                                    className="w-14 h-14"
                                                                />
                                                            ) : (
                                                                ""
                                                            )}
                                                        </div>
                                                    </div>
                                                    {data?.Details?.relations?.some(
                                                        (el) =>
                                                            el.name ===
                                                                "SPOUSE" &&
                                                            el.is_legacy ===
                                                                true
                                                    ) && (
                                                        <div className=" flex flex-1 md:flex-none flex-col items-center cursor-pointer md:items-start md:flex-row md:justify-center">
                                                            <div
                                                                className="flex-col md:border-l-2 ml-auto cursor-pointer md:ml-0"
                                                                onClick={() => {
                                                                    dispatch(
                                                                        changeOrbit(
                                                                            "spouse"
                                                                        )
                                                                    );
                                                                }}
                                                            >
                                                                <div className=" md:ml-4 ">
                                                                    {data?.Details?.relations?.filter(
                                                                        (
                                                                            el
                                                                        ) => {
                                                                            return (
                                                                                el.name ===
                                                                                    "SPOUSE" &&
                                                                                el.is_legacy ===
                                                                                    true
                                                                            );
                                                                        }
                                                                    ).length !==
                                                                        0 && (
                                                                        <div className="flex-grow md:flex-grow-0 ">
                                                                            <div>
                                                                                <div className="flex flex-col  justify-start items-center md:flex-row md:gap-4">
                                                                                    <p
                                                                                        className={`text-wrap text-right ml-auto break-words ${
                                                                                            textWillWrap(
                                                                                                data
                                                                                                    ?.Details
                                                                                                    ?.name
                                                                                            ) ||
                                                                                            textWillWrap(
                                                                                                data?.Details?.relations?.filter(
                                                                                                    (
                                                                                                        el
                                                                                                    ) => {
                                                                                                        return (
                                                                                                            el.name ===
                                                                                                                "SPOUSE" &&
                                                                                                            el.is_legacy ===
                                                                                                                true
                                                                                                        );
                                                                                                    }
                                                                                                )[0]
                                                                                                    ?.person_name
                                                                                            )
                                                                                                ? "min-h-12"
                                                                                                : ""
                                                                                        }  md:text-[22px] font-medium capitalize mt-3 md:mt-0 md:text-left md:ml-0 ${
                                                                                            spouseOrbt ===
                                                                                                "spouse" &&
                                                                                            currentSection ===
                                                                                                "legacy"
                                                                                                ? "underline underline-offset-4"
                                                                                                : ""
                                                                                        }`}
                                                                                    >
                                                                                        {
                                                                                            data?.Details?.relations?.filter(
                                                                                                (
                                                                                                    el
                                                                                                ) => {
                                                                                                    return (
                                                                                                        el.name ===
                                                                                                            "SPOUSE" &&
                                                                                                        el.is_legacy ===
                                                                                                            true
                                                                                                    );
                                                                                                }
                                                                                            )[0]
                                                                                                ?.person_name
                                                                                        }
                                                                                    </p>
                                                                                    {/* <GrView
                                            className={`${
                                              spouseOrbt === "spouse" &&
                                              currentSection === "legacy"
                                                ? "visible" // Keeps space but hides the icon
                                                : "opacity-0"
                                            } ml-auto mt-1`}
                                          /> */}
                                                                                </div>
                                                                                <p
                                                                                    className={`text-right md:text-left text-xs md:text-[14px] text-nowrap ${
                                                                                        data
                                                                                            ?.Details
                                                                                            ?.dark_theme
                                                                                            ? "text-gray-300"
                                                                                            : "text-black"
                                                                                    }  font-normal mt-[5px]`}
                                                                                >
                                                                                    {dateMMDDYYYYFormat(
                                                                                        data?.Details?.relations?.filter(
                                                                                            (
                                                                                                el
                                                                                            ) => {
                                                                                                return (
                                                                                                    el.name ===
                                                                                                        "SPOUSE" &&
                                                                                                    el.is_legacy ===
                                                                                                        true
                                                                                                );
                                                                                            }
                                                                                        )[0]
                                                                                            ?.dob
                                                                                    )}
                                                                                    {data?.Details?.relations?.filter(
                                                                                        (
                                                                                            el
                                                                                        ) => {
                                                                                            return (
                                                                                                el.name ===
                                                                                                    "SPOUSE" &&
                                                                                                el.is_legacy ===
                                                                                                    true
                                                                                            );
                                                                                        }
                                                                                    )[0]
                                                                                        ?.dod
                                                                                        ? `- ${dateMMDDYYYYFormat(
                                                                                              data?.Details?.relations?.filter(
                                                                                                  (
                                                                                                      el
                                                                                                  ) => {
                                                                                                      return (
                                                                                                          el.name ===
                                                                                                              "SPOUSE" &&
                                                                                                          el.is_legacy ===
                                                                                                              true
                                                                                                      );
                                                                                                  }
                                                                                              )[0]
                                                                                                  ?.dod
                                                                                          )}`
                                                                                        : "- Present"}
                                                                                </p>
                                                                            </div>
                                                                        </div>
                                                                    )}
                                                                </div>
                                                                {data?.Details
                                                                    ?.facebook ||
                                                                data?.Details
                                                                    ?.instagram ||
                                                                data?.Details
                                                                    ?.twitter ||
                                                                data?.Details
                                                                    ?.spouse_facebook ||
                                                                data?.Details
                                                                    ?.spouse_instagram ||
                                                                data?.Details
                                                                    ?.spouse_twitter ? (
                                                                    <div className="min-h-10 flex items-center justify-end  flex-row mt-1  mr-auto ">
                                                                        {data
                                                                            ?.Details
                                                                            ?.spouse_facebook && (
                                                                            <a
                                                                                target="_blank"
                                                                                href={
                                                                                    data
                                                                                        ?.Details
                                                                                        ?.spouse_facebook
                                                                                }
                                                                                className=" pr-3 h-8 flex items-center justify-center gap-2 transition-all duration-300 ease-in-out rounded-full hover:underline active:scale-95"
                                                                                rel="noreferrer"
                                                                            >
                                                                                <RiFacebookLine
                                                                                    size={
                                                                                        18
                                                                                    }
                                                                                />
                                                                            </a>
                                                                        )}
                                                                        {data
                                                                            ?.Details
                                                                            ?.spouse_instagram && (
                                                                            <a
                                                                                target="_blank"
                                                                                href={
                                                                                    data
                                                                                        ?.Details
                                                                                        ?.spouse_instagram
                                                                                }
                                                                                className="pl-2 pr-3 h-8 flex items-center justify-center gap-2 transition-all duration-300 ease-in-out rounded-full hover:underline active:scale-95"
                                                                                rel="noreferrer"
                                                                            >
                                                                                <RxInstagramLogo
                                                                                    size={
                                                                                        18
                                                                                    }
                                                                                />
                                                                            </a>
                                                                        )}
                                                                        {data
                                                                            ?.Details
                                                                            ?.spouse_twitter && (
                                                                            <a
                                                                                target="_blank"
                                                                                href={
                                                                                    data
                                                                                        ?.Details
                                                                                        ?.spouse_twitter
                                                                                }
                                                                                className="pl-2 pr-3 h-8 flex items-center justify-center gap-2 transition-all duration-300 ease-in-out rounded-full hover:underline active:scale-95"
                                                                                rel="noreferrer"
                                                                            >
                                                                                <RiTwitterXFill
                                                                                    size={
                                                                                        18
                                                                                    }
                                                                                />
                                                                            </a>
                                                                        )}
                                                                    </div>
                                                                ) : (
                                                                    ""
                                                                )}
                                                            </div>
                                                            <div className="min-h-10 flex ml-auto justify-center items-center md:justify-start md:ml-5 ">
                                                                {data?.Details
                                                                    ?.spouse_badge ? (
                                                                    <img
                                                                        src={`/${
                                                                            data
                                                                                ?.Details
                                                                                ?.spouse_badge
                                                                        }_${
                                                                            data
                                                                                ?.Details
                                                                                ?.dark_theme
                                                                                ? "dark"
                                                                                : "light"
                                                                        }.svg`}
                                                                        alt=""
                                                                        className="w-14 h-14"
                                                                    />
                                                                ) : (
                                                                    ""
                                                                )}
                                                            </div>
                                                        </div>
                                                    )}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div className="flex items-center gap-4 justify-end">
                                    <button
                                        title="Share"
                                        onClick={() => setOpenShareModal(true)}
                                        className={` ${
                                            data?.Details?.dark_theme
                                                ? "text-white border-[#24292F] hover:bg-[#24292F]/90"
                                                : "text-black border-grey hover:bg-[#24292F]/90"
                                        }text-white float-right select-none border hidden mt-5 md:mt-0  active:scale-95 transition-all duration-200 ease-in-out  font-medium rounded-full text-sm p-2.5 md:pl-5 md:pr-6 md:py-2.5 text-center md:flex items-center gap-2`}
                                    >
                                        <IoMdShare size={19} />
                                        <span className="hidden md:block">
                                            Share
                                        </span>
                                    </button>
                                    {token &&
                                        isAdmin() &&
                                        !pathname.includes("tribute") && (
                                            <div className="w-full md:w-auto z-10 flex items-center justify-end fixed bottom-20 sm:bottom-24 mb:bottom-0 pb-6 md:pb-0 md:right-0 right-3 md:static">
                                                <button
                                                    id="edit"
                                                    onClick={handleClickEdit}
                                                    type="button"
                                                    className={`${
                                                        data?.Details
                                                            ?.dark_theme
                                                            ? "text-white bg-black  md:bg-[#24292F] md:active:bg-[#333333] md:hover:bg-[#24292F]/90 md:border-transparent"
                                                            : "text-black border-[#808080] bg-white hover:bg-[#808080]/90"
                                                    }
                     select-none transition-all duration-200 ease-in-out  font-medium md:rounded-lg md:pl-5 md:pr-6 md:py-2.5  border-[0.5px] active:scale-95 rounded-full text-sm sm:text-base py-1.5 sm:py-2 px-3 sm:px-4 text-center flex items-center justify-center gap-2`}
                                                >
                                                    <FaUserEdit size={19} />
                                                    Add/Update
                                                </button>
                                            </div>
                                        )}
                                </div>
                            </>
                        )}
                    </div>
                    <div className="p-6 py-10 min-h-screen">
                        <Outlet />
                    </div>
                </div>
                <div
                    className={`${
                        token && isAdmin() ? "bottom-36" : "bottom-24"
                    } fixed bottom-36 right-3 flex flex-col items-end gap-3 md:bottom-32`}
                >
                    {data?.Details?.spotify && (
                        <div className="flex justify-center items-center gap-2">
                            {!isSpotifyVisible ? (
                                <img
                                    onClick={() => {
                                        setSpotifyVisible(true);
                                    }}
                                    src="/spotify.png"
                                    alt=""
                                    className={`cursor-pointer`}
                                    width={50}
                                    height={50}
                                />
                            ) : (
                                ""
                            )}
                            <div className="relative">
                                <button
                                    onClick={() => {
                                        setSpotifyVisible(false);
                                    }}
                                    type="button"
                                    className={`${
                                        isSpotifyVisible ? "block" : "hidden"
                                    } absolute top-[-10px] text-gray-400 transition-all duration-200 ease-in-out bg-black bg-opacity-70 hover:bg-opacity-25 active:bg-opacity-30 rounded-full text-sm w-6 h-6 ms-auto inline-flex justify-center items-center `}
                                >
                                    <IoClose size={24} />
                                </button>
                                <Spotify
                                    wide
                                    className={`w-full ${
                                        isSpotifyVisible ? "block" : "hidden"
                                    }`}
                                    link={data?.Details?.spotify}
                                />
                            </div>
                        </div>
                    )}
                </div>
                <div className="fixed bottom-3 right-3 flex flex-col items-end gap-3">
                    {token && isAdmin() && (
                        <>
                            <div className="w-full md:w-auto z-10 flex items-start justify-start fixed bottom-0 pl-3 sm:bottom-24 mb:bottom-20 pb-6 md:right-3 right-3 md:bottom-10 ">
                                <div
                                    className={`${
                                        data?.Details?.dark_theme
                                            ? "text-white  bg-white "
                                            : "text-black  bg-white"
                                    }   select-none border-[1px] border-black ml-3 hover:scale-105 active:scale-95 transition-all duration-300 ease-in-out rounded-full text-base sm:text-xl py-1.5 sm:py-2 px-3 sm:px-4 text-center flex items-center justify-center gap-2`}
                                >
                                    <div className="flex justify-start items-center z-50 ">
                                        <label className="relative flex items-center cursor-pointer">
                                            <img
                                                width={20}
                                                height={30}
                                                src={`${
                                                    dark_theme
                                                        ? "/power.png"
                                                        : "/dark.png"
                                                }`}
                                                onClick={() => {
                                                    setDarkTheme(
                                                        (prevTheme) => {
                                                            const newTheme =
                                                                !prevTheme;
                                                            handleSubmit(
                                                                newTheme
                                                            ); // Pass the updated value
                                                            return newTheme;
                                                        }
                                                    );
                                                }}
                                            />
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </>
                    )}
                    <button
                        onClick={() => setOpenShareModal(true)}
                        title="Share"
                        className={`${
                            data?.Details?.dark_theme
                                ? "text-white hover:bg-[#121212] bg-black "
                                : "text-black  bg-white"
                        }  md:hidden select-none border-[0.5px] hover:scale-105 active:scale-95 transition-all duration-300 ease-in-out rounded-full text-base sm:text-xl py-1.5 sm:py-2 px-3 sm:px-4 text-center flex items-center justify-center gap-2`}
                    >
                        <IoMdShare />
                        <span className="text-sm sm:text-base">Share</span>
                    </button>

                    <button
                        title="Add Tribute"
                        onClick={() => setOpenPostModal(true)}
                        className={`${
                            data?.Details?.dark_theme
                                ? "hover:bg-[#121212] bg-black text-white"
                                : "hover:bg-[#808080] bg-white text-black"
                        } z-10 flex select-none items-center gap-2 text-base sm:text-xl  hover:scale-105 md:hover:scale-100 border-[0.5px] tracking-[1px] font-normal active:scale-95  py-1.5 sm:py-2 px-3 sm:px-4 transition-all duration-300 ease-in-out rounded-full shadow-lg`}
                    >
                        <HiOutlinePencilSquare />
                        <span className="md:uppercase text-sm sm:text-base">
                            Add Tribute
                        </span>
                    </button>
                </div>
            </div>
            <Footer />
            {/* Modals */}
            <ShareModal
                open={openShareModal}
                setOpen={setOpenShareModal}
                theme={data?.Details?.dark_theme ?? false}
            />
            <PostModal
                theme={data?.Details?.dark_theme}
                refetch={refetch}
                open={openPostModal}
                setOpen={setOpenPostModal}
            />
            <EditBioModal
                step={step}
                setStep={setStep}
                userData={data?.Details}
                open={openEditBioModal}
                refetch={refetch}
                setOpen={setOpenEditBioModal}
                setOpenSuccessModal={setOpenSuccessModal}
            />
            {openUploadPhotosModal && (
                <UploadPhotosModal
                    refetch={refetch}
                    data={data?.Details}
                    open={openUploadPhotosModal}
                    setOpen={setOpenUploadPhotosModal}
                />
            )}
            <AddTimelineModal
                data={data?.Details}
                refetch={refetch}
                open={openTimlineModal}
                setOpen={setOpenTimlineModal}
            />
            <RegisterModal
                setOpenLoginModal={setOpenLoginModal}
                open={openRegisterModal}
                setOpen={setOpenRegisterModal}
            />
            <LoginModal
                hideCloseIcon={!token && !isLoading && !data?.Details?.name}
                refetch={refetch}
                setOpenEmptyModal={setOpenEmptyModal}
                setOpenRegisterModal={setOpenRegisterModal}
                setOpenResetPasswordModal={setOpenResetPasswordModal}
                open={openLoginModal}
                setOpen={setOpenLoginModal}
                setOpenChangePasswordModal={setOpenChangePasswordModal}
            />
            <ResetPasswordModal
                setOpenLoginModal={setOpenLoginModal}
                open={openResetPasswordModal}
                setOpenRegisterModal={setOpenRegisterModal}
                setOpen={setOpenResetPasswordModal}
            />
            <EmptyModal
                setIsOpen={setIsOpen}
                open={openEmptyModal}
                refetch={refetch}
                setOpen={setOpenEmptyModal}
                setOpenLoginModal={setOpenLoginModal}
                setOpenRegisterModal={setOpenRegisterModal}
            />
            <ChangePasswordModal
                open={openChangePasswordModal}
                setOpen={setOpenChangePasswordModal}
                setOpenRegisterModal={setOpenRegisterModal}
                setOpenLoginModal={setOpenLoginModal}
                theme={data?.Details?.dark_theme}
            />
            <FamilyTreeModal
                refetch={refetch}
                open={openFamilyTreeModal}
                setOpen={setOpenFamilyTreeModal}
            />
            <InstructionsModal
                setOpenFamilyTreeModal={setOpenFamilyTreeModal}
                setOpenUploadPhotosModal={setOpenUploadPhotosModal}
                open={openSuccessModal}
                setOpen={setOpenSuccessModal}
                setOpenTimlineModal={setOpenTimlineModal}
            />
        </Wrapper>
    );
};
