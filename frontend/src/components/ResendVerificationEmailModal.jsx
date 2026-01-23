import { useMutation } from '@tanstack/react-query';
import { IoClose } from 'react-icons/io5';
import { Spinner } from './Spinner';
import { toast } from 'react-toastify';
import { sendOTPRequestAsync } from '../services';

export const ResendVerificationEmailModal = ({ open, setOpen, email }) => {
  const { mutateAsync, isPending } = useMutation({ mutationFn: sendOTPRequestAsync });

  const handleSubmit = async () => {
    try {
      const { data } = await mutateAsync({ email });
      if (data.status === 200) {
        toast.info('Please verify your email, check your email for verification details.');
        setOpen(false);
      } else {
        toast.error(data.message);
      }
    } catch (error) {
      console.log(error);
    }
  };

  return (
    <div
      className={
        'overflow-y-auto bg-black backdrop-filter backdrop-blur-sm bg-opacity-50 overflow-x-hidden fixed top-0 right-0 left-0 bottom-0 max-h-full !z-50 flex justify-center items-center w-full md:inset-0 transition-all duration-200 ease-in-out ' +
        (open ? 'block' : 'hidden')
      }
    >
      <div className='relative p-4 w-full max-w-[500px] max-h-full'>
        <div className='relative bg-[#242526] rounded-lg shadow'>
          <div className='flex relative items-center justify-center p-4 pt-6 rounded-t'>
            <div className='absolute top-3 right-0 left-0 bottom-0 flex items-center justify-end p-4'>
              <button
                onClick={() => setOpen(false)}
                type='button'
                className='text-gray-400 transition-all duration-200 ease-in-out bg-white bg-opacity-20 hover:bg-opacity-25 active:bg-opacity-30 rounded-full text-sm w-8 h-8 ms-auto inline-flex justify-center items-center'
              >
                <IoClose size={24} />
              </button>
            </div>
          </div>
          <div className='p-4 md:p-5 space-y-4'>
            <div>
              <p className='text-center'>
                You need to verify your account to continue. If you did not receive a verification email, click below.
              </p>
            </div>
            <div className='pt-6 flex items-center justify-center'>
              <button
                onClick={handleSubmit}
                disabled={isPending}
                type='button'
                className='py-2.5 flex items-center justify-center gap-3 select-none px-6 mb-2 text-sm font-medium rounded-lg active:opacity-60 disabled:pointer-events-none disabled:opacity-40 bg-neutral-700 text-neutral-400 hover:text-white hover:bg-opacity-60 transition-all duration-200 ease-in-out'
              >
                {isPending && <Spinner />} {'Request Verification Email'}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
