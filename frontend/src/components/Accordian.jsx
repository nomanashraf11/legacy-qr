import { useState } from "react";
import { IoIosArrowDown } from "react-icons/io";

export const Accordian = ({ title, children }) => {
  const [isActive, setActive] = useState(true);

  const toggleAccordion = () => {
    setActive((flag) => !flag);
  };
  return (
    <div className={"border border-neutral-700 rounded-lg overflow-hidden"}>
      <button
        type="button"
        className={
          "flex w-full hover:bg-neutral-500 border-neutral-700 justify-between items-center p-4 " +
          (isActive ? "border-b" : "")
        }
        onClick={toggleAccordion}
      >
        <span className="sm:text-lg text-start font-semibold">{title}</span>
        <IoIosArrowDown
          className={
            "sm:min-w-6 sm:min-h-6 min-w-5 min-h-5 transition-all duration-300 ease-in-out " +
            (isActive ? "transform rotate-180" : "")
          }
        />
      </button>
      <div
        className={
          "transition-all duration-700 ease-in-out overflow-hidden " +
          (isActive ? "h-auto animate-fadeIn" : "h-0")
        }
      >
        <div className="p-4">{children}</div>
      </div>
    </div>
  );
};
