import { useMutation } from "@tanstack/react-query";
import { useEffect, useState } from "react";
import { IoClose } from "react-icons/io5";
import { deleteRelationImage, removeRelationAsync } from "../services";
import { MdCameraEnhance } from "react-icons/md";
import { API_BASE_URL } from "../config";
import { getAuthToken, useAppSelector } from "../redux";
import { formatDateStrToDate, isValidDate, objectToFormData } from "../utils";
import { IoIosClose } from "react-icons/io";
import { SelectDatepicker } from "react-select-datepicker";
import { ErrorMessage } from "formik";
import { v4 as uuidv4 } from "uuid";
import { capitalizeFirstLetter } from "../utils/index";
export const RelationCard = ({
  index,
  setFieldValue,
  values,
  relationship,
  refetch,
  handleBlur,
  errors,
  setIsDisabled,
  theme,
}) => {
  const specialRelations = [
    "GRANDDAUGHTER",
    "GRANDSON",
    "SONINLAW",
    "DAUGHTERINLAW",
  ];
  const appToken = useAppSelector(getAuthToken);

  const [isLoading, setIsLoading] = useState(false);
  const [isDead, setIsDead] = useState(relationship.dod ? false : true);
  const [tempDate, setTempDate] = useState(relationship);
  const [dateOfDeath, setDateOfDeath] = useState();

  const { mutate, isPending } = useMutation({
    mutationFn: (id) => removeRelationAsync(id),
  });

  const { mutateAsync, isPending: isDeleting } = useMutation({
    mutationFn: (name) => deleteRelationImage(name),
  });

  const handleRemove = async () => {
    if (relationship.uuid) {
      mutate(relationship.uuid, {
        onSuccess: ({ data }) => {
          if (data.status === 200) {
            const newRelations = [...values.relations];
            newRelations.splice(index, 1);
            setFieldValue("relations", newRelations);
            refetch();
          }
        },
        onError: (err) => {
          console.log(err);
        },
      });
    } else {
      const newRelations = [...values.relations];
      newRelations.splice(index, 1);
      setFieldValue("relations", newRelations);
    }
  };

  async function handleUpload(image) {
    try {
      setIsLoading(true);

      const body = objectToFormData({ image });

      const response = await fetch(`${API_BASE_URL}/upload-relation-photo`, {
        method: "POST",
        body,
        headers: {
          Accept: "application/json",
          Authorization: `Bearer ${appToken}`,
          "X-Custom-Header": "header value",
        },
      }).then((res) => res.json());

      if (response.status === 200) {
        let newRelations = [...values.relations];
        newRelations[index] = {
          ...newRelations[index],
          image: response.data,
          image_name: response.name,
        };
        setFieldValue("relations", newRelations);
      }
    } catch (error) {
      console.log(error);
    } finally {
      setIsLoading(false);
    }
  }

  const handleDeleteImage = async (e) => {
    e.stopPropagation();
    try {
      const { data } = await mutateAsync(relationship.image_name);
      if (data.status === 200) {
        refetch();
        const newRelations = [...values.relations];
        newRelations[index] = {
          ...newRelations[index],
          image: null,
          image_name: null,
        };
        setFieldValue("relations", newRelations);
      }
    } catch (error) {
      console.log(error);
    }
  };

  const transformedImageUrl = (() => {
    if (!relationship.image) {
      console.log("No image URL provided");
      return null;
    }

    const originalUrl = relationship.image.trim();
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
  })();

  console.log("Final URL to be used:", transformedImageUrl);
  useEffect(() => {
    if (relationship?.dod) {
      setDateOfDeath(
        relationship?.dod ? formatDateStrToDate(relationship?.dod) : new Date()
      );
      setIsDead(relationship?.dod ? false : true);
    }
    if (!isDead) {
      if (isValidDate(formatDateStrToDate(relationship?.dod))) {
        setIsDisabled(false);
      } else {
        setIsDisabled(true);
      }
    }
  }, [relationship]);

  return (
    <div
      className={`${
        theme ? "bg-[#333333]" : "bg-white "
      } space-y-3  p-4 rounded border border-[2px]`}
    >
      <div>
        <div className={"flex items-center justify-end"}>
          <button type="button" onClick={handleRemove}>
            {isPending ? (
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
              <IoClose
                size={20}
                className={` ${
                  theme ? "text-white" : "text-black"
                }  cursor-pointer`}
              />
            )}
          </button>
        </div>
        <div className={"grid grid-cols-1 w-full sm:grid-cols-11 gap-4"}>
          <div className={"col-span-5"}>
            <div className="w-full">
              <label
                className={` ${
                  theme ? "text-white" : "text-black"
                }block mb-2 text-sm font-medium `}
              >
                Relation
              </label>
              <div className={"w-full border rounded-lg border-white/20"}>
                <select
                  value={relationship?.name}
                  onBlur={handleBlur}
                  name={`relations[${index}].name`}
                  onChange={(e) => {
                    const name = e.target.value;

                    const newRelations = [...values.relations];
                    newRelations[index] = {
                      ...newRelations[index],
                      name,
                    };
                    setFieldValue("relations", newRelations);
                  }}
                  className={`${
                    theme
                      ? "bg-[#333333] border-white/20 placeholder-gray-400 text-white"
                      : "bg-[#F1F1F1] border-white/20 placeholder text-black"
                  } border outline-none text-sm rounded-lg block w-full p-2.5 `}
                >
                  {relations
                    .filter((option) => {
                      // Check if 'Mother', 'Father', etc., are in the relations
                      const hasMother = values.relations.some(
                        (relation) => relation?.name?.toUpperCase() === "MOTHER"
                      );
                      const hasFather = values.relations.some(
                        (relation) => relation?.name?.toUpperCase() === "FATHER"
                      );
                      const hasSon =
                        values.relations.filter(
                          (relation) => relation?.name?.toUpperCase() === "SON"
                        ).length >= 2;

                      const hasDaughter = values.relations.some(
                        (relation) =>
                          relation?.name?.toUpperCase() === "DAUGHTER"
                      );

                      // If 'Mother' is not in relations, remove "mother's side grandparents"
                      if (
                        !hasMother &&
                        (option.toUpperCase() === "MATERNALGRANDFATHER" ||
                          option.toUpperCase() === "MATERNALGRANDMOTHER")
                      ) {
                        // Skip filtering if the current option is the selected value
                        if (
                          relationship?.name.toUpperCase() ===
                          option.toUpperCase()
                        ) {
                          return true;
                        }
                        return false;
                      }

                      if (!hasSon && option.toUpperCase() === "DAUGHTERINLAW") {
                        // Skip filtering if the current option is the selected value
                        if (
                          relationship?.name.toUpperCase() ===
                          option.toUpperCase()
                        ) {
                          return true;
                        }
                        return false;
                      }

                      if (!hasDaughter && option.toUpperCase() === "SONINLAW") {
                        // Skip filtering if the current option is the selected value
                        if (
                          relationship?.name.toUpperCase() ===
                          option.toUpperCase()
                        ) {
                          return true;
                        }
                        return false;
                      }

                      // If 'Father' is not in relations, remove "father's side grandparents"
                      if (
                        !hasFather &&
                        (option.toUpperCase() === "PATERNALGRANDFATHER" ||
                          option.toUpperCase() === "PATERNALGRANDMOTHER")
                      ) {
                        // Skip filtering if the current option is the selected value
                        if (
                          relationship?.name.toUpperCase() ===
                          option.toUpperCase()
                        ) {
                          return true;
                        }
                        return false;
                      }

                      if (
                        !hasDaughter &&
                        !hasSon &&
                        (option.toUpperCase() === "GRANDSON" ||
                          option.toUpperCase() === "GRANDDAUGHTER")
                      ) {
                        // Skip filtering if the current option is the selected value
                        if (
                          relationship?.name.toUpperCase() ===
                          option.toUpperCase()
                        ) {
                          return true;
                        }
                        return false;
                      }

                      return true; // Otherwise, keep the option
                    })
                    .map((option) => (
                      <option key={option} value={option.toUpperCase()}>
                        {capitalizeFirstLetter(option)}
                      </option>
                    ))}
                </select>

                <ErrorMessage
                  className="text-red-400 text-xs"
                  name={`relations[${index}].name`}
                />
              </div>
            </div>
          </div>
          <div className={"col-span-5"}>
            <div className="w-full">
              <label
                className={` ${
                  theme ? "text-white" : "text-black"
                }block mb-2 text-sm font-medium `}
              >
                Name
              </label>
              <input
                onBlur={handleBlur}
                name={`relations[${index}].person_name`}
                onChange={(e) => {
                  const newRelations = [...values.relations];
                  newRelations[index] = {
                    ...newRelations[index],
                    person_name: e.target.value,
                  };
                  setFieldValue("relations", newRelations);
                }}
                placeholder="Enter name"
                required={relationship.name ? true : false}
                className={`${
                  theme
                    ? "bg-[#333333] border-white/20 placeholder-gray-400 text-white"
                    : "bg-[#F1F1F1] border-white/20 placeholder text-black"
                } border outline-none text-sm rounded-lg block w-full p-2.5 `}
                value={relationship.person_name}
              />

              <ErrorMessage
                component={"div"}
                className="text-red-400 text-xs mt-1"
                name={`relations[${index}].person_name`}
              />
            </div>
          </div>
          {specialRelations.some(
            (relation) =>
              values.relations[index].name.toUpperCase() === relation
          ) ? (
            <div className={"col-span-4"}>
              <div className="w-full">
                <label
                  className={` ${
                    theme ? "text-white" : "text-black"
                  }block mb-2 text-sm font-medium `}
                >
                  Related To
                </label>
                <select
                  onBlur={handleBlur}
                  key={index + 4}
                  value={relationship?.relation_id || ""}
                  name={`relations[${index}].relation_id`}
                  onChange={(e) => {
                    const relation_id = e.target.value;

                    const newRelations = [...values.relations];
                    newRelations[index] = {
                      ...newRelations[index],
                      relation_id,
                    };
                    setFieldValue("relations", newRelations);
                  }}
                  required={relationship?.relation_id ? true : false}
                  className={`${
                    theme
                      ? "bg-[#333333] border-white/20 placeholder-gray-400 text-white"
                      : "bg-[#F1F1F1] border-white/20 placeholder text-black"
                  } border outline-none text-sm rounded-lg block w-full p-2.5 `}
                >
                  <option>Select from below options</option>
                  {values.relations
                    .filter((relation) => {
                      const currentRelation =
                        values.relations[index]?.name?.toUpperCase();

                      if (
                        ["GRANDDAUGHTER", "GRANDSON"].includes(
                          currentRelation
                        ) &&
                        (relation.name.toUpperCase() === "SON" ||
                          relation.name.toUpperCase() === "DAUGHTER")
                      ) {
                        return true; // ✅ Correctly return true
                      }

                      if (
                        currentRelation === "SONINLAW" &&
                        relation.name.toUpperCase() === "DAUGHTER"
                      ) {
                        return true; // ✅ Show only daughters
                      }

                      if (
                        currentRelation === "DAUGHTERINLAW" &&
                        relation.name.toUpperCase() === "SON"
                      ) {
                        return true; // ✅ Show only sons
                      }

                      return false; // ❌ Default case to filter out unwanted options
                    })

                    .map((relation) => (
                      <option
                        key={relation?.uuid || uuidv4()}
                        value={relation?.uuid}
                      >
                        {relation.person_name}
                      </option>
                    ))}
                </select>

                <ErrorMessage
                  component={"div"}
                  className="text-red-400 text-xs mt-1"
                  name={`relations[${index}].relation_id`}
                />
              </div>
            </div>
          ) : (
            ""
          )}
          <div className="col-span-2 flex justify-end items-end">
            <div className="w-full">
              <label
                className={`${
                  theme ? "text-white" : "text-black"
                } sm:block hidden mb-2 invisible text-sm font-medium`}
              >
                image
              </label>

              <div className="relative h-10 w-10 ">
                {relationship.image && (
                  <IoIosClose
                    onClick={handleDeleteImage}
                    className={`${
                      theme ? "text-white bg-gray-400" : "text-black bg-black"
                    }absolute z-[1] cursor-pointer top-0 right-0  border rounded-full`}
                  />
                )}

                <label
                  className={`${
                    theme
                      ? "active:bg-[#222222] bg-[#333333]"
                      : "active:bg-[#dbd9d9] bg-[#e9e5e5]"
                  } flex w-10 h-10 items-center justify-center relative overflow-hidden border rounded border-white/20 cursor-pointer transition-all duration-150 ease-in-out `}
                >
                  {isLoading || isDeleting ? (
                    <svg
                      aria-hidden="true"
                      className="w-4 h-4 text-gray-200 animate-spin dark:text-gray-600 fill-blue-600"
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
                  ) : relationship.image ? (
                    <img
                      className="w-full h-full object-cover"
                      src={transformedImageUrl}
                    />
                  ) : (
                    <MdCameraEnhance />
                  )}
                  <input
                    id={"image" + index}
                    name="image"
                    onChange={(e) => {
                      handleUpload(e.target.files[0], index);
                    }}
                    type="file"
                    hidden
                    accept="image/*"
                  />
                </label>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div className="date-selector">
        <label
          className={` ${
            theme ? "text-white" : "text-black"
          }block mb-2 text-sm font-medium `}
        >
          Date of Birth
        </label>
        <SelectDatepicker
          labels={{
            yearPlaceholder: "Year",
            monthPlaceholder: "Month",
            dayPlaceholder: "Day",
          }}
          minDate={new Date(1800, 0, 1)}
          order={"month/day/year"}
          hideLabels
          id={index}
          key={index}
          selectedDate={formatDateStrToDate(relationship.dob || "") || null}
          onDateChange={(date) => {
            if (isValidDate(date)) {
              const newRelations = [...values.relations];
              newRelations[index] = {
                ...newRelations[index],
                dob: date,
              };
              setFieldValue("relations", newRelations);
            }
          }}
        />
        {errors[index]?.dob && (
          <p component={"div"} className="text-red-400 text-xs mt-1">
            {errors[index]?.dob}
          </p>
        )}
        {!isValidDate(
          relationship.dob ? formatDateStrToDate(relationship.dob) : null
        ) && <p className={"text-red-400 mt-1 text-xs"}>Select a valid date</p>}
      </div>
      <div className="date-selector">
        <div className={"flex items-center mb-2 justify-between"}>
          <label
            style={{ visibility: isDead ? "hidden" : "visible" }}
            className={` ${
              theme ? "text-white" : "text-black"
            } block mb-2 text-sm font-medium `}
          >
            Date of Death
          </label>
          <label
            className={`text-sm gap-1 select-none inline-flex font-medium ${
              theme ? "text-white" : "text-black"
            }`}
          >
            <input
              type="checkbox"
              id={"dead" + index}
              name={"dead" + index}
              checked={isDead}
              onChange={(e) => {
                setIsDead(e.target.checked);

                let newRelations = [...values.relations];

                if (e.target.checked) {
                  setIsDisabled(false);
                  setTempDate(relationship.dod);
                  newRelations[index] = {
                    ...newRelations[index],
                    dod: null,
                  };
                } else {
                  setIsDisabled(true);
                  newRelations[index] = {
                    ...newRelations[index],
                    dod: tempDate,
                  };
                }

                setFieldValue("relations", newRelations);
              }}
            />
            Present
          </label>
        </div>
        <span style={{ visibility: isDead ? "hidden" : "visible" }}>
          <SelectDatepicker
            order={"month/day/year"}
            labels={{
              yearPlaceholder: "Year",
              monthPlaceholder: "Month",
              dayPlaceholder: "Day",
            }}
            id={index}
            key={index}
            disabled={isDead}
            hideLabels
            minDate={formatDateStrToDate(relationship.dob) || null}
            selectedDate={dateOfDeath}
            onDateChange={(date) => {
              setDateOfDeath(date);
              if (isValidDate(date)) {
                const newRelations = [...values.relations];
                newRelations[index] = {
                  ...newRelations[index],
                  dod: date,
                };
                setFieldValue("relations", newRelations);
                setIsDisabled(false);
              }
            }}
          />
        </span>
        {errors[index]?.dod && (
          <p component={"div"} className="text-red-400 text-xs mt-1">
            {errors[index]?.dod}
          </p>
        )}

        {!isDead &&
          !isValidDate(
            relationship.dod ? formatDateStrToDate(relationship.dod) : null
          ) && (
            <p className={"text-red-400 mt-1 text-xs"}>Select a valid date</p>
          )}
      </div>
    </div>
  );
};

const relations = [
  "son",
  "daughter",
  "brother",
  "sister",
  "father",
  "mother",
  "spouse",
  "maternalGrandfather",
  "maternalGrandmother",
  "paternalGrandfather",
  "paternalGrandmother",
  "grandDaughter",
  "grandSon",
  "sonInLAW",
  "daughterInLaw",
];
