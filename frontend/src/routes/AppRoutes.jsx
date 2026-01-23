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
                <Route path="/:id/gallery" element={<Photos />} />
                <Route path="/:id/timeline" element={<Timeline />} />
                <Route path="/:id/tribute" element={<Tribute />} />
                <Route path="/:id/family-tree" element={<FamilyTree />} />
                <Route path="/:id/settings" element={<Settings />} />
            </Route>
            <Route path="/*" element={<NotFound />} />
        </Routes>
    );
};
