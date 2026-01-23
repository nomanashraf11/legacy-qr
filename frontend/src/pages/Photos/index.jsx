import Masonry, { ResponsiveMasonry } from "react-responsive-masonry";
import { getUserData, useAppSelector } from "../../redux";
import { EmptyData } from "../../components";
import { Card } from "./includes/Card";

export const Photos = () => {
  const data = useAppSelector(getUserData)?.Photos;
  return data?.length > 0 ? (
    <ResponsiveMasonry columnsCountBreakPoints={{ 300: 2, 900: 3 }}>
      <Masonry gutter="1rem">
        {data?.map((item, i) => (
          <Card item={item} key={i} />
        ))}
      </Masonry>
    </ResponsiveMasonry>
  ) : (
    <EmptyData message={"There are no photos/videos uploaded yet."} />
  );
};
