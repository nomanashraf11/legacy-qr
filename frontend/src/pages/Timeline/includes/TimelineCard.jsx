import { CiCalendar } from "react-icons/ci";
import { dateMMDDYYYYFormat } from "../../../utils";

export const TimelineCard = ({ timeline, theme }) => {
  return (
    <div className="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
      <div
        className={`${
          theme
            ? "border-white bg-gray-400 text-black"
            : "border-black bg-black text-white"
        } flex items-center justify-center w-10 h-10 rounded-full border  shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2`}
      >
        <CiCalendar />
      </div>
      <div
        className={`${
          theme ? " bg-[#1D1D1F]" : "bg-[#E5E5EA] border border-2"
        } w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)]  p-4 rounded-md relative`}
      >
        <div
          className={
            "flex items-center md:group-even:justify-end title space-x-2 mb-1 md:group-even:flex-row"
          }
        >
          <time
            className={`${
              theme ? "text-white" : "text-black"
            } font-caveat font-medium text-sm`}
          >
            {dateMMDDYYYYFormat(timeline.date)}
          </time>
        </div>
        <div
          className={` ${
            theme ? "text-white" : "text-black"
          } md:group-even:text-end`}
        >
          {timeline.description}
        </div>
      </div>
    </div>
  );
};
