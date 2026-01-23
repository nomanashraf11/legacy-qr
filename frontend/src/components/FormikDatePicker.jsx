import { useEffect } from "react";
import { SelectDatepicker } from "react-select-datepicker";
import { isValidDate } from "../utils";

export function FormikDatePicker({ form, field, id }) {
    let { setFieldValue, setErrors, values, errors } = form;

    useEffect(() => {
        if (isValidDate(values[field.name])) {
            let stateErrors = { ...errors };

            stateErrors = Object.keys(stateErrors)
                .filter((objKey) => objKey !== field.name)
                .reduce((newObj, key) => {
                    newObj[key] = stateErrors[key];
                    return newObj;
                }, {});

            setErrors(stateErrors);
        }
    }, [values[field.name], id, field.name]);

    // Convert the form value to a proper Date object or null
    const getSelectedDate = () => {
        const value = form.values[field.name];
        if (!value) return null;

        // If it's already a Date object, return it
        if (value instanceof Date) return value;

        // If it's a string, try to convert it to Date
        if (typeof value === "string") {
            const date = new Date(value);
            return isNaN(date.getTime()) ? null : date;
        }

        return null;
    };

    return (
        <div className="date-selector">
            <SelectDatepicker
                labels={{
                    yearPlaceholder: "Year",
                    monthPlaceholder: "Month",
                    dayPlaceholder: "Day",
                }}
                id={Math.random() + id || 0}
                order={"month/day/year"}
                hideLabels
                selectedDate={getSelectedDate()}
                onDateChange={(date) => {
                    if (isValidDate(date)) {
                        setFieldValue(field.name, date);
                    }
                }}
            />
        </div>
    );
}
