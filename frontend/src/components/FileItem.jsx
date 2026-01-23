import { useMutation } from "@tanstack/react-query";
import { useEffect, useState } from "react";
import ReactPlayer from "react-player";
import { toast } from "react-toastify";
import { deletePhotoAsync, updatePhotoVideoCaptionAsync } from "../services";
import { checkFileType } from "../utils";
import { IoClose } from "react-icons/io5";

export const FileItem = ({ item, refetch }) => {
    const [caption, setCaption] = useState("");

    // Clean YouTube URL to extract just the video ID
    const getCleanYouTubeUrl = (url) => {
        if (!url) return url;

        // Extract video ID from various YouTube URL formats
        const videoIdMatch = url.match(
            /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\n?#]+)/
        );
        if (videoIdMatch) {
            return `https://www.youtube.com/watch?v=${videoIdMatch[1]}`;
        }

        return url;
    };

    const { mutateAsync, isPending } = useMutation({
        mutationFn: deletePhotoAsync,
    });
    const { mutateAsync: mutate, isPending: isLoading } = useMutation({
        mutationFn: (payload) =>
            updatePhotoVideoCaptionAsync(item.uuid, payload),
    });

    const handleSubmit = async () => {
        try {
            const payload = { caption };
            const { data } = await mutate(payload);
            if (data.status === 200) {
                toast.success(data.message);
                refetch();
            } else {
                toast.error(data.message);
            }
        } catch (error) {
            console.log(error);
        }
    };

    const removePhoto = async () => {
        try {
            const { data } = await mutateAsync(item.uuid);
            if (data.status === 200) {
                toast.success(data.message);
                refetch();
            } else {
                toast.error(data.message);
            }
        } catch (error) {
            console.log(error);
        }
    };

    useEffect(() => {
        if (item.caption) {
            setCaption(item.caption);
        }
    }, [item]);
    return (
        <div className="relative w-full">
            <div className="h-24 max-h-24 rounded-lg overflow-hidden [&_video]:object-cover">
                {item.link ? (
                    <ReactPlayer
                        width={"100%"}
                        playsinline
                        height={"100%"}
                        loop
                        muted
                        url={getCleanYouTubeUrl(item.link)}
                    />
                ) : checkFileType(item.image) === "image" ? (
                    <img
                        className="w-full h-full object-cover"
                        src={item.image}
                    />
                ) : (
                    <ReactPlayer
                        width={"100%"}
                        playsinline
                        height={"100%"}
                        loop
                        muted
                        url={item.image}
                    />
                )}
            </div>

            <div className="mt-2 space-y-2">
                <input
                    className="border outline-none text-xs rounded-md block w-full px-2.5 py-1.5 bg-[#333333] border-white/20 placeholder-gray-400 text-white"
                    type="text"
                    placeholder="Enter caption here..."
                    value={caption}
                    onChange={(e) => setCaption(e.target.value)}
                />

                <button
                    onClick={handleSubmit}
                    type="button"
                    disabled={!caption || isLoading}
                    className="text-white select-none border border-white/10 active:scale-95 disabled:pointer-events-none disabled:opacity-50 transition-all duration-300 ease-in-out hover:bg-gray-400 hover:text-black font-medium rounded-full text-sm w-full py-1.5 text-center flex items-center justify-center gap-2"
                >
                    {isLoading ? "Loading..." : "Update"}
                </button>
            </div>

            <button
                disabled={isPending}
                onClick={removePhoto}
                type="button"
                className={
                    "absolute top-0.5 disabled:pointer-events-none disabled:opacity-50 right-0.5 text-gray-400 transition-all duration-200 ease-in-out bg-[#242526] hover:bg-opacity-90 active:bg-[#242526] rounded-full text-sm  inline-flex justify-center items-center w-5 h-5 "
                }
            >
                {isPending ? (
                    <svg
                        aria-hidden="true"
                        className="w-4 h-4 text-gray-200 animate-spin dark:text-gray-600 fill-blue-600"
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
                    <IoClose />
                )}
            </button>
        </div>
    );
};
