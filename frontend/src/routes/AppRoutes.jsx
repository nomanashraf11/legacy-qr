import { Navigate, Route, Routes } from "react-router-dom";
import {
    Bio,
    Blank,
    Christmas,
    ChristmasDemo,
    ChristmasTest,
    EmailVerification,
    FamilyTree,
    Link,
    NotFound,
    Photos,
    QrCodes,
    Settings,
    Timeline,
    Tribute,
} from "../pages";
import { AppLayout } from "../Layout";
import { TabRouteGuard } from "../components/TabRouteGuard";

export const AppRoutes = () => {
    return (
        <Routes>
            <Route path="/" element={<Navigate replace to="/my-qrcodes" />} />
            <Route path="/:id" element={<Blank />} />
            <Route path="/my-qrcodes" element={<QrCodes />} />
            <Route path="/verify-email" element={<EmailVerification />} />
            {/* Christmas Demo Route - For previewing functionality */}
            <Route path="/christmas-demo" element={<ChristmasDemo />} />
            {/* Christmas Version Route - Standalone page */}
            <Route path="/:id/christmas" element={<Christmas />} />
            {/* Christmas Test Route - For testing functionality */}
            <Route path="/:id/christmas-test" element={<ChristmasTest />} />
            {/* Link Route - For QR codes that need linking */}
            <Route path="/:id/link" element={<Link />} />
            {/* Full Version Routes - With AppLayout */}
            <Route element={<AppLayout />}>
                <Route index path="/:id/legacy" element={<Bio />} />
                <Route
                    path="/:id/gallery"
                    element={
                        <TabRouteGuard tabKey="gallery">
                            <Photos />
                        </TabRouteGuard>
                    }
                />
                <Route
                    path="/:id/timeline"
                    element={
                        <TabRouteGuard tabKey="timeline">
                            <Timeline />
                        </TabRouteGuard>
                    }
                />
                <Route
                    path="/:id/tribute"
                    element={
                        <TabRouteGuard tabKey="tribute">
                            <Tribute />
                        </TabRouteGuard>
                    }
                />
                <Route
                    path="/:id/family-tree"
                    element={
                        <TabRouteGuard tabKey="family_tree">
                            <FamilyTree />
                        </TabRouteGuard>
                    }
                />
                <Route path="/:id/settings" element={<Settings />} />
            </Route>
            <Route path="/*" element={<NotFound />} />
        </Routes>
    );
};
