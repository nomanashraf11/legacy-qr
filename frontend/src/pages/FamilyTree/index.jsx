import { getUserData, useAppSelector } from "../../redux";
import { useEffect, useRef } from "react";
import { EmptyData } from "../../components";
import { Tree, TreeNode } from "react-organizational-chart";
import { formatRelations, dateMMDDYYYYFormat } from "../../utils";

const levelOrder = [
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
  "SONINLAW",
  "DAUGHTERINLAW",
];
export const FamilyTree = () => {
  const relations = useAppSelector(getUserData)?.relations || [];
  const userData = useAppSelector(getUserData);

  const sortedRelations = [...relations].sort((a, b) => {
    const indexA = levelOrder.indexOf(a.name);
    const indexB = levelOrder.indexOf(b.name);

    // Handle cases where the relation is not in the levelOrder array
    return (
      (indexA === -1 ? Infinity : indexA) - (indexB === -1 ? Infinity : indexB)
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
  const treeRef = useRef(null);
  const treeContainerRef = useRef(null);
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
      console.log("URL already uses legacy domain - no transformation needed");
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
  useEffect(() => {
    const centerTree = () => {
      if (
        window.innerWidth <= 768 &&
        treeContainerRef.current &&
        treeRef.current
      ) {
        const container = treeContainerRef.current;
        const tree = treeRef.current;

        // Calculate the center position
        const scrollPosition =
          tree.offsetLeft + tree.offsetWidth / 2 - container.clientWidth / 2;

        // Apply smooth scrolling
        container.scrollTo({
          left: scrollPosition,
          behavior: "smooth",
        });
      }
    };

    // Run on mount and resize
    centerTree();
    window.addEventListener("resize", centerTree);

    return () => window.removeEventListener("resize", centerTree);
  }, []);
  return (
    <div
      ref={treeContainerRef}
      className="min-h-screen w-full overflow-auto whitespace-nowrap"
    >
      {relations?.length > 0 ? (
        <>
          <Tree
            lineColor={"#626262"}
            lineBorderRadius={"12px"}
            label={
              <div className={`parent_node overflow-auto `} ref={treeRef}>
                {hasFather ? (
                  <div className="paternal ">
                    <div>
                      {!hasGrandparentFatherSide ? (
                        <div className="node" style={{ height: "112px" }}></div>
                      ) : (
                        ""
                      )}
                      {sortedRelations.map((relation, index) => {
                        if (
                          relation.name.toUpperCase() ===
                          "paternalGrandfather".toUpperCase()
                        ) {
                          return (
                            <div key={index} className={`node`}>
                              {relation.image && (
                                <img src={transformImageUrl(relation.image)} />
                              )}
                              <p className="">{relation.person_name}</p>
                              <span>
                                {formatRelations(relation?.name)}
                              </span>{" "}
                              <span>
                                {dateMMDDYYYYFormat(relation?.dob)} -{" "}
                                {relation?.dod
                                  ? dateMMDDYYYYFormat(relation?.dod)
                                  : "Present"}
                              </span>
                            </div>
                          );
                        }
                        if (
                          relation.name.toUpperCase() ===
                          "paternalGrandmother".toUpperCase()
                        ) {
                          return (
                            <div key={index} className={"node "}>
                              {relation.image && (
                                <img src={transformImageUrl(relation.image)} />
                              )}
                              <p>{relation.person_name}</p>
                              <span>{formatRelations(relation.name)}</span>{" "}
                              <span>
                                {dateMMDDYYYYFormat(relation?.dob)} -{" "}
                                {relation?.dod
                                  ? dateMMDDYYYYFormat(relation?.dod)
                                  : "Present"}
                              </span>
                            </div>
                          );
                        }
                      })}
                    </div>
                    {sortedRelations.map((relation, index) => {
                      if (
                        relation.name.toUpperCase() === "father".toUpperCase()
                      ) {
                        return (
                          <div
                            key={index}
                            className={`node  ${
                              !hasGrandparentFatherSide ? "" : "line"
                            } `}
                          >
                            {relation.image && (
                              <img src={transformImageUrl(relation.image)} />
                            )}
                            <p className="">{relation.person_name}</p>
                            <span>{formatRelations(relation.name)}</span>{" "}
                            <span>
                              {dateMMDDYYYYFormat(relation?.dob)} -{" "}
                              {relation?.dod
                                ? dateMMDDYYYYFormat(relation?.dod)
                                : "Present"}
                            </span>
                          </div>
                        );
                      }
                    })}
                  </div>
                ) : (
                  ""
                )}

                {hasMother ? (
                  <div className="paternal ">
                    <div>
                      {!hasGrandparentMotherSide ? (
                        <div className="node" style={{ height: "112px" }}></div>
                      ) : (
                        ""
                      )}
                      {sortedRelations.map((relation, index) => {
                        if (
                          relation.name.toUpperCase() ===
                          "maternalGrandfather".toUpperCase()
                        ) {
                          return (
                            <div key={index} className={`node `}>
                              {relation.image && (
                                <img src={transformImageUrl(relation.image)} />
                              )}
                              <p className="">{relation.person_name}</p>
                              <span>{formatRelations(relation.name)}</span>{" "}
                              <span>
                                {dateMMDDYYYYFormat(relation?.dob)} -{" "}
                                {relation?.dod
                                  ? dateMMDDYYYYFormat(relation?.dod)
                                  : "Present"}
                              </span>
                            </div>
                          );
                        }
                        if (
                          relation.name.toUpperCase() ===
                          "maternalGrandmother".toUpperCase()
                        ) {
                          return (
                            <div key={index} className={"node "}>
                              {relation.image && (
                                <img src={transformImageUrl(relation.image)} />
                              )}
                              <p>{relation.person_name}</p>
                              <span>{formatRelations(relation.name)}</span>{" "}
                              <span>
                                {dateMMDDYYYYFormat(relation?.dob)} -{" "}
                                {relation?.dod
                                  ? dateMMDDYYYYFormat(relation?.dod)
                                  : "Present"}
                              </span>
                            </div>
                          );
                        }
                      })}
                    </div>
                    {sortedRelations.map((relation, index) => {
                      if (
                        relation.name.toUpperCase() === "mother".toUpperCase()
                      ) {
                        return (
                          <div
                            key={index}
                            className={`node line ${
                              !hasGrandparentMotherSide ? "hide-before" : ""
                            }`}
                          >
                            {relation.image && (
                              <img src={transformImageUrl(relation.image)} />
                            )}
                            <p className="">{relation.person_name}</p>
                            <span>{formatRelations(relation.name)}</span>{" "}
                            <span>
                              {dateMMDDYYYYFormat(relation?.dob)} -{" "}
                              {relation?.dod
                                ? dateMMDDYYYYFormat(relation?.dod)
                                : "Present"}
                            </span>
                          </div>
                        );
                      }
                    })}
                  </div>
                ) : (
                  ""
                )}
              </div>
            }
          >
            <TreeNode>
              {relations.map((relation, index) => {
                if (relation.name.toUpperCase() === "BROTHER") {
                  return (
                    <TreeNode
                      key={12312}
                      label={
                        <div key={index} className={"node sibling-node"}>
                          {relation.image && (
                            <img src={transformImageUrl(relation.image)} />
                          )}
                          <p>{relation.person_name}</p>
                          <span>{formatRelations(relation.name)}</span>{" "}
                          <span>
                            {dateMMDDYYYYFormat(relation?.dob)} -{" "}
                            {relation?.dod
                              ? dateMMDDYYYYFormat(relation?.dod)
                              : "Present"}
                          </span>
                        </div>
                      }
                    />
                  );
                }
              })}

              <TreeNode
                label={
                  <div className={"node self-node"}>
                    {userData?.profile_picture && (
                      <img src={transformImageUrl(userData?.profile_picture)} />
                    )}
                    <p>{userData?.name}</p>
                    <span>
                      {dateMMDDYYYYFormat(userData?.dob)} -{" "}
                      {userData?.dod
                        ? dateMMDDYYYYFormat(userData?.dod)
                        : "present"}
                    </span>
                  </div>
                }
              >
                <TreeNode
                  label={relations.map((relation, index) => {
                    if (relation.name.toUpperCase() === "SPOUSE") {
                      return (
                        <div key={index} className={"node"}>
                          {relation.image && (
                            <img src={transformImageUrl(relation.image)} />
                          )}
                          <p>{relation.person_name}</p>
                          <span>{formatRelations(relation.name)}</span>
                          <span>
                            {dateMMDDYYYYFormat(relation?.dob)} -{" "}
                            {relation?.dod
                              ? dateMMDDYYYYFormat(relation?.dod)
                              : "Present"}
                          </span>
                        </div>
                      );
                    } else {
                      return <div key={index} />;
                    }
                  })}
                >
                  {relations.map((relation, index) => {
                    if (relation.name.toUpperCase() === "SON") {
                      return (
                        <>
                          <TreeNode
                            key={index}
                            label={
                              <div className={"node"}>
                                {relation.image && (
                                  <img
                                    src={transformImageUrl(relation.image)}
                                  />
                                )}
                                <p>{relation.person_name}</p>
                                <span>{formatRelations(relation.name)}</span>
                                <span>
                                  {dateMMDDYYYYFormat(relation?.dob)} -{" "}
                                  {relation?.dod
                                    ? dateMMDDYYYYFormat(relation?.dod)
                                    : "Present"}
                                </span>
                              </div>
                            }
                          >
                            <TreeNode
                              key={index + 2}
                              label={relation.related.map((relation, index) => {
                                if (
                                  relation.name.toUpperCase() ===
                                  "DAUGHTERINLAW"
                                ) {
                                  return (
                                    <div key={index} className={"node"}>
                                      {relation.image && (
                                        <img
                                          src={transformImageUrl(
                                            relation.image
                                          )}
                                        />
                                      )}
                                      <p>{relation.person_name}</p>
                                      <span>
                                        {formatRelations(relation.name)}
                                      </span>
                                      <span>
                                        {dateMMDDYYYYFormat(relation?.dob)} -{" "}
                                        {relation?.dod
                                          ? dateMMDDYYYYFormat(relation?.dod)
                                          : "Present"}
                                      </span>
                                    </div>
                                  );
                                } else {
                                  return <div key={index} />;
                                }
                              })}
                            >
                              {relation?.related?.map((child, childIndex) => {
                                if (
                                  child?.name.toUpperCase() === "GRANDSON" ||
                                  child?.name.toUpperCase() === "GRANDDAUGHTER"
                                ) {
                                  return (
                                    <TreeNode
                                      key={childIndex}
                                      label={
                                        <div className={"node"}>
                                          {child?.image && (
                                            <img
                                              src={transformImageUrl(
                                                child?.image
                                              )}
                                            />
                                          )}
                                          <p>{child?.person_name}</p>
                                          <span>
                                            {formatRelations(child?.name)}
                                          </span>
                                          <span>
                                            {dateMMDDYYYYFormat(child?.dob)} -{" "}
                                            {child?.dod
                                              ? dateMMDDYYYYFormat(child?.dod)
                                              : "Present"}
                                          </span>
                                        </div>
                                      }
                                    />
                                  );
                                }
                              })}
                            </TreeNode>
                          </TreeNode>
                        </>
                      );
                    }
                    if (relation?.name?.toUpperCase() === "DAUGHTER") {
                      return (
                        <TreeNode
                          key={index}
                          label={
                            <div className={"node"}>
                              {relation.image && (
                                <img src={transformImageUrl(relation.image)} />
                              )}
                              <p>{relation.person_name}</p>
                              <span>{formatRelations(relation.name)}</span>{" "}
                              <span>
                                {dateMMDDYYYYFormat(relation?.dob)} -{" "}
                                {relation?.dod
                                  ? dateMMDDYYYYFormat(relation?.dod)
                                  : "Present"}
                              </span>
                            </div>
                          }
                        >
                          <TreeNode
                            key={index}
                            label={relation.related.map((relation, index) => {
                              if (relation.name.toUpperCase() === "SONINLAW") {
                                return (
                                  <div key={index} className={"node"}>
                                    {relation.image && (
                                      <img
                                        src={transformImageUrl(relation.image)}
                                      />
                                    )}
                                    <p>{relation.person_name}</p>
                                    <span>
                                      {formatRelations(relation.name)}
                                    </span>
                                    <span>
                                      {dateMMDDYYYYFormat(relation?.dob)} -{" "}
                                      {relation?.dod
                                        ? dateMMDDYYYYFormat(relation?.dod)
                                        : "Present"}
                                    </span>
                                  </div>
                                );
                              } else {
                                return <div key={index} />;
                              }
                            })}
                          >
                            {relation?.related?.map((child, childIndex) => {
                              if (
                                child?.name.toUpperCase() === "GRANDSON" ||
                                child?.name.toUpperCase() === "GRANDDAUGHTER"
                              ) {
                                return (
                                  <TreeNode
                                    key={childIndex}
                                    label={
                                      <div className={"node"}>
                                        {child?.image && (
                                          <img
                                            src={transformImageUrl(
                                              child?.image
                                            )}
                                          />
                                        )}
                                        <p>{child?.person_name}</p>
                                        <span>
                                          {formatRelations(child?.name)}
                                        </span>
                                        <span>
                                          {dateMMDDYYYYFormat(child?.dob)} -{" "}
                                          {child?.dod
                                            ? dateMMDDYYYYFormat(child?.dod)
                                            : "Present"}
                                        </span>
                                      </div>
                                    }
                                  />
                                );
                              }
                            })}
                          </TreeNode>
                        </TreeNode>
                      );
                    }
                  })}
                </TreeNode>
              </TreeNode>
              {relations.map((relation, index) => {
                if (relation.name.toUpperCase() === "SISTER") {
                  return (
                    <TreeNode
                      key={index}
                      label={
                        <div className={"node sibling-node"}>
                          {relation.image && (
                            <img src={transformImageUrl(relation.image)} />
                          )}
                          <p>{relation.person_name}</p>
                          <span>{formatRelations(relation.name)}</span>{" "}
                          <span>
                            {dateMMDDYYYYFormat(relation?.dob)} -{" "}
                            {relation?.dod
                              ? dateMMDDYYYYFormat(relation?.dod)
                              : "Present"}
                          </span>
                        </div>
                      }
                    />
                  );
                }
              })}
            </TreeNode>
          </Tree>
        </>
      ) : (
        <EmptyData message={"No family tree added yet."} />
      )}
    </div>
  );
};
