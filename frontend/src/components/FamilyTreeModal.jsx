import { Form, Formik } from "formik";
import * as Yup from "yup";
import { IoClose } from "react-icons/io5";
import { RelationCard } from "./RelationCard";
import { getAuthToken, getUserData, useAppSelector } from "../redux";
import { MdDone } from "react-icons/md";
import { dateYYYYMMDDFormat, objectToFormData } from "../utils";
import { API_BASE_URL } from "../config";
import { useParams } from "react-router-dom";
import { toast } from "react-toastify";
import { useState } from "react";
import { v4 as uuidv4 } from "uuid";

const validationSchema = Yup.object().shape({
    relations: Yup.array().of(
        Yup.object().shape({
            name: Yup.string().required("Relation is required"),
            person_name: Yup.string().required("Name is required"),
            dob: Yup.date()
                .nullable()
                .notRequired()
                .max(new Date(), "Date of birth must be before today"),
            dod: Yup.date()
                .nullable()
                .notRequired()
                .test(
                    "after-dob",
                    "Date of death must be on or after date of birth",
                    function (value) {
                        const { dob } = this.parent;
                        if (!value || !dob) return true;
                        const death =
                            value instanceof Date ? value : new Date(value);
                        const birth = dob instanceof Date ? dob : new Date(dob);
                        return death >= birth;
                    }
                ),
        })
    ),
});

