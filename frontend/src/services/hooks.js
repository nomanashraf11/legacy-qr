import { useQuery } from "@tanstack/react-query";
import { getMyQrCodes, getTributesData } from ".";

export const useFetchTributeData = (id) => {
  return useQuery({
    queryKey: ["tribute" + id],
    queryFn: () => getTributesData(id),
    select: (data) => data.data,
    enabled: !!id,
    refetchOnWindowFocus: false,
  });
};

export const useFetchQrCodesData = () => {
  return useQuery({
    queryKey: ["qr-codes"],
    queryFn: () => getMyQrCodes(),
    select: (data) => data.data,
    enabled: false,
  });
};
