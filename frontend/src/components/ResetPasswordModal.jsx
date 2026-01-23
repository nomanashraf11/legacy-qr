import React, { useState } from 'react';
import { ErrorMessage, Field, Form, Formik } from 'formik';
import * as Yup from 'yup';
import { IoClose } from 'react-icons/io5';
import { forgotPasswordAsync, resetPasswordAsync } from '../services';
import { Spinner } from '.';
import { toast } from 'react-toastify';
import { getAuthToken, useAppSelector } from '../redux/index.js';
import { regex } from '../utils';

export const ResetPasswordModal = ({ open, setOpen, setOpenLoginModal, setOpenRegisterModal }) => {
  const token = useAppSelector(getAuthToken);
  const [emailSent, setEmailSent] = useState(false);

  const initialValues = {
    email: '',
    newPassword: '',
    confirmPassword: '',
    otp: '',
  };

  const validationSchema = Yup.object().shape({
    email: Yup.string().email('Invalid email').required('Email is required'),
    newPassword: emailSent ? Yup.string().required('New Password is required') : Yup.string(),
    confirmPassword: emailSent
      ? Yup.string()
          .oneOf([Yup.ref('newPassword'), null], 'Passwords must match')
          .matches(
            regex.password,
            'Password must be at least 8 characters long and include at least 1 digit, 1 lowercase letter, and 1 uppercase letter.'
          )
          .required('Confirm Password is required')
      : Yup.string(),
    otp: emailSent ? Yup.string().required('OTP is required') : Yup.string(),
  });

  const handleSubmit = async (values, { setSubmitting, resetForm }) => {
    setSubmitting(true);
    try {
      if (emailSent) {
        const payload = {
          email: values.email,
          password: values.newPassword,
          otp: values.otp,
        };
        const { data } = await resetPasswordAsync(payload);

        if (data.status === 200) {
          toast.success(data.message, {
            autoClose: 8000,
          });
          setOpen(false);
          setOpenLoginModal(true);
        } else {
          toast.error(data.message, {
            autoClose: 8000,
          });
        }
      } else {
        const { data } = await forgotPasswordAsync({ email: values.email });
        if (data.status === 200) {
          toast.success('Verification Code sent to your email address. Please check your email and enter the Verification Code.', {
            autoClose: 8000,
          });
          setEmailSent(true);
        } else {
          toast.error(data.message);
        }
      }
    } catch (error) {
      console.log(error);
    } finally {
      setSubmitting(false);
      if (emailSent) {
        resetForm();
      }
    }
  };

  const handleClose = () => {
    setOpen(false);
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
      {({ resetForm, isSubmitting, isValid }) => (
        <Form>
          <div
            className={
              'overflow-y-auto bg-black backdrop-filter backdrop-blur-sm bg-opacity-50 overflow-x-hidden fixed top-0 right-0 left-0 bottom-0 max-h-full !z-50 flex justify-center items-center w-full md:inset-0 transition-all duration-200 ease-in-out ' +
              (open ? 'block' : 'hidden')
            }
          >
            <div className='relative p-4 w-full max-w-[500px] max-h-full'>
              <div className='relative bg-[#242526] rounded-lg shadow'>
                <div className='flex items-center justify-center p-4 border-b relative border-white/5 rounded-t'>
                  <h3 className='text-xl font-semibold text-center w-full'>Reset Password</h3>
                  <div className='absolute top-0 right-0 left-0 bottom-0 flex items-center justify-end p-4'>
                    <button
                      onClick={() => {
                        handleClose();
                        resetForm();
                        if (!token) {
                          setOpenLoginModal(true);
                        }
                      }}
                      type='button'
                      className='text-gray-400 transition-all duration-200 ease-in-out bg-white bg-opacity-20 hover:bg-opacity-25 active:bg-opacity-30 rounded-full text-sm w-8 h-8 ms-auto inline-flex justify-center items-center'
                    >
                      <IoClose size={24} />
                      <span className='sr-only'>Close modal</span>
                    </button>
                  </div>
                </div>
                <div className='p-4 md:p-5 space-y-4'>
                  <div>
                    <label htmlFor='email' className='block mb-2 text-sm font-medium text-white'>
                      Your email
                    </label>
                    <Field
                      type='email'
                      disabled={emailSent}
                      name='email'
                      id='reset-email'
                      className='border outline-none text-sm rounded-lg block w-full p-2.5 bg-[#333333] border-white/20 placeholder-gray-400 text-white'
                      placeholder='name@company.com'
                      required
                    />
                    <ErrorMessage className='text-red-400 text-xs' name='email' component='div' />
                  </div>
                  {emailSent && (
                    <>
                      <div>
                        <label htmlFor='newPassword' className='block mb-2 text-sm font-medium text-white'>
                          New Password
                        </label>
                        <Field
                          type='password'
                          name='newPassword'
                          id='reset-newPassword'
                          placeholder='••••••••'
                          className='border outline-none text-sm rounded-lg block w-full p-2.5 bg-[#333333] border-white/20 placeholder-gray-400 text-white'
                          required
                        />
                        <ErrorMessage className='text-red-400 text-xs' name='newPassword' component='div' />
                      </div>
                      <div>
                        <label htmlFor='confirmPassword' className='block mb-2 text-sm font-medium text-white'>
                          Confirm Password
                        </label>
                        <Field
                          type='password'
                          name='confirmPassword'
                          id='reset-confirmPassword'
                          placeholder='••••••••'
                          className='border outline-none text-sm rounded-lg block w-full p-2.5 bg-[#333333] border-white/20 placeholder-gray-400 text-white'
                          required
                        />
                        <ErrorMessage className='text-red-400 text-xs' name='confirmPassword' component='div' />
                      </div>
                      <div>
                        <label htmlFor='otp' className='block mb-2 text-sm font-medium text-white'>
                          Verification Code
                        </label>
                        <Field
                          type='text'
                          name='otp'
                          id='reset-otp'
                          className='border outline-none text-sm rounded-lg block w-full p-2.5 bg-[#333333] border-white/20 placeholder-gray-400 text-white'
                          required
                        />
                        <ErrorMessage className='text-red-400 text-xs' name='otp' component='div' />
                      </div>
                    </>
                  )}
                  <button
                    disabled={!isValid || isSubmitting}
                    type='submit'
                    className='w-full disabled:text-gray-400 text-[#333333] bg-white/90 active:bg-white/75 disabled:bg-white/10 font-medium rounded-lg px-5 py-2 text-center'
                  >
                    {isSubmitting ? <Spinner /> : emailSent ? 'Reset Password' : 'Forgot Password'}
                  </button>
                  <div>
                    <p
                      onClick={() => {
                        setOpen(false);
                        setOpenLoginModal(true);
                      }}
                      className='text-white/60 cursor-pointer select-none hover:text-white/90 transition-all duration-300 ease-in-out'
                    >
                      I want to Login.
                    </p>
                    <p
                      onClick={() => {
                        setOpen(false);
                        setOpenRegisterModal(true);
                      }}
                      className='text-white/60 cursor-pointer select-none hover:text-white/90 transition-all duration-300 ease-in-out'
                    >
                      I want to register a new account.
                    </p>
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
