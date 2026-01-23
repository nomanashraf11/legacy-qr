import { getAuthToken, getID, useAppSelector } from "../redux";
import { useMutation } from "@tanstack/react-query";
import { connectUserToLinkAsync } from "../services";
import { toast } from "react-toastify";
import { Spinner } from "./Spinner";

export const EmptyModal = ({
    open,
    setOpen,
    setOpenLoginModal,
    setOpenRegisterModal,
    setIsOpen,
    refetch,
    versionType = "full", // Default to 'full' if not provided
}) => {
    const token = useAppSelector(getAuthToken);
    const id = useAppSelector(getID);

    const { mutateAsync, isPending } = useMutation({
        mutationFn: connectUserToLinkAsync,
    });

    const linkUser = async () => {
        try {
            const { data } = await mutateAsync(id);
            if (data.status === 201) {
                toast.success("Profile linked successfully");
                setOpen(false);
                setIsOpen(true);
                refetch();
            } else {
                toast.error(data.message);
            }
        } catch (error) {
            console.log(error);
        }
    };

    return (
        <div
            className={
                "overflow-x-hidden fixed top-0 right-0 left-0 bottom-0 max-h-full !z-50 flex justify-center items-center w-full md:inset-0 transition-all duration-200 ease-in-out " +
                (open ? "block" : "hidden")
            }
        >
            <div className="relative p-4 w-full max-w-[500px] max-h-full">
                <div className="relative bg-[#242526] rounded-lg shadow">
                    <div className="max-h-[400px] p-8">
                        <div className="text-lg">
                            <p>Welcome to Living Legacy!</p>
                            {token && (
                                <p>
                                    Click 'Connect' below to connect your{" "}
                                    {versionType === "christmas"
                                        ? "ornament"
                                        : "medallion"}{" "}
                                    with your account and proceed to setup.
                                </p>
                            )}
                            {!token && (
                                <>
                                    <p>
                                        Register to connect your{" "}
                                        {versionType === "christmas"
                                            ? "ornament"
                                            : "medallion"}{" "}
                                        with your Living Legacy
                                        {versionType === "christmas"
                                            ? " Christmas page"
                                            : " memorial page"}
                                        .
                                    </p>
                                    <p>
                                        Already have an account? Login to
                                        connect your{" "}
                                        {versionType === "christmas"
                                            ? "ornament"
                                            : "medallion"}
                                    </p>
                                </>
                            )}
                        </div>

                        {token && (
                            <div className="text-center mt-10">
                                <button
                                    disabled={isPending}
                                    onClick={linkUser}
                                    className="w-1/2 border p-2 rounded-full disabled:pointer-events-none border-white/10 hover:bg-white/70 font-medium active:scale-95 hover:text-black/80 transition-all duration-300 ease-in-out"
                                >
                                    {isPending ? <Spinner /> : "Connect"}
                                </button>
                            </div>
                        )}

                        {!token && (
                            <div className="mt-10 flex items-center gap-4 justify-between w-full">
                                <button
                                    onClick={() => {
                                        setOpen(false);
                                        setOpenLoginModal(true);
                                    }}
                                    className="w-1/2 border p-2 rounded-full border-white/10 hover:bg-white/70 font-medium active:scale-95 hover:text-black/80 transition-all duration-300 ease-in-out"
                                >
                                    Login
                                </button>
                                <button
                                    onClick={() => {
                                        setOpen(false);
                                        setOpenRegisterModal(true);
                                    }}
                                    className="w-1/2 border border-transparent font-medium shadow-md hover:shadow-gray-400 p-2 rounded-full bg-white/70 active:scale-95 text-black/80 transition-all duration-300 ease-in-out"
                                >
                                    Register
                                </button>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
};
