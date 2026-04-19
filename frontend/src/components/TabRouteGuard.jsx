import { Navigate, useParams } from "react-router-dom";
import { useFetchTributeData } from "../services";
import { mergeTabVisibility } from "../constants/tabVisibility";

/**
 * Redirects to Legacy if the current tab is turned off in settings.
 */
export function TabRouteGuard({ tabKey, children }) {
    const { id } = useParams();
    const { data, isLoading } = useFetchTributeData(id);

    if (isLoading && !data) {
        return null;
    }

    const merged = mergeTabVisibility(data?.Details?.tab_visibility);
    if (merged[tabKey] === false) {
        return <Navigate to={`/${id}/legacy`} replace />;
    }

    return children;
}
