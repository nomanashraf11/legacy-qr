import { FaCircleExclamation } from 'react-icons/fa6';

export const EmptyData = ({ message }) => {
  return (
    <div className='flex flex-col items-center justify-center gap-6 text-gray-600 text-lg h-60'>
      <FaCircleExclamation className='inline-block fill-gray-400 w-8 h-8' />
      <p>{message}</p>
    </div>
  );
};
