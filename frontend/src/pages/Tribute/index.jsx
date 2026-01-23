import React, { useMemo } from "react";
import { TributeCard } from "./includes/TributeCard";
import { EmptyData } from "../../components";
import { useParams } from "react-router-dom";
import { useFetchQrCodesData, useFetchTributeData } from "../../services";

export const Tribute = () => {
  const { id } = useParams();
  const { data: tribute, refetch } = useFetchTributeData(id);
  const { data: qrCodes } = useFetchQrCodesData();

  const isAdmin = () => {
    const ids = qrCodes?.data?.map((item) => item.uuid);
    return ids?.includes(id);
  };

  const data = useMemo(() => {
    return tribute?.Details?.Tributes
      ? [...tribute?.Details?.Tributes].reverse()
      : [];
  }, [tribute?.Details?.Tributes]);
  return (
    <div className="mx-auto">
      {data?.length > 0 ? (
        data?.map((item, index) => (
          <TributeCard
            theme={tribute?.Details?.dark_theme}
            isAdmin={isAdmin()}
            refetch={refetch}
            key={index}
            tribute={item}
          />
        ))
      ) : (
        <EmptyData message={"No tributes added yet."} />
      )}
    </div>
  );
};
