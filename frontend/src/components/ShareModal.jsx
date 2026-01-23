import { IoClose } from "react-icons/io5";
import { useParams } from "react-router-dom";
import { FaXTwitter } from "react-icons/fa6";
import { IoMdShare } from "react-icons/io";
import { toast } from "react-toastify";
import { LIVE_URL } from "../config";
import { Facebook } from "react-social-sharing";

export const ShareModal = ({ open, setOpen, theme }) => {
  const { id } = useParams();
  const url = `${LIVE_URL}${id}/legacy`;

  const copyToClipboard = () => {
    navigator.clipboard
      .writeText(url)
      .then(() => {
        toast.success("Link copied to clipboard");
      })
      .catch((error) => {
        console.error("Failed to copy:", error);
        toast.error("Failed to copy link to clipboard");
      });
  };
  return (
    <div
      className={
        `backdrop-filter backdrop-blur-sm bg-opacity-60 overflow-x-hidden fixed top-0 right-0 left-0 bottom-0 max-h-full !z-50 flex justify-center items-center w-full md:inset-0 transition-all duration-200 ease-in-out ` +
        (open ? "block" : "hidden")
      }
    >
      <div className="relative p-4 w-full max-w-[400px] max-h-full">
        <div
          className={`${
            theme ? "text-white bg-black " : "!text-black bg-[#D3D3D3]"
          } relative rounded-lg shadow`}
        >
          <div className="flex items-center justify-center p-4 border-b relative border-white/5 rounded-t">
            <h3 className="text-xl font-semibold text-center w-full">Share</h3>
            <div className="absolute top-0 right-0 left-0 bottom-0 flex items-center justify-end p-4">
              <button
                onClick={() => setOpen(false)}
                type="button"
                className={`${
                  theme ? "text-black bg-white " : "!text-black bg-black"
                }  transition-all duration-200 ease-in-out bg-opacity-20 hover:bg-opacity-25 active:bg-opacity-30 rounded-full text-sm w-8 h-8 ms-auto inline-flex justify-center items-center`}
              >
                <IoClose size={24} />
              </button>
            </div>
          </div>
          <div className="max-h-[400px] p-8 space-y-4 border-white/10">
            <Facebook url={url}>Facebook</Facebook>
            <a
              target="_blank"
              href={`https://twitter.com/intent/tweet?url=${url}`}
              title="Share to X"
              className={`text-white hover:bg-[#222222] border-white/10 w-full relative border cursor-pointer select-none flex items-center text-center gap-4 py-2 rounded-full   font-medium active:scale-95 transition-all duration-300 ease-in-out`}
              rel="noreferrer"
            >
              <div className="w-1/3 flex items-center justify-end mr-1.5">
                <FaXTwitter />
              </div>
              X
            </a>
            <button
              title="Copy Link"
              onClick={copyToClipboard}
              className={`text-white hover:bg-[#222222] border-white/10  w-full relative disabled:pointer-events-none disabled:opacity-45 border cursor-pointer select-none flex items-center text-center gap-4 py-2 rounded-full  font-medium active:scale-95 transition-all duration-300 ease-in-out`}
            >
              <div className="w-1/3 flex items-center justify-end mr-1.5">
                <IoMdShare />
              </div>
              Copy
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};