export const FamilyTreeModal = ({ open, setOpen, refetch }) => {
    const generateUUID = () => {
        return uuidv4();
    };
    const { id } = useParams();

    const userData = useAppSelector(getUserData);
    const appToken = useAppSelector(getAuthToken);

    let initialValues = {
        relations: userData?.relations || [],
    };

    const [isDisabled, setIsDisabled] = useState(false);

    const handleClose = () => {
        setOpen(false);
    };

    const handleSubmit = async (values, { setSubmitting }) => {
        setSubmitting(true);
        try {
            const { relations } = values;

            const {
                longitude,
                latitude,
                name,
                dob,
                dod,
                facebook,
                instagram,
                twitter,
                spotify,
                youtube,
                bio,
                spouse_facebook,
                spouse_instagram,
                spouse_twitter,
                spouseBio,
                spouse_badge,
                dark_theme,
                badge,
                spouseDob,
                spouseDod,
                spouseName,
            } = userData;

            let newRelations = relations?.map((item) => ({
                ...item,
                dob: item.dob ? dateYYYYMMDDFormat(item.dob) : null,
                dod: item.dod ? dateYYYYMMDDFormat(item.dod) : null,
            }));

            const payload = {
                ...(newRelations.length > 0 && {
                    relations: JSON.stringify(newRelations),
                }),
                bio,
                longitude,
                latitude,
                dob,
                dod,
                name,
                facebook,
                instagram,
                twitter,
                spotify,
                youtube,
                spouse_facebook,
                spouse_instagram,
                spouse_twitter,
                spouseBio,
                spouse_badge,
                dark_theme,
                badge,
                spouseDob,
                spouseDod,
                spouseName,
            };

            const body = objectToFormData(payload);

            const response = await fetch(`${API_BASE_URL}/${id}/add_bio`, {
                method: "POST",
                body,
                headers: {
                    Accept: "application/json",
                    Authorization: `Bearer ${appToken}`,
                    "X-Custom-Header": "header value",
                },
            }).then((res) => res.json());

            if (response.status === 200) {
                toast.success("Profile updated successfully");
                refetch();
                handleClose();
            } else {
                toast.error("Profile update failed");
            }
        } catch (error) {
            console.log("error", error);
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <Formik
            enableReinitialize
            validateOnBlur
            validateOnChange
            validateOnMount
            initialValues={initialValues}
            validationSchema={validationSchema}
            onSubmit={handleSubmit}
        >
            {({
                isSubmitting,
                isValid,
                values,
                setFieldValue,
                handleBlur,
                resetForm,
                errors,
            }) => (
                <Form>
                    <div
                        className={
                            `${
                                userData?.dark_theme ? "bg-black" : "bg-white"
                            }  backdrop-filter backdrop-blur-sm bg-opacity-60 overflow-x-hidden fixed top-0 right-0 left-0 bottom-0 max-h-full !z-50 flex justify-center items-center w-full md:inset-0 transition-all duration-200 ease-in-out ` +
                            (open ? "block" : "hidden")
                        }
                    >
                        <div className="relative p-4 w-full max-w-[500px] sm:max-w-2xl max-h-full">
                            <div
                                className={`${
                                    userData?.dark_theme
                                        ? "bg-black"
                                        : "bg-white"
                                } relative  rounded-lg shadow`}
                            >
                                <div className="flex items-center justify-center p-4 border-b relative border-white/5 rounded-t">
                                    <h3 className="sm:text-xl font-semibold text-center w-full pr-8 sm:pr-0">
                                        Add Family Tree
                                    </h3>
                                    <div className="absolute top-0 right-0 left-0 bottom-0 flex items-center justify-end p-4">
                                        <button
                                            onClick={() => {
                                                resetForm();
                                                handleClose();
                                            }}
                                            type="button"
                                            className={` ${
                                                userData?.dark_theme
                                                    ? "bg-black"
                                                    : "bg-[#c4c1c1]"
                                            } text-gray-400 transition-all duration-200 ease-in-out bg-white bg-opacity-20 hover:bg-opacity-25 active:bg-opacity-30 rounded-full text-sm w-8 h-8 ms-auto inline-flex justify-center items-center`}
                                        >
                                            <IoClose size={24} />
                                        </button>
                                    </div>
                                </div>
                                <div
                                    className={` ${
                                        userData?.dark_theme
                                            ? "bg-black"
                                            : "bg-white"
                                    } max-h-[450px] overflow-auto p-4 md:p-6`}
                                >
                                    <div className="space-y-4">
                                        <p className="block mb-6 sm:text-lg">
                                            Add{" "}
                                            {userData?.name || "your loved one"}
                                            ’s family members below (optional)
                                        </p>
                                        <div className="space-y-10 pb-4">
                                            {values.relations?.length > 0 &&
                                                values?.relations?.map(
                                                    (relationship, index) => (
                                                        <RelationCard
                                                            theme={
                                                                userData?.dark_theme
                                                            }
                                                            handleBlur={
                                                                handleBlur
                                                            }
                                                            key={index}
                                                            index={index}
                                                            setFieldValue={
                                                                setFieldValue
                                                            }
                                                            values={values}
                                                            relationship={
                                                                relationship
                                                            }
                                                            refetch={refetch}
                                                            errors={
                                                                errors.relations ||
                                                                []
                                                            }
                                                            setIsDisabled={
                                                                setIsDisabled
                                                            }
                                                        />
                                                    )
                                                )}
                                        </div>
                                        <button
                                            type="button"
                                            onClick={() => {
                                                if (values.relations) {
                                                    setFieldValue("relations", [
                                                        ...values.relations,
                                                        {
                                                            name: "SON",
                                                            person_name: "",
                                                            image: null,
                                                            dob: null,
                                                            dod: null,
                                                            uuid: generateUUID(),
                                                        },
                                                    ]);
                                                } else {
                                                    setFieldValue("relations", [
                                                        {
                                                            name: "SON",
                                                            person_name: "",
                                                            image: null,
                                                            dob: null,
                                                            dod: null,
                                                            uuid: generateUUID(),
                                                        },
                                                    ]);
                                                }
                                            }}
                                            className={`flex items-center justify-center select-none w-full bg-[#33333380]  hover:bg-[#33333380] active:scale-95 rounded-md py-3 shadow-md transition-all duration-300 ease-in-out z-10`}
                                        >
                                            Add New Relation / Family Member
                                        </button>
                                    </div>
                                </div>
                                <div
                                    className={
                                        "flex items-center pb-6 pt-2 px-6 mt-4 justify-between "
                                    }
                                >
                                    <span
                                        className={
                                            "flex select-none cursor-pointer items-center justify-center text-white/80 active:scale-90 transition-all duration-200 text-lg font-medium rounded-lg sm:ml-5 text-center"
                                        }
                                        onClick={handleClose}
                                    >
                                        Cancel
                                    </span>

                                    <button
                                        type="submit"
                                        disabled={
                                            !isValid ||
                                            isSubmitting ||
                                            values.relations?.length === 0 ||
                                            isDisabled
                                        }
                                        className="disabled:pointer-events-none disabled:opacity-40 select-none flex items-center justify-center gap-2 sm:min-w-60 text-white bg-blue-400 active:scale-90 transition-all duration-200 text-sm sm:text-lg font-medium rounded-lg px-5 py-2 text-center"
                                    >
                                        {isSubmitting ? (
                                            <svg
                                                aria-hidden="true"
                                                className="inline w-4 h-4 text-gray-200 animate-spin dark:text-gray-600 fill-blue-600"
                                                viewBox="0 0 100 101"
                                                fill="none"
                                                xmlns="http://www.w3.org/2000/svg"
                                            >
                                                <path
                                                    d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                                                    fill="currentColor"
                                                />
                                                <path
                                                    d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                                                    fill="currentFill"
                                                />
                                            </svg>
                                        ) : (
                                            <MdDone
                                                className={
                                                    "text-base sm:text-xl"
                                                }
                                            />
                                        )}
                                        Update Family Tree
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </Form>
            )}
        </Formik>
    );
};
