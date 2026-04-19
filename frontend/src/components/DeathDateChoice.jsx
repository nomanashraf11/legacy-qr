import { Field } from "formik";
import { FormikDatePicker } from "./FormikDatePicker";

/**
 * Mutually exclusive "still living" vs "enter date of death" — clearer than a lone "Present" checkbox.
 */
export function DeathDateChoice({
    idPrefix,
    isLiving,
    darkTheme,
    fieldName,
    onLiving,
    onDeceased,
    pickerClassName,
    livingHelp = "Use this when there is no date of death to enter (this person is living).",
    deceasedHelp = "Select this to enter a full date of death below.",
}) {
    const text = darkTheme ? "text-white" : "text-black";
    const sub = darkTheme ? "text-white/70" : "text-gray-600";
    const box = darkTheme
        ? "border-white/10 bg-white/5"
        : "border-gray-200 bg-gray-50";

    return (
        <div className="space-y-3 w-full">
            <div
                className={`rounded-lg border p-4 space-y-4 ${box}`}
                role="radiogroup"
                aria-label="Date of death options"
            >
                <label
                    className={`flex items-start gap-3 cursor-pointer ${text}`}
                >
                    <input
                        type="radio"
                        className="mt-1.5 shrink-0"
                        name={`${idPrefix}-dod-mode`}
                        checked={isLiving}
                        onChange={onLiving}
                    />
                    <span>
                        <span className="font-medium block">Still living</span>
                        <span className={`text-sm ${sub}`}>{livingHelp}</span>
                    </span>
                </label>
                <label
                    className={`flex items-start gap-3 cursor-pointer ${text}`}
                >
                    <input
                        type="radio"
                        className="mt-1.5 shrink-0"
                        name={`${idPrefix}-dod-mode`}
                        checked={!isLiving}
                        onChange={onDeceased}
                    />
                    <span>
                        <span className="font-medium block">Has passed</span>
                        <span className={`text-sm ${sub}`}>{deceasedHelp}</span>
                    </span>
                </label>
            </div>
            {!isLiving && (
                <Field
                    name={fieldName}
                    component={FormikDatePicker}
                    className={pickerClassName}
                />
            )}
        </div>
    );
}
