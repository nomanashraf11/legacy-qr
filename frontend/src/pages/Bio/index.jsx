import { StaticMap } from "../../components/StaticMap";
import { FaDirections } from "react-icons/fa";
import { getOrbitStatus, getUserData, useAppSelector } from "../../redux";
import { EmptyData } from "../../components";
import Linkify from "react-linkify";

export const Bio = () => {
  const spouseOrbt = useAppSelector(getOrbitStatus);
  const userData = useAppSelector(getUserData);

  return userData ? (
    <div className="grid md:grid-cols-2 ">
      {spouseOrbt === "main" ? (
        <Linkify>
          <pre
            className={`${
              userData.dark_theme ? " bg-[#1D1D1F]" : "bg-[#E5E5EA]"
            } overflow-hidden text-base whitespace-pre-line font-roboto w-full text-wrap p-3 rounded`}
          >
            {userData?.bio ? userData?.bio : "There is no obituary for spouse"}
          </pre>
        </Linkify>
      ) : (
        <Linkify>
          <pre
            className={`${
              userData.dark_theme ? " bg-[#1D1D1F]" : "bg-[#E5E5EA]"
            }  overflow-hidden text-base whitespace-pre-line font-roboto w-full text-wrap ml-2 p-3 rounded`}
          >
            {userData?.relations?.find(
              (value) => value.name.toLowerCase() === "spouse"
            )?.bio
              ? userData?.relations?.find(
                  (value) => value.name.toLowerCase() === "spouse"
                )?.bio
              : "There is no obituary for spouse"}
          </pre>
        </Linkify>
      )}
      <div className="md:pl-14 lg:pl-24 flex flex-col gap-10 mt-10 md:m-0 relative">
        <div className="flex flex-col gap-4 relative">
          {userData?.latitude && userData?.longitude && (
            <>
              <div className="relative">
                <StaticMap />
              </div>
              <a
                rel="noreferrer"
                target="_blank"
                className="flex items-center justify-center gap-2 hover:bg-[#222222] active:bg-[#121212] rounded p-2 bg-[#333333] transition-all duration-200 ease-in-out"
                href={`https://www.google.com/maps?q=${userData?.latitude},${userData?.longitude}`}
              >
                <FaDirections color="#1A73E8" />
                Get Directions
              </a>
            </>
          )}
        </div>
      </div>
    </div>
  ) : (
    <EmptyData message={"No bio added yet."} />
  );
};
