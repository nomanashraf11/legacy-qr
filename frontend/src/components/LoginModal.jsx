import { useState } from "react";
import { ErrorMessage, Field, Form, Formik } from "formik";
import * as Yup from "yup";
import { useNavigate, useParams } from "react-router-dom";
import { GoArrowUpRight, GoSignIn } from "react-icons/go";
import { setUserToken, useAppDispatch } from "../redux";
import { googleLogin, loginAsync } from "../services";
import { Spinner } from ".";
import { toast } from "react-toastify";
import { IoClose } from "react-icons/io5";
import { FaRegEye, FaRegEyeSlash } from "react-icons/fa";
import { BASE_URL } from "../config";
import { ResendVerificationEmailModal } from "./ResendVerificationEmailModal";
import { GoogleLogin } from "@react-oauth/google";

export default GoogleLogin;

export const LoginModal = ({
  open,
  setOpen,
  setOpenRegisterModal,
  refetch,
  setOpenResetPasswordModal,
  hideCloseIcon,
  setOpenEmptyModal,
}) => {
  console.log("testing");
  const navigate = useNavigate();
  const dipatch = useAppDispatch();
  const { id } = useParams();

  const [showPassword, setShowPassword] = useState(false);
  const [openResendEmailModal, setOpenResendEmailModal] = useState(false);

  const validationSchema = Yup.object().shape({
    email: Yup.string().email("Invalid email").required("Email is required"),
    password: Yup.string().required("Password is required"),
  });
  const [error, setError] = useState(null);
  const [isLoading, setIsLoading] = useState(false);

  // const clientId =
  //   "747107467406-rv0eqtno390pnmk4164souiv8eqjgk37.apps.googleusercontent.com";
  // console.log("hello");
  // useEffect(() => {
  //   if (!clientId) {
  //     setError("Google Client ID is missing in environment variables");
  //     return;
  //   }

  //   const renderGoogleButton = () => {
  //     const buttonContainer = document.getElementById("googleButton");
  //     if (buttonContainer && !buttonContainer.hasChildNodes()) {
  //       window.google.accounts.id.initialize({
  //         use_fedcm_for_prompt: false,
  //         client_id: clientId,
  //         callback: handleCredentialResponse,
  //       });

  //       window.google.accounts.id.renderButton(buttonContainer, {
  //         theme: "outline",
  //         size: "large",
  //         width: 250,
  //       });

  //       window.google.accounts.id.prompt();
  //     }
  //   };

  //   const loadGoogleScript = () => {
  //     if (!window.google) {
  //       const script = document.createElement("script");
  //       script.src = "https://accounts.google.com/gsi/client";
  //       script.onload = renderGoogleButton;
  //       script.async = true;
  //       script.id = "google-client-script";
  //       document.querySelector("body").appendChild(script);
  //     } else {
  //       renderGoogleButton();
  //     }
  //   };

  //   const observer = new MutationObserver(() => {
  //     const modal = document.getElementById("loginModel"); // Replace with your modal's class or ID
  //     const buttonContainer = document.getElementById("googleButton");
  //     console.log(modal && buttonContainer);
  //     console.log(modal, buttonContainer);
  //     if (modal && buttonContainer && modal.contains(buttonContainer)) {
  //       renderGoogleButton();
  //     }
  //   });

  //   observer.observe(document.body, { childList: true, subtree: true });

  //   loadGoogleScript();

  //   return () => {
  //     observer.disconnect();
  //     const scriptTag = document.getElementById("google-client-script");
  //     if (scriptTag) {
  //       scriptTag.remove();
  //     }
  //   };
  // }, [clientId]);

  const handleCredentialResponse = async (response) => {
    try {
      setIsLoading(true);
      setError(null);
      console.log(response.credential);
      const data = await googleLogin({
        idToken: response.credential,
        qrCode: id,
      });

      if (data?.status === 200) {
        dipatch(setUserToken(data.data.data?.token));
        toast.success(data.message);
        if (!id) {
          navigate("/my-qrcodes");
        }
        refetch?.();
        setOpen(false);
        setOpenEmptyModal?.(false);
      } else if (data?.status == "400") {
        toast.error(data?.message);
      } else if (data?.status == "204" && data?.token) {
        dipatch(setUserToken(data?.token));
        toast.success("Login Successful");
        if (!id) {
          navigate("/my-qrcodes");
        }
        refetch?.();
        setOpen(false);
      } else if (data?.status == 201) {
        setOpenResendEmailModal(true);
      } else {
        toast.error(data?.message);
      }
    } catch (err) {
      console.error("Login error:", err);
      setError("Failed to authenticate with the server");
    } finally {
      setIsLoading(false);
    }
  };

  if (error) {
    return <div className="text-red-500">{error}</div>;
  }

  const handleSubmit = async (values, { setSubmitting, resetForm }) => {
    setSubmitting(true);
    try {
      const payload = {
        ...values,
        ...(id && { qr_code: id }),
      };
      const { data } = await loginAsync(payload);
      if (data.status === 200) {
        dipatch(setUserToken(data.data.token));
        toast.success(data.message);
        if (!id) {
          navigate("/my-qrcodes");
        }
        refetch?.();
        setOpen(false);
        setOpenEmptyModal?.(false);
      } else if (data.status == "204" && data.token) {
        dipatch(setUserToken(data.token));
        toast.success("Login Successful");
        if (!id) {
          navigate("/my-qrcodes");
        }
        refetch?.();
        setOpen(false);
        resetForm();
    } else if (data?.status == 201) {
        setOpenResendEmailModal(true);
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
      enableReinitialize
      validateOnChange
      initialValues={{ email: "", password: "" }}
      onSubmit={handleSubmit}
      validationSchema={validationSchema}
    >
      {({ isSubmitting, isValid, values }) => (
        <Form>
          <div
            id="loginModel"
            className={
              "overflow-y-auto bg-black backdrop-filter backdrop-blur-sm bg-opacity-50 overflow-x-hidden fixed top-0 right-0 left-0 bottom-0 max-h-full !z-50 flex justify-center items-center w-full md:inset-0 transition-all duration-200 ease-in-out " +
              (open ? "block" : "hidden")
            }
          >
            <div className="relative p-4 w-full max-w-[500px] max-h-full">
              <div className="relative bg-[#242526] rounded-lg shadow">
                <div className="flex relative items-center justify-center p-4 border-b border-white/5 rounded-t">
                  <h3 className="text-xl font-semibold text-center w-full">
                    Login to your account
                  </h3>

                  <div className="absolute top-0 right-0 left-0 bottom-0 flex items-center justify-end p-4">
                    {hideCloseIcon ? null : (
                      <button
                        onClick={() => setOpen(false)}
                        type="button"
                        className="text-gray-400 transition-all duration-200 ease-in-out bg-white bg-opacity-20 hover:bg-opacity-25 active:bg-opacity-30 rounded-full text-sm w-8 h-8 ms-auto inline-flex justify-center items-center"
                      >
                        <IoClose size={24} />
                      </button>
                    )}
                  </div>
                </div>

                <div className="p-4 md:p-5 space-y-4">
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
                      id="login-email"
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
                        id="login-password"
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

                  <div className="flex justify-between">
                    <div className="flex items-start">
                      <div className="flex items-center h-5">
                        <input
                          id="remember"
                          type="checkbox"
                          className="w-4 h-4 border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-blue-300 dark:bg-gray-600 dark:border-white/20 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800"
                          defaultChecked
                        />
                      </div>
                      <label
                        htmlFor="remember"
                        className="ms-2 text-sm font-medium text-gray-300"
                      >
                        Remember me
                      </label>
                    </div>
                    <span
                      onClick={() => {
                        setOpen(false);
                        setOpenResetPasswordModal(true);
                      }}
                      className="text-sm hover:underline hover:cursor-pointer text-blue-500"
                    >
                      Lost Password?
                    </span>
                  </div>
                  <button
                    disabled={!isValid || isSubmitting}
                    type="submit"
                    className="w-full text-[#333333] bg-white/90 active:bg-white/75 disabled:text-gray-400 disabled:bg-white/10 font-medium rounded-lg px-5 py-2 text-center"
                  >
                    {isSubmitting ? <Spinner /> : "Login"}
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
                  <div className="flex justify-center items-center">
                    <GoogleLogin
                      onSuccess={handleCredentialResponse}
                      onError={(err) => {
                        console.log(err);
                      }}
                    />
                  </div>

                  {isLoading && <div>Loading...</div>}
                  <div>
                    {setOpenRegisterModal && (
                      <div className="text-sm font-medium flex items-center gap-1 text-gray-300">
                        Don't have an account?
                        <span
                          onClick={() => {
                            setOpen(false);
                            setOpenRegisterModal(true);
                          }}
                          className="cursor-pointer flex items-center gap-1 hover:underline space-x-1 text-blue-500"
                        >
                          Register Now
                          <GoSignIn className="rotate-180" />
                        </span>
                      </div>
                    )}

                    <div className="text-sm font-medium flex items-center gap-1 text-gray-300">
                      Looking to create a tribute page?
                      <a
                        href={BASE_URL}
                        target="_blank"
                        className="flex items-center gap-1 hover:underline space-x-1 text-blue-500"
                        rel="noreferrer"
                      >
                        Start Now
                        <GoArrowUpRight />
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <ResendVerificationEmailModal
            email={values.email}
            open={openResendEmailModal}
            setOpen={setOpenResendEmailModal}
          />
        </Form>
      )}
    </Formik>
  );
};
