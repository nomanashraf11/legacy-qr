import { ErrorMessage, Field, Form, Formik } from "formik";
import React from "react";
import { IoClose, IoImagesOutline } from "react-icons/io5";
import * as Yup from "yup";
import { API_BASE_URL } from "../config";
import { toast } from "react-toastify";
import { objectToFormData } from "../utils";
import { Spinner } from "./Spinner";
import { useParams } from "react-router-dom";

const validationSchema = Yup.object().shape({
  name: Yup.string()
    .min(2, "Name should be at least 6 characters long")
    .required("Name is required"),
  description: Yup.string()
    .min(2, "Thought should be at least 6 characters long")
    .required("Your thoughts are required"),
});

export const PostModal = ({ open, setOpen, refetch, theme }) => {
  const { id } = useParams();
  const handleClose = () => {
    setOpen(false);
  };

  const initalValues = {
    name: "",
    description: "",
    image: null,
  };

  const handleSubmit = async (values, { setSubmitting, resetForm }) => {
    setSubmitting(true);
    try {
      const payload = {
        name: values.name,
        description: values.description,
        ...(values.image && { image: values.image }),
      };
      const formData = objectToFormData(payload);
      const response = await fetch(`${API_BASE_URL}/${id}/add_tribute`, {
        method: "POST",
        body: formData,
      }).then((res) => res.json());
      if (response.status === 201) {
        toast.success(response.messsage);
        resetForm();
        refetch();
        handleClose();
      } else {
        toast.error(response.messsage);
      }
    } catch (error) {
      console.log(error);
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <Formik
      initialValues={initalValues}
      onSubmit={handleSubmit}
      validationSchema={validationSchema}
      validateOnMount
      validateOnBlur
      validateOnChange
    >
      {({
        values,
        setFieldValue,
        resetForm,
        errors,
        handleBlur,
        isValid,
        isSubmitting,
      }) => (
        <Form>
          <div
            className={
              `${
                theme ? "bg-black" : "bg-[#F1F1F1]"
              } overflow-y-auto  backdrop-filter backdrop-blur-sm bg-opacity-50 overflow-x-hidden fixed top-0 right-0 left-0 z-50 flex justify-center items-center w-full md:inset-0 max-h-full transition-all duration-200 ease-in-out ` +
              (open ? "block" : "hidden")
            }
          >
            <div className="relative p-4 w-full max-w-[500px] max-h-full">
              <div
                className={`${
                  theme ? "bg-[#242526]" : "bg-[#F1F1F1]"
                } relative bg-[#242526] rounded-lg shadow`}
              >
                <div className="flex items-center justify-center p-4 border-b relative border-white/5 rounded-t">
                  <h3 className="text-xl font-semibold text-center w-full">
                    Add Tribute
                  </h3>
                  <div className="absolute top-0 right-0 left-0 bottom-0 flex items-center justify-end p-4">
                    <button
                      onClick={() => {
                        handleClose();
                        resetForm();
                      }}
                      type="button"
                      className="text-gray-400 transition-all duration-200 ease-in-out bg-white bg-opacity-20 hover:bg-opacity-25 active:bg-opacity-30 rounded-full text-sm w-8 h-8 ms-auto inline-flex justify-center items-center"
                    >
                      <IoClose size={24} />
                    </button>
                  </div>
                </div>
                <div className="p-4 md:p-5">
                  <div>
                    <div className="overflow-auto max-h-80 hide-scrollbar mb-3">
                      <div>
                        <label
                          className={`${
                            theme ? "text-white" : "text-black"
                          } block mb-1 text-sm font-medium `}
                          htmlFor="name"
                        >
                          Enter your name
                        </label>
                        <Field
                          placeholder="Full Name"
                          className={`${
                            theme
                              ? "bg-[#333333] border-white/20 placeholder-gray-400 text-white"
                              : "bg-[#F1F1F1] border-white/20 placeholder text-black"
                          } border outline-none text-sm rounded-lg block w-full p-2.5 `}
                          name="name"
                          required
                          autoFocus
                          onBlur={handleBlur}
                        />
                        <ErrorMessage
                          className="text-red-400 text-xs"
                          name="name"
                          component="div"
                        />
                      </div>
                      <div className="mb-2 mt-2">
                        <textarea
                          name="description"
                          id="description"
                          rows={4}
                          onBlur={handleBlur}
                          required
                          maxLength={10000}
                          className={`${
                            theme
                              ? "bg-[#333333] border-white/20 placeholder-gray-400 text-white"
                              : "bg-[#cac5c5] border-white/20 placeholder text-black"
                          } border outline-none text-sm rounded-lg block w-full p-2.5 `}
                          placeholder="Write your thoughts here..."
                          value={values.description}
                          onChange={(e) =>
                            setFieldValue("description", e.target.value)
                          }
                        />
                        <ErrorMessage
                          className="text-red-400 text-xs"
                          name="description"
                          component="div"
                        />
                      </div>
                      {values.image && (
                        <div className="w-full mb-8">
                          <div className="w-full border-white/15 relative rounded-lg overflow-hidden">
                            <img
                              className="w-full h-auto max-w-full max-h-52 object-cover"
                              src={URL.createObjectURL(values.image)}
                            />
                            <button
                              onClick={() => {
                                setFieldValue("image", null);
                              }}
                              type="button"
                              className="absolute top-2 right-2 border-white/45 text-gray-400 transition-all duration-200 ease-in-out bg-[#525355] active:bg-opacity-30 rounded-full text-sm w-7 h-7 ms-auto inline-flex justify-center items-center"
                            >
                              <IoClose size={20} />
                            </button>
                          </div>
                        </div>
                      )}
                    </div>

                    <div className="space-y-3 w-full">
                      <label className="flex w-full items-center justify-between border active:bg-white active:bg-opacity-10 transition-all duration-200 ease-in-out border-white/10 rounded-lg cursor-pointer p-3">
                        <input
                          onChange={(e) => {
                            const file = e.target.files[0];
                            if (file?.type.startsWith("image/")) {
                              setFieldValue("image", file);
                            }
                          }}
                          type="file"
                          hidden
                          accept="image/*"
                        />
                        <p>Add to your tribute</p>
                        <IoImagesOutline size={20} />
                      </label>
                    </div>

                    <button
                      disabled={!isValid || isSubmitting}
                      type="submit"
                      className="mt-5 w-full text-black disabled:bg-gray-50/10 bg-white/85 active:bg-white/50 hover:bg-white/75 transition-all duration-200 ease-in-out rounded-lg py-2"
                    >
                      {isSubmitting ? <Spinner /> : "Post"}
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </Form>
      )}
    </Formik>
  );
};
