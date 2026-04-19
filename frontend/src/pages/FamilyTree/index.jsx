import { getUserData, useAppSelector } from "../../redux";
import { Fragment, useEffect, useRef } from "react";
import { EmptyData } from "../../components";
import { Tree, TreeNode } from "react-organizational-chart";
import { TransformWrapper, TransformComponent } from "react-zoom-pan-pinch";
import {
    MdCenterFocusStrong,
    MdRestartAlt,
    MdZoomIn,
    MdZoomOut,
} from "react-icons/md";
import { formatRelations, formatRelationLifeSpan } from "../../utils";
import { FamilyTreePersonCard } from "../../components/FamilyTreePersonCard";

/** Deeper zoom-out than scroll-only layout; tune if text becomes too small. */
const FAMILY_TREE_MIN_SCALE = 0.25;
const FAMILY_TREE_MAX_SCALE = 2;

const levelOrder = [
    "PATERNALGREATGRANDMOTHER",
    "PATERNALGREATGRANDFATHER",
    "MATERNALGREATGRANDMOTHER",
    "MATERNALGREATGRANDFATHER",
    "PATERNALGRANDMOTHER",
    "PATERNALGRANDFATHER",
    "MATERNALGRANDMOTHER",
    "MATERNALGRANDFATHER",
    "FATHER",
    "MOTHER",
    "BROTHER",
    "SISTER",
    "SPOUSE",
    "SON",
    "DAUGHTER",
    "GRANDDAUGHTER",
    "GRANDSON",
    "GREATGRANDDAUGHTER",
    "GREATGRANDSON",
    "SONINLAW",
    "DAUGHTERINLAW",
];
export const FamilyTree = () => {
    const relations = useAppSelector(getUserData)?.relations || [];
    const userData = useAppSelector(getUserData);

    const sortedRelations = [...relations].sort((a, b) => {
        const indexA = levelOrder.indexOf((a.name || "").toUpperCase());
        const indexB = levelOrder.indexOf((b.name || "").toUpperCase());

        // Handle cases where the relation is not in the levelOrder array
        return (
            (indexA === -1 ? Infinity : indexA) -
            (indexB === -1 ? Infinity : indexB)
        );
    });
    const hasMother = sortedRelations.some(
        (relation) => relation.name.toUpperCase() === "MOTHER"
    );

    const hasFather = sortedRelations.some(
        (relation) => relation.name.toUpperCase() === "FATHER"
    );

    const hasGrandparentMotherSide = sortedRelations.some(
        (relation) =>
            relation.name.toUpperCase() === "MATERNALGRANDMOTHER" ||
            relation.name.toUpperCase() === "MATERNALGRANDFATHER"
    );

    const hasGrandparentFatherSide = sortedRelations.some(
        (relation) =>
            relation.name.toUpperCase() === "PATERNALGRANDMOTHER" ||
            relation.name.toUpperCase() === "PATERNALGRANDFATHER"
    );

    const hasGreatGrandparentFatherSide = sortedRelations.some(
        (relation) =>
            relation.name.toUpperCase() === "PATERNALGREATGRANDMOTHER" ||
            relation.name.toUpperCase() === "PATERNALGREATGRANDFATHER"
    );

    const hasGreatGrandparentMotherSide = sortedRelations.some(
        (relation) =>
            relation.name.toUpperCase() === "MATERNALGREATGRANDMOTHER" ||
            relation.name.toUpperCase() === "MATERNALGREATGRANDFATHER"
    );

    /** Show each side if the parent exists or any ancestor on that side is present */
    const showPaternalBranch =
        hasFather || hasGrandparentFatherSide || hasGreatGrandparentFatherSide;
    const showMaternalBranch =
        hasMother || hasGrandparentMotherSide || hasGreatGrandparentMotherSide;

    /** Father + mother both shown — horizontal spouse connector between columns */
    const parentsPaired =
        showPaternalBranch && showMaternalBranch && hasFather && hasMother;

    /**
     * When parents are paired they render in `family-tree-parents-row`, not inside
     * `.ft-branch`. Empty branch shells still had border/background (thin “ghost” boxes).
     */
    const paternalBranchHasContent =
        hasGreatGrandparentFatherSide ||
        hasGrandparentFatherSide ||
        (!parentsPaired && hasFather);
    const maternalBranchHasContent =
        hasGreatGrandparentMotherSide ||
        hasGrandparentMotherSide ||
        (!parentsPaired && hasMother);

    const fatherRelation = sortedRelations.find(
        (r) => (r.name || "").toUpperCase() === "FATHER"
    );
    const motherRelation = sortedRelations.find(
        (r) => (r.name || "").toUpperCase() === "MOTHER"
    );

    const treeRef = useRef(null);
    /** Pan/zoom surface — use centerView instead of scrolling the outer div. */
    const transformRef = useRef(null);
    const transformImageUrl = (url) => {
        if (!url) {
            console.log("No image URL provided");
            return null;
        }

        const originalUrl = url.trim();
        console.log("Original URL:", originalUrl);

        if (!originalUrl) {
            console.log("URL is empty after trimming");
            return null;
        }

        if (originalUrl.includes("legacy.livinglegacyqr.com")) {
            console.log(
                "URL already uses legacy domain - no transformation needed"
            );
            return originalUrl;
        }

        if (originalUrl.includes("livinglegacyqr.com")) {
            const transformedUrl = originalUrl.replace(
                /https?:\/\/(www\.)?livinglegacyqr\.com/,
                "https://legacy.livinglegacyqr.com"
            );
            console.log("Transformed URL:", transformedUrl);
            return transformedUrl;
        }

        console.log("No transformation applied - returning original URL");
        return originalUrl;
    };
    // After data or viewport changes, re-center on small screens (replaces old horizontal scrollTo).
    useEffect(() => {
        const centerOnMobile = () => {
            if (window.innerWidth > 768) return;
            const api = transformRef.current;
            if (api?.centerView) {
                api.centerView(1, 200);
            }
        };

        const t = setTimeout(centerOnMobile, 200);
        window.addEventListener("resize", centerOnMobile);

        return () => {
            clearTimeout(t);
            window.removeEventListener("resize", centerOnMobile);
        };
    }, [relations?.length]);
    const zoomBtnClass =
        "inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-zinc-600/60 bg-zinc-800/90 text-zinc-100 shadow-sm transition hover:bg-zinc-700 hover:border-zinc-500 active:scale-95 md:h-10 md:w-10";

    return (
        <div className="family-tree-zoom-viewport ft-viewport relative w-full min-h-[calc(100vh-6rem)] max-w-full overflow-hidden">
            {relations?.length > 0 ? (
                <TransformWrapper
                    ref={transformRef}
                    minScale={FAMILY_TREE_MIN_SCALE}
                    maxScale={FAMILY_TREE_MAX_SCALE}
                    initialScale={1}
                    centerOnInit
                    centerZoomedOut
                    wheel={{ step: 0.12 }}
                    pinch={{ step: 5 }}
                >
                    {(ctrl) => (
                        <>
                            <div
                                className="absolute top-3 right-3 z-10 flex flex-wrap items-center justify-end gap-1 rounded-xl border border-zinc-700/80 bg-zinc-900/75 p-1.5 shadow-lg shadow-black/30 backdrop-blur-md md:gap-1.5"
                                role="toolbar"
                                aria-label="Family tree zoom"
                            >
                                <button
                                    type="button"
                                    className={zoomBtnClass}
                                    aria-label="Zoom out"
                                    onClick={() => ctrl.zoomOut()}
                                >
                                    <MdZoomOut className="h-5 w-5" />
                                </button>
                                <button
                                    type="button"
                                    className={zoomBtnClass}
                                    aria-label="Zoom in"
                                    onClick={() => ctrl.zoomIn()}
                                >
                                    <MdZoomIn className="h-5 w-5" />
                                </button>
                                <button
                                    type="button"
                                    className={zoomBtnClass}
                                    aria-label="Center view"
                                    onClick={() => ctrl.centerView(1, 200)}
                                >
                                    <MdCenterFocusStrong className="h-5 w-5" />
                                </button>
                                <button
                                    type="button"
                                    className={zoomBtnClass}
                                    aria-label="Reset zoom and position"
                                    onClick={() => ctrl.resetTransform(200)}
                                >
                                    <MdRestartAlt className="h-5 w-5" />
                                </button>
                            </div>
                            <TransformComponent
                                wrapperClass="family-tree-zoom-wrapper-inner"
                                contentClass="family-tree-zoom-content"
                            >
                                <div className="family-tree-org">
                                    <Tree
                                        lineHeight="40px"
                                        lineWidth="3px"
                                        lineColor={"rgba(186, 198, 212, 0.95)"}
                                        nodePadding="18px"
                                        lineBorderRadius="14px"
                                        lineStyle="solid"
                                        label={
                                            <div
                                                className={`parent_node max-w-full min-w-0${
                                                    parentsPaired
                                                        ? " parent_node--with-spouse"
                                                        : ""
                                                }`}
                                                ref={treeRef}
                                            >
                                                {showPaternalBranch &&
                                                paternalBranchHasContent ? (
                                                    <div className="paternal ft-branch ft-branch--paternal">
                                                        {hasGreatGrandparentFatherSide ? (
                                                            <div className="family-tree-gp-row">
                                                                {sortedRelations.map(
                                                                    (
                                                                        relation,
                                                                        index
                                                                    ) => {
                                                                        if (
                                                                            relation.name.toUpperCase() ===
                                                                            "paternalGreatGrandfather".toUpperCase()
                                                                        ) {
                                                                            return (
                                                                                <FamilyTreePersonCard
                                                                                    key={
                                                                                        index
                                                                                    }
                                                                                    personName={
                                                                                        relation.person_name
                                                                                    }
                                                                                    imageSrc={
                                                                                        relation.image
                                                                                            ? transformImageUrl(
                                                                                                  relation.image
                                                                                              )
                                                                                            : undefined
                                                                                    }
                                                                                    relationLabel={formatRelations(
                                                                                        relation?.name
                                                                                    )}
                                                                                    lifeSpan={formatRelationLifeSpan(
                                                                                        relation?.dob,
                                                                                        relation?.dod
                                                                                    )}
                                                                                    variant="ancestor"
                                                                                />
                                                                            );
                                                                        }
                                                                        if (
                                                                            relation.name.toUpperCase() ===
                                                                            "paternalGreatGrandmother".toUpperCase()
                                                                        ) {
                                                                            return (
                                                                                <FamilyTreePersonCard
                                                                                    key={
                                                                                        index
                                                                                    }
                                                                                    personName={
                                                                                        relation.person_name
                                                                                    }
                                                                                    imageSrc={
                                                                                        relation.image
                                                                                            ? transformImageUrl(
                                                                                                  relation.image
                                                                                              )
                                                                                            : undefined
                                                                                    }
                                                                                    relationLabel={formatRelations(
                                                                                        relation.name
                                                                                    )}
                                                                                    lifeSpan={formatRelationLifeSpan(
                                                                                        relation?.dob,
                                                                                        relation?.dod
                                                                                    )}
                                                                                    variant="ancestor"
                                                                                />
                                                                            );
                                                                        }
                                                                        return null;
                                                                    }
                                                                )}
                                                            </div>
                                                        ) : null}
                                                        {hasGreatGrandparentFatherSide &&
                                                            hasGrandparentFatherSide && (
                                                                <div
                                                                    className="family-tree-ancestor-connector"
                                                                    aria-hidden
                                                                />
                                                            )}
                                                        {hasGrandparentFatherSide ? (
                                                            <div className="family-tree-gp-row">
                                                                {sortedRelations.map(
                                                                    (
                                                                        relation,
                                                                        index
                                                                    ) => {
                                                                        if (
                                                                            relation.name.toUpperCase() ===
                                                                            "paternalGrandfather".toUpperCase()
                                                                        ) {
                                                                            return (
                                                                                <FamilyTreePersonCard
                                                                                    key={
                                                                                        index
                                                                                    }
                                                                                    personName={
                                                                                        relation.person_name
                                                                                    }
                                                                                    imageSrc={
                                                                                        relation.image
                                                                                            ? transformImageUrl(
                                                                                                  relation.image
                                                                                              )
                                                                                            : undefined
                                                                                    }
                                                                                    relationLabel={formatRelations(
                                                                                        relation?.name
                                                                                    )}
                                                                                    lifeSpan={formatRelationLifeSpan(
                                                                                        relation?.dob,
                                                                                        relation?.dod
                                                                                    )}
                                                                                    variant="ancestor"
                                                                                />
                                                                            );
                                                                        }
                                                                        if (
                                                                            relation.name.toUpperCase() ===
                                                                            "paternalGrandmother".toUpperCase()
                                                                        ) {
                                                                            return (
                                                                                <FamilyTreePersonCard
                                                                                    key={
                                                                                        index
                                                                                    }
                                                                                    personName={
                                                                                        relation.person_name
                                                                                    }
                                                                                    imageSrc={
                                                                                        relation.image
                                                                                            ? transformImageUrl(
                                                                                                  relation.image
                                                                                              )
                                                                                            : undefined
                                                                                    }
                                                                                    relationLabel={formatRelations(
                                                                                        relation.name
                                                                                    )}
                                                                                    lifeSpan={formatRelationLifeSpan(
                                                                                        relation?.dob,
                                                                                        relation?.dod
                                                                                    )}
                                                                                    variant="ancestor"
                                                                                />
                                                                            );
                                                                        }
                                                                        return null;
                                                                    }
                                                                )}
                                                            </div>
                                                        ) : null}
                                                        {sortedRelations.map(
                                                            (
                                                                relation,
                                                                index
                                                            ) => {
                                                                if (
                                                                    relation.name.toUpperCase() ===
                                                                    "father".toUpperCase()
                                                                ) {
                                                                    if (
                                                                        parentsPaired
                                                                    ) {
                                                                        return null;
                                                                    }
                                                                    const fatherNode =
                                                                        (
                                                                            <div className="family-tree-parent-slot">
                                                                                <FamilyTreePersonCard
                                                                                    personName={
                                                                                        relation.person_name
                                                                                    }
                                                                                    imageSrc={
                                                                                        relation.image
                                                                                            ? transformImageUrl(
                                                                                                  relation.image
                                                                                              )
                                                                                            : undefined
                                                                                    }
                                                                                    relationLabel={formatRelations(
                                                                                        relation.name
                                                                                    )}
                                                                                    lifeSpan={formatRelationLifeSpan(
                                                                                        relation?.dob,
                                                                                        relation?.dod
                                                                                    )}
                                                                                    variant="parent"
                                                                                    lineClass={`${
                                                                                        !hasGrandparentFatherSide
                                                                                            ? ""
                                                                                            : "line"
                                                                                    }`.trim()}
                                                                                />
                                                                            </div>
                                                                        );
                                                                    return (
                                                                        <Fragment
                                                                            key={
                                                                                index
                                                                            }
                                                                        >
                                                                            {
                                                                                fatherNode
                                                                            }
                                                                        </Fragment>
                                                                    );
                                                                }
                                                            }
                                                        )}
                                                    </div>
                                                ) : (
                                                    ""
                                                )}

                                                {showMaternalBranch &&
                                                maternalBranchHasContent ? (
                                                    <div className="maternal ft-branch ft-branch--maternal">
                                                        {hasGreatGrandparentMotherSide ? (
                                                            <div className="family-tree-gp-row">
                                                                {sortedRelations.map(
                                                                    (
                                                                        relation,
                                                                        index
                                                                    ) => {
                                                                        if (
                                                                            relation.name.toUpperCase() ===
                                                                            "maternalGreatGrandfather".toUpperCase()
                                                                        ) {
                                                                            return (
                                                                                <FamilyTreePersonCard
                                                                                    key={
                                                                                        index
                                                                                    }
                                                                                    personName={
                                                                                        relation.person_name
                                                                                    }
                                                                                    imageSrc={
                                                                                        relation.image
                                                                                            ? transformImageUrl(
                                                                                                  relation.image
                                                                                              )
                                                                                            : undefined
                                                                                    }
                                                                                    relationLabel={formatRelations(
                                                                                        relation.name
                                                                                    )}
                                                                                    lifeSpan={formatRelationLifeSpan(
                                                                                        relation?.dob,
                                                                                        relation?.dod
                                                                                    )}
                                                                                    variant="ancestor"
                                                                                />
                                                                            );
                                                                        }
                                                                        if (
                                                                            relation.name.toUpperCase() ===
                                                                            "maternalGreatGrandmother".toUpperCase()
                                                                        ) {
                                                                            return (
                                                                                <FamilyTreePersonCard
                                                                                    key={
                                                                                        index
                                                                                    }
                                                                                    personName={
                                                                                        relation.person_name
                                                                                    }
                                                                                    imageSrc={
                                                                                        relation.image
                                                                                            ? transformImageUrl(
                                                                                                  relation.image
                                                                                              )
                                                                                            : undefined
                                                                                    }
                                                                                    relationLabel={formatRelations(
                                                                                        relation.name
                                                                                    )}
                                                                                    lifeSpan={formatRelationLifeSpan(
                                                                                        relation?.dob,
                                                                                        relation?.dod
                                                                                    )}
                                                                                    variant="ancestor"
                                                                                />
                                                                            );
                                                                        }
                                                                        return null;
                                                                    }
                                                                )}
                                                            </div>
                                                        ) : null}
                                                        {hasGreatGrandparentMotherSide &&
                                                            hasGrandparentMotherSide && (
                                                                <div
                                                                    className="family-tree-ancestor-connector"
                                                                    aria-hidden
                                                                />
                                                            )}
                                                        {hasGrandparentMotherSide ? (
                                                            <div className="family-tree-gp-row">
                                                                {sortedRelations.map(
                                                                    (
                                                                        relation,
                                                                        index
                                                                    ) => {
                                                                        if (
                                                                            relation.name.toUpperCase() ===
                                                                            "maternalGrandfather".toUpperCase()
                                                                        ) {
                                                                            return (
                                                                                <FamilyTreePersonCard
                                                                                    key={
                                                                                        index
                                                                                    }
                                                                                    personName={
                                                                                        relation.person_name
                                                                                    }
                                                                                    imageSrc={
                                                                                        relation.image
                                                                                            ? transformImageUrl(
                                                                                                  relation.image
                                                                                              )
                                                                                            : undefined
                                                                                    }
                                                                                    relationLabel={formatRelations(
                                                                                        relation.name
                                                                                    )}
                                                                                    lifeSpan={formatRelationLifeSpan(
                                                                                        relation?.dob,
                                                                                        relation?.dod
                                                                                    )}
                                                                                    variant="ancestor"
                                                                                />
                                                                            );
                                                                        }
                                                                        if (
                                                                            relation.name.toUpperCase() ===
                                                                            "maternalGrandmother".toUpperCase()
                                                                        ) {
                                                                            return (
                                                                                <FamilyTreePersonCard
                                                                                    key={
                                                                                        index
                                                                                    }
                                                                                    personName={
                                                                                        relation.person_name
                                                                                    }
                                                                                    imageSrc={
                                                                                        relation.image
                                                                                            ? transformImageUrl(
                                                                                                  relation.image
                                                                                              )
                                                                                            : undefined
                                                                                    }
                                                                                    relationLabel={formatRelations(
                                                                                        relation.name
                                                                                    )}
                                                                                    lifeSpan={formatRelationLifeSpan(
                                                                                        relation?.dob,
                                                                                        relation?.dod
                                                                                    )}
                                                                                    variant="ancestor"
                                                                                />
                                                                            );
                                                                        }
                                                                        return null;
                                                                    }
                                                                )}
                                                            </div>
                                                        ) : null}
                                                        {sortedRelations.map(
                                                            (
                                                                relation,
                                                                index
                                                            ) => {
                                                                if (
                                                                    relation.name.toUpperCase() ===
                                                                    "mother".toUpperCase()
                                                                ) {
                                                                    if (
                                                                        parentsPaired
                                                                    ) {
                                                                        return null;
                                                                    }
                                                                    const motherNode =
                                                                        (
                                                                            <div className="family-tree-parent-slot">
                                                                                <FamilyTreePersonCard
                                                                                    personName={
                                                                                        relation.person_name
                                                                                    }
                                                                                    imageSrc={
                                                                                        relation.image
                                                                                            ? transformImageUrl(
                                                                                                  relation.image
                                                                                              )
                                                                                            : undefined
                                                                                    }
                                                                                    relationLabel={formatRelations(
                                                                                        relation.name
                                                                                    )}
                                                                                    lifeSpan={formatRelationLifeSpan(
                                                                                        relation?.dob,
                                                                                        relation?.dod
                                                                                    )}
                                                                                    variant="parent"
                                                                                    lineClass={`line ${
                                                                                        !hasGrandparentMotherSide
                                                                                            ? "hide-before"
                                                                                            : ""
                                                                                    }`.trim()}
                                                                                />
                                                                            </div>
                                                                        );
                                                                    return (
                                                                        <Fragment
                                                                            key={
                                                                                index
                                                                            }
                                                                        >
                                                                            {
                                                                                motherNode
                                                                            }
                                                                        </Fragment>
                                                                    );
                                                                }
                                                            }
                                                        )}
                                                    </div>
                                                ) : (
                                                    ""
                                                )}

                                                {parentsPaired &&
                                                    fatherRelation &&
                                                    motherRelation && (
                                                        <div className="family-tree-parents-row">
                                                            <div className="family-tree-parent-anchor family-tree-parent-anchor--father">
                                                                <FamilyTreePersonCard
                                                                    personName={
                                                                        fatherRelation.person_name
                                                                    }
                                                                    imageSrc={
                                                                        fatherRelation.image
                                                                            ? transformImageUrl(
                                                                                  fatherRelation.image
                                                                              )
                                                                            : undefined
                                                                    }
                                                                    relationLabel={formatRelations(
                                                                        fatherRelation.name
                                                                    )}
                                                                    lifeSpan={formatRelationLifeSpan(
                                                                        fatherRelation?.dob,
                                                                        fatherRelation?.dod
                                                                    )}
                                                                    variant="parent"
                                                                    lineClass={`${
                                                                        !hasGrandparentFatherSide
                                                                            ? ""
                                                                            : "line"
                                                                    }`.trim()}
                                                                />
                                                            </div>
                                                            <div
                                                                className="family-tree-spouse-rung"
                                                                aria-hidden
                                                            >
                                                                <div className="family-tree-spouse-rung__line" />
                                                            </div>
                                                            <div className="family-tree-parent-anchor family-tree-parent-anchor--mother">
                                                                <FamilyTreePersonCard
                                                                    personName={
                                                                        motherRelation.person_name
                                                                    }
                                                                    imageSrc={
                                                                        motherRelation.image
                                                                            ? transformImageUrl(
                                                                                  motherRelation.image
                                                                              )
                                                                            : undefined
                                                                    }
                                                                    relationLabel={formatRelations(
                                                                        motherRelation.name
                                                                    )}
                                                                    lifeSpan={formatRelationLifeSpan(
                                                                        motherRelation?.dob,
                                                                        motherRelation?.dod
                                                                    )}
                                                                    variant="parent"
                                                                    lineClass={`line ${
                                                                        !hasGrandparentMotherSide
                                                                            ? "hide-before"
                                                                            : ""
                                                                    }`.trim()}
                                                                />
                                                            </div>
                                                        </div>
                                                    )}
                                            </div>
                                        }
                                    >
                                        <TreeNode>
                                            {relations.map(
                                                (relation, index) => {
                                                    if (
                                                        relation.name.toUpperCase() ===
                                                        "BROTHER"
                                                    ) {
                                                        return (
                                                            <TreeNode
                                                                key={index}
                                                                label={
                                                                    <FamilyTreePersonCard
                                                                        personName={
                                                                            relation.person_name
                                                                        }
                                                                        imageSrc={
                                                                            relation.image
                                                                                ? transformImageUrl(
                                                                                      relation.image
                                                                                  )
                                                                                : undefined
                                                                        }
                                                                        relationLabel={formatRelations(
                                                                            relation.name
                                                                        )}
                                                                        lifeSpan={formatRelationLifeSpan(
                                                                            relation?.dob,
                                                                            relation?.dod
                                                                        )}
                                                                        variant="sibling"
                                                                    />
                                                                }
                                                            />
                                                        );
                                                    }
                                                }
                                            )}

                                            <TreeNode
                                                label={
                                                    <FamilyTreePersonCard
                                                        personName={
                                                            userData?.name
                                                        }
                                                        imageSrc={
                                                            userData?.profile_picture
                                                                ? transformImageUrl(
                                                                      userData.profile_picture
                                                                  )
                                                                : undefined
                                                        }
                                                        lifeSpan={formatRelationLifeSpan(
                                                            userData?.dob,
                                                            userData?.dod,
                                                            "present"
                                                        )}
                                                        variant="self"
                                                    />
                                                }
                                            >
                                                <TreeNode
                                                    label={relations.map(
                                                        (relation, index) => {
                                                            if (
                                                                relation.name.toUpperCase() ===
                                                                "SPOUSE"
                                                            ) {
                                                                return (
                                                                    <FamilyTreePersonCard
                                                                        key={
                                                                            index
                                                                        }
                                                                        personName={
                                                                            relation.person_name
                                                                        }
                                                                        imageSrc={
                                                                            relation.image
                                                                                ? transformImageUrl(
                                                                                      relation.image
                                                                                  )
                                                                                : undefined
                                                                        }
                                                                        relationLabel={formatRelations(
                                                                            relation.name
                                                                        )}
                                                                        lifeSpan={formatRelationLifeSpan(
                                                                            relation?.dob,
                                                                            relation?.dod
                                                                        )}
                                                                        variant="descendant"
                                                                    />
                                                                );
                                                            } else {
                                                                return (
                                                                    <div
                                                                        key={
                                                                            index
                                                                        }
                                                                    />
                                                                );
                                                            }
                                                        }
                                                    )}
                                                >
                                                    {relations.map(
                                                        (relation, index) => {
                                                            if (
                                                                relation.name.toUpperCase() ===
                                                                "SON"
                                                            ) {
                                                                return (
                                                                    <>
                                                                        <TreeNode
                                                                            key={
                                                                                index
                                                                            }
                                                                            label={
                                                                                <FamilyTreePersonCard
                                                                                    personName={
                                                                                        relation.person_name
                                                                                    }
                                                                                    imageSrc={
                                                                                        relation.image
                                                                                            ? transformImageUrl(
                                                                                                  relation.image
                                                                                              )
                                                                                            : undefined
                                                                                    }
                                                                                    relationLabel={formatRelations(
                                                                                        relation.name
                                                                                    )}
                                                                                    lifeSpan={formatRelationLifeSpan(
                                                                                        relation?.dob,
                                                                                        relation?.dod
                                                                                    )}
                                                                                    variant="descendant"
                                                                                />
                                                                            }
                                                                        >
                                                                            <TreeNode
                                                                                key={
                                                                                    index +
                                                                                    2
                                                                                }
                                                                                label={relation.related.map(
                                                                                    (
                                                                                        relation,
                                                                                        index
                                                                                    ) => {
                                                                                        if (
                                                                                            relation.name.toUpperCase() ===
                                                                                            "DAUGHTERINLAW"
                                                                                        ) {
                                                                                            return (
                                                                                                <FamilyTreePersonCard
                                                                                                    key={
                                                                                                        index
                                                                                                    }
                                                                                                    personName={
                                                                                                        relation.person_name
                                                                                                    }
                                                                                                    imageSrc={
                                                                                                        relation.image
                                                                                                            ? transformImageUrl(
                                                                                                                  relation.image
                                                                                                              )
                                                                                                            : undefined
                                                                                                    }
                                                                                                    relationLabel={formatRelations(
                                                                                                        relation.name
                                                                                                    )}
                                                                                                    lifeSpan={formatRelationLifeSpan(
                                                                                                        relation?.dob,
                                                                                                        relation?.dod
                                                                                                    )}
                                                                                                    variant="descendant"
                                                                                                />
                                                                                            );
                                                                                        } else {
                                                                                            return (
                                                                                                <div
                                                                                                    key={
                                                                                                        index
                                                                                                    }
                                                                                                />
                                                                                            );
                                                                                        }
                                                                                    }
                                                                                )}
                                                                            >
                                                                                {relation?.related?.map(
                                                                                    (
                                                                                        child,
                                                                                        childIndex
                                                                                    ) => {
                                                                                        if (
                                                                                            child?.name.toUpperCase() ===
                                                                                                "GRANDSON" ||
                                                                                            child?.name.toUpperCase() ===
                                                                                                "GRANDDAUGHTER"
                                                                                        ) {
                                                                                            return (
                                                                                                <TreeNode
                                                                                                    key={
                                                                                                        childIndex
                                                                                                    }
                                                                                                    label={
                                                                                                        <FamilyTreePersonCard
                                                                                                            personName={
                                                                                                                child?.person_name
                                                                                                            }
                                                                                                            imageSrc={
                                                                                                                child?.image
                                                                                                                    ? transformImageUrl(
                                                                                                                          child.image
                                                                                                                      )
                                                                                                                    : undefined
                                                                                                            }
                                                                                                            relationLabel={formatRelations(
                                                                                                                child?.name
                                                                                                            )}
                                                                                                            lifeSpan={formatRelationLifeSpan(
                                                                                                                child?.dob,
                                                                                                                child?.dod
                                                                                                            )}
                                                                                                            variant="descendant"
                                                                                                        />
                                                                                                    }
                                                                                                >
                                                                                                    {child?.related?.map(
                                                                                                        (
                                                                                                            ggc,
                                                                                                            ggcIndex
                                                                                                        ) => {
                                                                                                            if (
                                                                                                                ggc?.name.toUpperCase() ===
                                                                                                                    "GREATGRANDSON" ||
                                                                                                                ggc?.name.toUpperCase() ===
                                                                                                                    "GREATGRANDDAUGHTER"
                                                                                                            ) {
                                                                                                                return (
                                                                                                                    <TreeNode
                                                                                                                        key={
                                                                                                                            ggcIndex
                                                                                                                        }
                                                                                                                        label={
                                                                                                                            <FamilyTreePersonCard
                                                                                                                                personName={
                                                                                                                                    ggc?.person_name
                                                                                                                                }
                                                                                                                                imageSrc={
                                                                                                                                    ggc?.image
                                                                                                                                        ? transformImageUrl(
                                                                                                                                              ggc.image
                                                                                                                                          )
                                                                                                                                        : undefined
                                                                                                                                }
                                                                                                                                relationLabel={formatRelations(
                                                                                                                                    ggc?.name
                                                                                                                                )}
                                                                                                                                lifeSpan={formatRelationLifeSpan(
                                                                                                                                    ggc?.dob,
                                                                                                                                    ggc?.dod
                                                                                                                                )}
                                                                                                                                variant="descendant"
                                                                                                                            />
                                                                                                                        }
                                                                                                                    />
                                                                                                                );
                                                                                                            }
                                                                                                            return null;
                                                                                                        }
                                                                                                    )}
                                                                                                </TreeNode>
                                                                                            );
                                                                                        }
                                                                                        return null;
                                                                                    }
                                                                                )}
                                                                            </TreeNode>
                                                                        </TreeNode>
                                                                    </>
                                                                );
                                                            }
                                                            if (
                                                                relation?.name?.toUpperCase() ===
                                                                "DAUGHTER"
                                                            ) {
                                                                return (
                                                                    <TreeNode
                                                                        key={
                                                                            index
                                                                        }
                                                                        label={
                                                                            <FamilyTreePersonCard
                                                                                personName={
                                                                                    relation.person_name
                                                                                }
                                                                                imageSrc={
                                                                                    relation.image
                                                                                        ? transformImageUrl(
                                                                                              relation.image
                                                                                          )
                                                                                        : undefined
                                                                                }
                                                                                relationLabel={formatRelations(
                                                                                    relation.name
                                                                                )}
                                                                                lifeSpan={formatRelationLifeSpan(
                                                                                    relation?.dob,
                                                                                    relation?.dod
                                                                                )}
                                                                                variant="descendant"
                                                                            />
                                                                        }
                                                                    >
                                                                        <TreeNode
                                                                            key={
                                                                                index
                                                                            }
                                                                            label={relation.related.map(
                                                                                (
                                                                                    relation,
                                                                                    index
                                                                                ) => {
                                                                                    if (
                                                                                        relation.name.toUpperCase() ===
                                                                                        "SONINLAW"
                                                                                    ) {
                                                                                        return (
                                                                                            <FamilyTreePersonCard
                                                                                                key={
                                                                                                    index
                                                                                                }
                                                                                                personName={
                                                                                                    relation.person_name
                                                                                                }
                                                                                                imageSrc={
                                                                                                    relation.image
                                                                                                        ? transformImageUrl(
                                                                                                              relation.image
                                                                                                          )
                                                                                                        : undefined
                                                                                                }
                                                                                                relationLabel={formatRelations(
                                                                                                    relation.name
                                                                                                )}
                                                                                                lifeSpan={formatRelationLifeSpan(
                                                                                                    relation?.dob,
                                                                                                    relation?.dod
                                                                                                )}
                                                                                                variant="descendant"
                                                                                            />
                                                                                        );
                                                                                    } else {
                                                                                        return (
                                                                                            <div
                                                                                                key={
                                                                                                    index
                                                                                                }
                                                                                            />
                                                                                        );
                                                                                    }
                                                                                }
                                                                            )}
                                                                        >
                                                                            {relation?.related?.map(
                                                                                (
                                                                                    child,
                                                                                    childIndex
                                                                                ) => {
                                                                                    if (
                                                                                        child?.name.toUpperCase() ===
                                                                                            "GRANDSON" ||
                                                                                        child?.name.toUpperCase() ===
                                                                                            "GRANDDAUGHTER"
                                                                                    ) {
                                                                                        return (
                                                                                            <TreeNode
                                                                                                key={
                                                                                                    childIndex
                                                                                                }
                                                                                                label={
                                                                                                    <FamilyTreePersonCard
                                                                                                        personName={
                                                                                                            child?.person_name
                                                                                                        }
                                                                                                        imageSrc={
                                                                                                            child?.image
                                                                                                                ? transformImageUrl(
                                                                                                                      child.image
                                                                                                                  )
                                                                                                                : undefined
                                                                                                        }
                                                                                                        relationLabel={formatRelations(
                                                                                                            child?.name
                                                                                                        )}
                                                                                                        lifeSpan={formatRelationLifeSpan(
                                                                                                            child?.dob,
                                                                                                            child?.dod
                                                                                                        )}
                                                                                                        variant="descendant"
                                                                                                    />
                                                                                                }
                                                                                            >
                                                                                                {child?.related?.map(
                                                                                                    (
                                                                                                        ggc,
                                                                                                        ggcIndex
                                                                                                    ) => {
                                                                                                        if (
                                                                                                            ggc?.name.toUpperCase() ===
                                                                                                                "GREATGRANDSON" ||
                                                                                                            ggc?.name.toUpperCase() ===
                                                                                                                "GREATGRANDDAUGHTER"
                                                                                                        ) {
                                                                                                            return (
                                                                                                                <TreeNode
                                                                                                                    key={
                                                                                                                        ggcIndex
                                                                                                                    }
                                                                                                                    label={
                                                                                                                        <FamilyTreePersonCard
                                                                                                                            personName={
                                                                                                                                ggc?.person_name
                                                                                                                            }
                                                                                                                            imageSrc={
                                                                                                                                ggc?.image
                                                                                                                                    ? transformImageUrl(
                                                                                                                                          ggc.image
                                                                                                                                      )
                                                                                                                                    : undefined
                                                                                                                            }
                                                                                                                            relationLabel={formatRelations(
                                                                                                                                ggc?.name
                                                                                                                            )}
                                                                                                                            lifeSpan={formatRelationLifeSpan(
                                                                                                                                ggc?.dob,
                                                                                                                                ggc?.dod
                                                                                                                            )}
                                                                                                                            variant="descendant"
                                                                                                                        />
                                                                                                                    }
                                                                                                                />
                                                                                                            );
                                                                                                        }
                                                                                                        return null;
                                                                                                    }
                                                                                                )}
                                                                                            </TreeNode>
                                                                                        );
                                                                                    }
                                                                                    return null;
                                                                                }
                                                                            )}
                                                                        </TreeNode>
                                                                    </TreeNode>
                                                                );
                                                            }
                                                        }
                                                    )}
                                                </TreeNode>
                                            </TreeNode>
                                            {relations.map(
                                                (relation, index) => {
                                                    if (
                                                        relation.name.toUpperCase() ===
                                                        "SISTER"
                                                    ) {
                                                        return (
                                                            <TreeNode
                                                                key={index}
                                                                label={
                                                                    <FamilyTreePersonCard
                                                                        personName={
                                                                            relation.person_name
                                                                        }
                                                                        imageSrc={
                                                                            relation.image
                                                                                ? transformImageUrl(
                                                                                      relation.image
                                                                                  )
                                                                                : undefined
                                                                        }
                                                                        relationLabel={formatRelations(
                                                                            relation.name
                                                                        )}
                                                                        lifeSpan={formatRelationLifeSpan(
                                                                            relation?.dob,
                                                                            relation?.dod
                                                                        )}
                                                                        variant="sibling"
                                                                    />
                                                                }
                                                            />
                                                        );
                                                    }
                                                }
                                            )}
                                        </TreeNode>
                                    </Tree>
                                </div>
                            </TransformComponent>
                        </>
                    )}
                </TransformWrapper>
            ) : (
                <EmptyData message={"No family tree added yet."} />
            )}
        </div>
    );
};
