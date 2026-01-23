import { ErrorMessage, Field, Form, Formik } from "formik";
import * as Yup from "yup";
import { IoClose } from "react-icons/io5";
import { changePasswordAsync } from "../services";
import { Spinner } from ".";
import { FaRegEye, FaRegEyeSlash } from "react-icons/fa";
import { toast } from "react-toastify";
import { regex } from "../utils";

export const ChangePasswordModal = ({ open, setOpen, theme }) => {
  const initialValues = {
    old_password: "",
    password: "",
    confirmPassword: "",
    showOldPassword: false,
    showNewPassword: false,
    showConfirmPassword: false,
  };

  const validationSchema = Yup.object().shape({
    old_password: Yup.string().required("Current Passwrod is required"),
    password: Yup.string()
      .matches(
        regex.password,
        "Password must be at least 8 characters long and include at least 1 digit, 1 lowercase letter, and 1 uppercase letter."
      )
      .required("New Password is required"),
    confirmPassword: Yup.string()
      .oneOf([Yup.ref("password"), null], "Passwords must match")
      .required("Confirm Password is required"),
  });

  const handleSubmit = async (
    { old_password, password },
    { setSubmitting, resetForm }
  ) => {
    setSubmitting(true);
    try {
      const { data } = await changePasswordAsync({ old_password, password });

      if (data.status === 200) {
        toast.success(data.message);
        resetForm();
        setOpen(false);
      } else {
        toast.error(data.message);
      }
    } catch (error) {
      console.log(error);
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <Formik
      validateOnMount
      validateOnBlur
      validateOnChange
      initialValues={initialValues}
      onSubmit={handleSubmit}
      validationSchema={validationSchema}
    >
      {({ resetForm, isSubmitting, isValid, values, setFieldValue }) => (
        <Form>
          <div
            className={
              `${
                theme ? " bg-black" : "bg-white"
              } overflow-y-auto  backdrop-filter backdrop-blur-sm bg-opacity-50 overflow-x-hidden fixed top-0 right-0 left-0 bottom-0 max-h-full !z-50 flex justify-center items-center w-full md:inset-0 transition-all duration-200 ease-in-out ` +
              (open ? "block" : "hidden")
            }
          >
            <div className="relative p-4 w-full max-w-[500px] max-h-full">
              <div
                className={`${
                  theme ? " bg-black" : "bg-white"
                }  relative  rounded-lg shadow`}
              >
                <div className="flex items-center justify-center p-4 border-b relative border-white/5 rounded-t">
                  <h3 className="text-xl font-semibold text-center w-full">
                    Change Password
                  </h3>
                  <div className="absolute top-0 right-0 left-0 bottom-0 flex items-center justify-end p-4">
                    <button
                      onClick={() => {
                        resetForm();
                        setOpen(false);
                      }}
                      type="button"
                      className={`${
                        theme ? " bg-black" : "bg-white"
                      }  text-gray-400 transition-all duration-200 ease-in-out  bg-opacity-20 hover:bg-opacity-25 active:bg-opacity-30 rounded-full text-sm w-8 h-8 ms-auto inline-flex justify-center items-center`}
                    >
                      <IoClose size={24} />
                      <span className="sr-only">Close modal</span>
                    </button>
                  </div>
                </div>
                <div className="p-4 md:p-5 space-y-4">
                  <div>
                    <label
                      htmlFor="old_password"
                      className={`${
                        theme ? " text-white" : "text-black"
                      }  block mb-2 text-sm font-medium text-white`}
                    >
                      Current password
                    </label>
                    <div className={"relative"}>
                      <Field
                        type={values.showOldPassword ? "text" : "password"}
                        placeholder={"Enter current password"}
                        name="old_password"
                        className={`${
                          theme
                            ? "bg-[#333333] border-white/20 placeholder-gray-400 text-white"
                            : "bg-[#F1F1F1] border-white/20 placeholder text-black"
                        } border outline-none text-sm rounded-lg block w-full p-2.5 `}
                        required
                      />
                      <span
                        onClick={() =>
                          setFieldValue(
                            "showOldPassword",
                            !values.showOldPassword
                          )
                        }
                        className="absolute z-[1] cursor-pointer top-3 right-3"
                      >
                        {values.showOldPassword ? (
                          <FaRegEyeSlash />
                        ) : (
                          <FaRegEye />
                        )}
                      </span>
                    </div>
                    <ErrorMessage
                      className="text-red-400 text-xs"
                      name="old_password"
                      component="div"
                    />
                  </div>
                  <div>
                    <label
                      htmlFor="password"
                      className={`${
                        theme ? " text-white" : "text-black"
                      }  block mb-2 text-sm font-medium text-white`}
                    >
                      New Password
                    </label>
                    <div className="relative">
                      <Field
                        type={values.showNewPassword ? "text" : "password"}
                        name="password"
                        placeholder={"Enter new password"}
                        className={`${
                          theme
                            ? "bg-[#333333] border-white/20 placeholder-gray-400 text-white"
                            : "bg-[#F1F1F1] border-white/20 placeholder text-black"
                        } border outline-none text-sm rounded-lg block w-full p-2.5 `}
                        required
                      />
                      <span
                        onClick={() =>
                          setFieldValue(
                            "showNewPassword",
                            !values.showNewPassword
                          )
                        }
                        className="absolute z-[1] cursor-pointer top-3 right-3"
                      >
                        {values.showNewPassword ? (
                          <FaRegEyeSlash />
                        ) : (
                          <FaRegEye />
                        )}
                      </span>
                    </div>
                    <ErrorMessage
                      className="text-red-400 text-xs"
                      name="password"
                      component="div"
                    />
                  </div>
                  <div className={"pb-4"}>
                    <label
                      htmlFor="confirmPassword"
                      className={`${
                        theme ? " text-white" : "text-black"
                      }  block mb-2 text-sm font-medium text-white`}
                    >
                      Confirm Password
                    </label>
                    <div className="relative">
                      <Field
                        type={values.showConfirmPassword ? "text" : "password"}
                        name="confirmPassword"
                        placeholder={"Enter password again, just to be sure"}
                        className={`${
                          theme
                            ? "bg-[#333333] border-white/20 placeholder-gray-400 text-white"
                            : "bg-[#F1F1F1] border-white/20 placeholder text-black"
                        } border outline-none text-sm rounded-lg block w-full p-2.5 `}
                        required
                      />
                      <span
                        onClick={() =>
                          setFieldValue(
                            "showConfirmPassword",
                            !values.showConfirmPassword
                          )
                        }
                        className="absolute z-[1] cursor-pointer top-3 right-3"
                      >
                        {values.showConfirmPassword ? (
                          <FaRegEyeSlash />
                        ) : (
                          <FaRegEye />
                        )}
                      </span>
                    </div>
                    <ErrorMessage
                      className="text-red-400 text-xs"
                      name="confirmPassword"
                      component="div"
                    />
                  </div>

                  <button
                    disabled={!isValid || isSubmitting}
                    type="submit"
                    className="w-full disabled:text-gray-400 text-[#333333] bg-white/90 active:bg-white/75 disabled:bg-white/10 font-medium rounded-lg px-5 py-2 text-center"
                  >
                    {isSubmitting ? <Spinner /> : "Update Password"}
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
