import { Link, NavLink } from "react-router-dom/dist";
import { IoIosMenu } from "react-icons/io";
import {
    getAuthToken,
    getID,
    removeUserToken,
    useAppDispatch,
    useAppSelector,
} from "../redux";
import { useQueryClient } from "@tanstack/react-query";
import { useFetchQrCodesData } from "../services/index.js";

export const Navigation = ({
    setOpenLoginModal,
    refetch,
    setOpenChangePasswordModal,
}) => {
    const id = useAppSelector(getID);
    const token = useAppSelector(getAuthToken);
    const dipatch = useAppDispatch();
    const queryClient = useQueryClient();

    const { data: qrCodes } = useFetchQrCodesData();

    const isAdmin = () => {
        const ids = qrCodes?.data?.map((item) => item.uuid);
        return ids?.includes(id);
    };

    const navs = [
        {
            id: "legacy-link",
            title: "Legacy",
            to: `/${id}/legacy`,
        },
        {
            id: "family-link",
            title: "Family Tree",
            to: `/${id}/family-tree`,
        },
        {
            id: "gallery-link",
            title: "Gallery",
            to: `/${id}/gallery`,
        },
        {
            id: "timeline-link",
            title: "Timeline",
            to: `/${id}/timeline`,
        },
        {
            id: "tribute-link",
            title: "Tribute",
            to: `/${id}/tribute`,
        },
        {
            title: "hamburger",
        },
    ];
    return (
        <div className="fixed top-2 left-0 right-0 z-50 w-full flex justify-center">
            <div
                id="navigation"
                className="flex items-center bg-white bg-opacity-70 backdrop-filter backdrop-blur-sm rounded-full gap-2 sm:gap-3 px-4 md:px-6 relative"
            >
                {navs.map((nav, index) =>
                    nav.to ? (
                        <NavLink
                            to={nav.to}
                            className={({ isActive }) =>
                                `${
                                    isActive ? " font-medium " : ""
                                } px-0 sm:px-2 py-2 cursor-pointer text-xs sm:text-base text-black hover:font-medium sm:hover:scale-110 transition-transform duration-50 ease-in-out`
                            }
                            key={index}
                            id={nav.id}
                        >
                            {nav.title}
                        </NavLink>
                    ) : (
                        <div
                            key={index}
                            className="group flex sm:pr-2 items-center justify-center h-full transition-all duration-150 ease-in-out"
                        >
                            <span className="py-2 cursor-pointer h-full text-black">
                                <IoIosMenu className="text-lg sm:text-2xl" />
                            </span>

                            <div
                                id="dropdownHover"
                                className={
                                    "z-10 absolute top-7 right-4 group-hover:block hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-44"
                                }
                            >
                                <ul
                                    className="py-2 text-gray-700 dark:text-gray-200"
                                    aria-labelledby="dropdownHoverButton"
                                >
                                    {!token && (
                                        <li>
                                            <span
                                                onClick={() =>
                                                    setOpenLoginModal(true)
                                                }
                                                className="select-none cursor-pointer block px-4 py-2 text-gray-700 hover:bg-gray-100 active:bg-gray-200"
                                            >
                                                Login
                                            </span>
                                        </li>
                                    )}
                                    {token && (
                                        <li>
                                            <Link
                                                to={"/my-qrcodes"}
                                                className="select-none cursor-pointer block px-4 py-2 text-gray-700 hover:bg-gray-100 active:bg-gray-200"
                                            >
                                                My QRCodes
                                            </Link>
                                        </li>
                                    )}
                                    {token && isAdmin() && (
                                        <li
                                            onClick={() =>
                                                setOpenChangePasswordModal(true)
                                            }
                                        >
                                            <span className="select-none cursor-pointer block px-4 py-2 text-gray-700 hover:bg-gray-100 active:bg-gray-200">
                                                Change Password
                                            </span>
                                        </li>
                                    )}
                                    {token && isAdmin() && (
                                        <li>
                                            <Link
                                                to={`/${id}/settings`}
                                                className="select-none cursor-pointer block px-4 py-2 text-gray-700 hover:bg-gray-100 active:bg-gray-200"
                                            >
                                                Settings
                                            </Link>
                                        </li>
                                    )}
                                    {token && (
                                        <li>
                                            <span
                                                onClick={() => {
                                                    dipatch(removeUserToken());
                                                    queryClient.clear();
                                                    refetch();
                                                }}
                                                className="select-none cursor-pointer block px-4 py-2 text-gray-700 hover:bg-gray-100 active:bg-gray-200"
                                            >
                                                Logout
                                            </span>
                                        </li>
                                    )}
                                </ul>
                            </div>
                        </div>
                    )
                )}
            </div>
        </div>
    );
};
