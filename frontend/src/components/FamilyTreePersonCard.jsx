/**
 * Person tile for the family tree — layout only; relation logic stays in FamilyTree.
 */
export function FamilyTreePersonCard({
    personName,
    imageSrc,
    relationLabel,
    lifeSpan,
    variant = "descendant",
    lineClass = "",
}) {
    const initial = (personName || "?").trim().slice(0, 1).toUpperCase() || "?";
    const rootClass = ["ft-card", `ft-card--${variant}`, lineClass]
        .filter(Boolean)
        .join(" ");

    return (
        <div className={rootClass}>
            <div className="ft-card__inner">
                <div className="ft-card__avatar-wrap">
                    {imageSrc ? (
                        <img
                            className="ft-card__avatar"
                            src={imageSrc}
                            alt=""
                        />
                    ) : (
                        <div
                            className="ft-card__avatar ft-card__avatar--placeholder"
                            aria-hidden
                        >
                            {initial}
                        </div>
                    )}
                </div>
                {relationLabel ? (
                    <span className="ft-card__role">{relationLabel}</span>
                ) : null}
                <p className="ft-card__name">{personName || "—"}</p>
                {lifeSpan ? (
                    <span className="ft-card__meta">{lifeSpan}</span>
                ) : null}
            </div>
        </div>
    );
}
