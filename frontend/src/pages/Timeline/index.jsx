import { TimelineCard } from "./includes/TimelineCard";
import { getUserData, useAppSelector } from "../../redux";
import { EmptyData } from "../../components";

export const Timeline = () => {
  const data = useAppSelector(getUserData)
    ?.Timelines?.slice()
    .sort((a, b) => new Date(a.date) - new Date(b.date));
  const theme = useAppSelector(getUserData)?.dark_theme;

  return data?.length > 0 ? (
    <div className="space-y-8 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-slate-300 before:to-transparent">
      {data?.map((item, number) => (
        <TimelineCard timeline={item} key={number} theme={theme} />
      ))}
    </div>
  ) : (
    <EmptyData message={"There is no timeline data available."} />
  );
};
