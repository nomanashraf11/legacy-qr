import Cropper from 'react-easy-crop';
import { useCallback, useState } from 'react';
import getCroppedImg from '../utils/cropImage';
import Slider from 'react-range-slider-input';
import 'react-range-slider-input/dist/style.css';
import { IoClose } from 'react-icons/io5';
import { useMutation } from '@tanstack/react-query';

export function ImageCropModal({ open, imageURL, setOpen, setCroppedImage, ratio, isAvatar, name }) {
  const [crop, setCrop] = useState({ x: 0, y: 0 });
  const [zoom, setZoom] = useState([0, 1]);

  const { mutate, isPending } = useMutation({ mutationFn: (croppedAreaPixels) => getCroppedImg(imageURL, croppedAreaPixels, name) });

  const [croppedAreaPixels, setCroppedAreaPixels] = useState(null);

  const handleCropCancel = () => {
    setOpen(false);
  };

  const onCropComplete = useCallback((croppedArea, croppedAreaPixels) => {
    setCroppedAreaPixels(croppedAreaPixels);
  }, []);

  const showCroppedImage = useCallback(async () => {
    mutate(croppedAreaPixels, {
      onSuccess(res) {
        setCroppedImage(res);
        handleCropCancel();
      },
      onError(err) {
        console.log(err);
      },
    });
  }, [croppedAreaPixels]);
  return (
    <div
      className={
        'bg-black backdrop-filter backdrop-blur-sm bg-opacity-60 overflow-x-hidden fixed top-0 right-0 left-0 bottom-0 max-h-full !z-50 flex justify-center items-center w-full md:inset-0 ' +
        (open ? 'block' : 'hidden')
      }
    >
      <div className='relative p-4 w-full max-w-[500px]  max-h-full'>
        <div className='relative bg-[#242526] rounded-lg shadow'>
          <div className='flex items-center justify-center p-4 border-b relative border-white/5 rounded-t'>
            <h3 className='text-xl font-semibold text-center w-full'>Crop Image</h3>
            <div className='absolute top-0 right-0 left-0 bottom-0 flex items-center justify-end p-4'>
              <button
                onClick={handleCropCancel}
                type='button'
                className='text-gray-400 transition-all duration-200 ease-in-out bg-white bg-opacity-20 hover:bg-opacity-25 active:bg-opacity-30 rounded-full text-sm w-8 h-8 ms-auto inline-flex justify-center items-center'
              >
                <IoClose size={24} />
              </button>
            </div>
          </div>
          <div className='p-4 md:p-6'>
            <div className={'relative h-[150px] sm:h-[300px] w-full'}>
              <Cropper
                image={imageURL}
                crop={crop}
                zoom={zoom[1]}
                aspect={ratio === 'dp' ? 1 / 1 : 16 / 9}
                style={{
                  cropAreaStyle: {
                    borderRadius: isAvatar ? '100%' : 0,
                  },
                }}
                onCropChange={setCrop}
                onCropComplete={onCropComplete}
                onZoomChange={(z) => setZoom((prev) => [prev[0], z])}
              />
            </div>
            <div className='[&_.range-slider]:h-1'>
              <Slider className='single-thumb' min={1} max={3} step={0.1} value={zoom} onInput={setZoom} thumbsDisabled={[true, false]} rangeSlideDisabled={true} />
            </div>
            <div className='mt-6 flex items-center justify-end gap-4'>
              <button
                type='button'
                className='w-[150px] flex items-center select-none justify-center rounded-full active:scale-[0.95] hover:bg-white/20 hover:border-transparent transition-all duration-150 ease-in-out border p-2 border-white/20'
                onClick={handleCropCancel}
              >
                Cancel
              </button>
              <button
                type='button'
                className='w-[150px] select-none flex items-center justify-center bg-white/20 p-2 active:scale-[0.95] disabled:scale-100 disabled:opacity-50 rounded-full transition-all duration-200 ease-in-out hover:bg-white/10'
                onClick={showCroppedImage}
              >
                {isPending ? 'Cropping...' : 'Save'}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
