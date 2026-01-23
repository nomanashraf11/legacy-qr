import ReactPlayer from "react-player";
import { checkFileType } from "../../../utils";
import Lightbox from "yet-another-react-lightbox";
import "yet-another-react-lightbox/styles.css";
import { useState } from "react";

export const Card = ({ item }) => {
    const [open, setOpen] = useState(false);

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

    return (
        <div>
            <div className="rounded-lg overflow-hidden">
                {item.link ? (
                    <ReactPlayer
                        width={"100%"}
                        playsinline
                        height={"100%"}
                        loop
                        controls={true}
                        muted
                        url={getCleanYouTubeUrl(item.link)}
                    />
                ) : checkFileType(item.image) === "image" ? (
                    <>
                        <Lightbox
                            render={{
                                buttonPrev: () => null,
                                buttonNext: () => null,
                            }}
                            carousel={{ finite: true }}
                            open={open}
                            close={() => setOpen(false)}
                            slides={[{ src: item.image || "" }]}
                        />
                        <img
                            onClick={() => setOpen((flag) => !flag)}
                            className="w-full h-full cursor-pointer rounded-lg block"
                            src={item.image}
                        />
                    </>
                ) : (
                    <ReactPlayer
                        width={"100%"}
                        playsinline
                        height={"100%"}
                        loop
                        controls={true}
                        muted
                        url={item.image}
                    />
                )}
            </div>
            <span>{item.caption}</span>
        </div>
    );
};
