import { BASE_URL } from "../config";

export const Footer = () => {
  return (
    <div className="flex select-none flex-col pb-3 items-center justify-center gap-4 bg-black border-t border-t-white/10">
      <a target="_blank" href={BASE_URL}>
        <img className="h-20 w-40 object-cover" src="/logo-dark.png" />
      </a>
    </div>
  );
};
