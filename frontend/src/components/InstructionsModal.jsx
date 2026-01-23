import { CiViewTimeline } from 'react-icons/ci';
import { MdDone } from 'react-icons/md';
import { GrGallery } from 'react-icons/gr';
import { PiTree } from 'react-icons/pi';
import { getRemembered, setRemembered, useAppDispatch, useAppSelector } from '../redux';
import { useNavigate, useParams } from 'react-router-dom';

export const InstructionsModal = ({ setOpenTimlineModal, open, setOpen, setOpenUploadPhotosModal, setOpenFamilyTreeModal }) => {
  const { id } = useParams();
  const navigate = useNavigate();
  const remember = useAppSelector(getRemembered);
  const dispatch = useAppDispatch();

  const handleClose = () => {
    if (remember) {
      localStorage.setItem('remember', true);
    }
    setOpen(false);
  };
  return (
    <div
      className={
        'bg-black backdrop-filter backdrop-blur-sm bg-opacity-60 overflow-x-hidden fixed top-0 right-0 left-0 bottom-0 max-h-full !z-50 flex justify-center items-center w-full md:inset-0 transition-all duration-200 ease-in-out ' +
        (open ? 'block' : 'hidden')
      }
    >
      <div className='relative p-4 w-full max-w-[400px] sm:max-w-2xl'>
        <div className='bg-[#242526] rounded-lg shadow'>
          <div className='p-4 md:p-6'>
            <div className='flex items-center justify-center'>
              <MdDone className='inline-block fill-green-500 text-5xl sm:text-7xl' />
            </div>
            <p className='text-center'>Congratulations! Your profile has been updated successfully.</p>
            <p className='text-center mt-7 text-lg uppercase font-normal opacity-75'>Next Steps</p>
            <div className='max-w-[400px] grid grid-cols-3 gap-4 mx-auto mt-6'>
              <button
                onClick={() => {
                  setOpen(false);
                  navigate('/' + id + '/gallery');
                  setOpenUploadPhotosModal(true);
                }}
                className='border active:scale-95 transition-all duration-200 ease-in-out border-neutral-700 text-white/70 md:hover:bg-neutral-900/30 w-full flex flex-col justify-center items-center gap-3 p-2 rounded-lg '
              >
                <GrGallery className='w-4 h-4' />
                <span>Gallery</span>
              </button>
              <button
                onClick={() => {
                  setOpen(false);
                  navigate('/' + id + '/family-tree');
                  setOpenFamilyTreeModal(true);
                }}
                className='border active:scale-95 transition-all duration-200 ease-in-out border-neutral-700 text-white/70 md:hover:bg-neutral-900/30 w-full flex flex-col justify-center items-center gap-3 p-2 rounded-lg '
              >
                <PiTree className='w-5 h-5' />

                <span>Family Tree</span>
              </button>
              <button
                onClick={() => {
                  setOpen(false);
                  navigate('/' + id + '/timeline');
                  setOpenTimlineModal(true);
                }}
                className='border active:scale-95 transition-all duration-200 ease-in-out border-neutral-700 text-white/70 md:hover:bg-neutral-900/30 w-full flex flex-col justify-center items-center gap-3 p-2 rounded-lg '
              >
                <CiViewTimeline className='w-5 h-5' />
                <span>Timeline</span>
              </button>
            </div>
          </div>
          <div className={'pb-6 mt-4'}>
            <div className='max-w-[200px] mx-auto flex flex-col items-center'>
              <label className='flex items-center gap-3 select-none'>
                <input
                  checked={remember}
                  type='checkbox'
                  className='w-4 h-4 border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-blue-300 dark:bg-gray-600 dark:border-white/20 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800'
                  onChange={(e) => {
                    dispatch(setRemembered(e.target.checked));
                  }}
                />
                <span className='text-sm font-medium text-white opacity-60'>Never show this again</span>
              </label>
              <button
                onClick={handleClose}
                className='border border-neutral-700 active:scale-95 transition-all duration-200 ease-in-out text-white/70 md:hover:bg-neutral-900/30 mt-4 w-full p-2 rounded-full '
              >
                Close
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
