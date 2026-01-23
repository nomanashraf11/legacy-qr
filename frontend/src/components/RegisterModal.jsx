import { useState } from "react";
import { ErrorMessage, Field, Form, Formik } from "formik";
import * as Yup from "yup";
import { IoClose } from "react-icons/io5";
import { Link, useParams, useNavigate } from "react-router-dom";
import { GoArrowUpRight, GoSignIn } from "react-icons/go";
import { registerAsync, sendOTPRequestAsync } from "../services";
import { Spinner } from ".";
import { toast } from "react-toastify";
import { FaRegEye, FaRegEyeSlash } from "react-icons/fa";
import { regex } from "../utils";

import { setUserToken, useAppDispatch } from "../redux";
import { googleLogin } from "../services";
import { GoogleLogin } from "@react-oauth/google";

export const RegisterModal = ({ open, setOpen, setOpenLoginModal }) => {
  const { id } = useParams();
  const handleClose = () => {
    setOpen(false);
  };
  const navigate = useNavigate();
  const dipatch = useAppDispatch();
  const [setError] = useState(null);
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  console.log("aoo geee");
  const handleCredentialResponse = async (response) => {
    console.log(response);
    try {
      console.log(response.credential);
      const data = await googleLogin({
        idToken: response.credential,
        qrCode: id,
      });

      if (data.status === 200) {
        dipatch(setUserToken(data.data.data.token));
        toast.success(data.message);

        navigate("/my-qrcodes");
        setOpen(false);
      } else if (data.status == "204" && data.token) {
        dipatch(setUserToken(data.token));
        toast.success("Login Successful");

        navigate("/my-qrcodes");

        setOpen(false);
      } else {
        toast.error(data.message);
      }
    } catch (err) {
      console.error("Login error:", err);
      setError("Failed to authenticate with the server");
    }
  };
  const initialValues = {
    name: "",
    email: "",
    password: "",
    confirmPassword: "",
  };

  const validationSchema = Yup.object().shape({
    name: Yup.string().required("Name is required"),
    email: Yup.string().email("Invalid email").required("Email is required"),
    password: Yup.string()
      .matches(
        regex.password,
        "Password must be at least 8 characters long and include at least 1 digit, 1 lowercase letter, and 1 uppercase letter."
      )
      .required("Password is required"),
    confirmPassword: Yup.string()
      .oneOf([Yup.ref("password"), null], "Passwords do not match")
      .required("Confirm Password is required"),
  });

  const handleSubmit = async (
    values,
    { setSubmitting, resetForm, setErrors }
  ) => {
    setSubmitting(true);
    try {
      const payload = {
        name: values.name,
        email: values.email,
        password: values.password,
        ...(id ? { qr_code: id } : {}),
      };
      const { data } = await registerAsync(payload);
      if (data?.status === 201 && data.data) {
        const { data } = await sendOTPRequestAsync({ email: values.email });
        if (data.status === 200) {
          toast.info(
            "Your account has been registered successfully. Please verify your email address, check your email for verification details.",
            {
              autoClose: 8000,
              position: "top-center",
            }
          );
          resetForm();
          handleClose();
          setOpenLoginModal(true);
        } else {
          toast.error(data.message, {
            autoClose: 8000,
            position: "top-center",
          });
        }
      } else {
        setErrors(data.errors);
        toast.error(data.message, {
          autoClose: 8000,
          position: "top-center",
        });
      }
    } catch (error) {
      console.log(error);
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <Formik
      validateOnChange
      enableReinitialize
      validateOnMount
      initialValues={initialValues}
      onSubmit={handleSubmit}
      validationSchema={validationSchema}
    >
      {({ resetForm, isSubmitting, isValid }) => (
        <Form>
          <div
            className={
              "overflow-y-auto bg-black backdrop-filter backdrop-blur-sm bg-opacity-50 overflow-x-hidden fixed top-0 right-0 left-0 bottom-0 max-h-full !z-50 flex justify-center items-center w-full md:inset-0 transition-all duration-200 ease-in-out " +
              (open ? "block" : "hidden")
            }
          >
            <div className="relative p-4 w-full max-w-[500px] max-h-full">
              <div className="relative bg-[#242526] rounded-lg shadow">
                <div className="flex items-center justify-center p-4 border-b relative border-white/5 rounded-t">
                  <h3 className="text-xl font-semibold text-center w-full">
                    Register an account
                  </h3>
                  <div className="absolute top-0 right-0 left-0 bottom-0 flex items-center justify-end p-4">
                    <button
                      onClick={() => {
                        handleClose();
                        resetForm();
                        setOpenLoginModal(true);
                      }}
                      type="button"
                      className="text-gray-400 transition-all duration-200 ease-in-out bg-white bg-opacity-20 hover:bg-opacity-25 active:bg-opacity-30 rounded-full text-sm w-8 h-8 ms-auto inline-flex justify-center items-center"
                    >
                      <IoClose size={24} />
                      <span className="sr-only">Close modal</span>
                    </button>
                  </div>
                </div>
                <div className="p-4 md:p-5 space-y-4">
                  <div>
                    <label
                      htmlFor="name"
                      className="block mb-2 text-sm font-medium text-white"
                    >
                      Your name
                    </label>
                    <Field
                      type="text"
                      name="name"
                      id="name"
                      className="border outline-none text-sm rounded-lg block w-full p-2.5 bg-[#333333] border-white/20 placeholder-gray-400 text-white"
                      placeholder="Your Full Name"
                      required
                    />
                    <ErrorMessage
                      className="text-red-400 text-xs"
                      name="name"
                      component="div"
                    />
                  </div>
                  <div>
                    <label
                      htmlFor="email"
                      className="block mb-2 text-sm font-medium text-white"
                    >
                      Your email
                    </label>
                    <Field
                      type="email"
                      name="email"
                      id="email"
                      className="border outline-none text-sm rounded-lg block w-full p-2.5 bg-[#333333] border-white/20 placeholder-gray-400 text-white"
                      placeholder="name@company.com"
                      required
                    />
                    <ErrorMessage
                      className="text-red-400 text-xs"
                      name="email"
                      component="div"
                    />
                  </div>
                  <div>
                    <label
                      htmlFor="password"
                      className="block mb-2 text-sm font-medium text-white"
                    >
                      Your password
                    </label>
                    <div className="relative">
                      <Field
                        type={showPassword ? "text" : "password"}
                        name="password"
                        id="password"
                        placeholder={
                          showPassword ? "Enter your password" : "••••••••"
                        }
                        className="border outline-none text-sm rounded-lg block w-full p-2.5 bg-[#333333] border-white/20 placeholder-gray-400 text-white"
                        required
                      />
                      <span
                        onClick={() => setShowPassword((flag) => !flag)}
                        className="absolute cursor-pointer top-3 right-3"
                      >
                        {showPassword ? <FaRegEyeSlash /> : <FaRegEye />}
                      </span>
                    </div>
                    <ErrorMessage
                      className="text-red-400 text-xs"
                      name="password"
                      component="div"
                    />
                  </div>
                  <div>
                    <label
                      htmlFor="confirmPassword"
                      className="block mb-2 text-sm font-medium text-white"
                    >
                      Enter your password again, just to be sure
                    </label>

                    <div className="relative">
                      <Field
                        type={showConfirmPassword ? "text" : "password"}
                        name="confirmPassword"
                        id="confirmPassword"
                        placeholder={
                          showConfirmPassword
                            ? "Enter your password again"
                            : "••••••••"
                        }
                        className="border outline-none text-sm rounded-lg block w-full p-2.5 bg-[#333333] border-white/20 placeholder-gray-400 text-white"
                        required
                      />
                      <span
                        onClick={() => setShowConfirmPassword((flag) => !flag)}
                        className="absolute cursor-pointer top-3 right-3"
                      >
                        {showConfirmPassword ? <FaRegEyeSlash /> : <FaRegEye />}
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
                    className="w-full text-[#333333] disabled:text-gray-400 bg-white/90 active:bg-white/75 disabled:bg-white/10 font-medium rounded-lg px-5 py-2 text-center"
                  >
                    {isSubmitting ? <Spinner /> : "Register"}
                  </button>
                  <div
                    className="flex justify-center"
                    style={{ width: "100%", borderBottom: "2px solid white" }}
                  >
                    <p
                      style={{
                        position: "relative",
                        top: "13px",
                        backgroundColor: "#242526",
                      }}
                    >
                      OR
                    </p>
                  </div>
                  <div className="flex justify-center">
                    <GoogleLogin
                      onSuccess={handleCredentialResponse}
                      onError={(err) => {
                        console.log(err);
                      }}
                    />
                  </div>

                  <div>
                    <div className="text-sm font-medium flex items-center gap-1 text-gray-300">
                      Already have an account?
                      <span
                        onClick={() => {
                          setOpen(false);
                          setOpenLoginModal(true);
                        }}
                        className="cursor-pointer flex items-center gap-1 hover:underline space-x-1 text-blue-500"
                      >
                        Log in
                        <GoSignIn />
                      </span>
                    </div>
                    <div className="text-sm font-medium flex items-center gap-1 text-gray-300">
                      Looking to create a tribute page?
                      <Link className="flex items-center gap-1 hover:underline space-x-1 text-blue-500">
                        Start Now
                        <GoArrowUpRight />
                      </Link>
                    </div>
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
