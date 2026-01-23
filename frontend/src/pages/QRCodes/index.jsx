import { useEffect, useState } from "react";
import { useFetchQrCodesData } from "../../services";
import { LoginModal, ResetPasswordModal, Wrapper } from "../../components";
import {
  getAuthToken,
  removeUserToken,
  useAppDispatch,
  useAppSelector,
} from "../../redux";
import { IoIosMenu } from "react-icons/io";
import { Card } from "./includes/Card";
import { useQueryClient } from "@tanstack/react-query";
import { request } from "../../api";
import { ChangePasswordModal } from "../../components/ChangePasswordModal.jsx";

export const QrCodes = () => {
  const dispatch = useAppDispatch();
  const token = useAppSelector(getAuthToken);
  const queryClient = useQueryClient();

  const [openLoginModal, setOpenLoginModal] = useState(false);
  const [openRegisterModal, setOpenRegisterModal] = useState(false);
  const [openResetPasswordModal, setOpenResetPasswordModal] = useState(false);
  const [openChangePasswordModal, setOpenChangePasswordModal] = useState(false);

  const { data, refetch, isLoading } = useFetchQrCodesData();

  const logout = () => {
    dispatch(removeUserToken());
    setOpenLoginModal(true);
    queryClient.clear();
  };

  request.interceptors.response.use(
    (response) => {
      if (response.data && response.data.status === 401) {
        logout();
      }
      return response;
    },
    (error) => {
      if (error.response && error.response.status === 401) {
        logout();
      }
      return Promise.reject(error);
    },
  );

  useEffect(() => {
    if (!data) {
      refetch();
    }
  }, [token]);

  return (
    <Wrapper isLoading={isLoading}>
      <div className="flex flex-col items-center pb-10">
        <div className="w-full max-w-5xl p-6 pb-10">
          <div className="w-full flex items-center justify-between">
            <h1 className="text-4xl my-10">My QR Codes </h1>
            <div className="group relative flex items-center justify-center h-full transition-all duration-150 ease-in-out">
              <span className="py-2 pr-2 cursor-pointer h-full">
                <IoIosMenu size={24} />
              </span>

              <div
                id="dropdownHover"
                className={
                  "z-10 absolute top-5 right-4 group-hover:block hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-44"
                }
              >
                <ul
                  className="py-2 text-gray-700"
                  aria-labelledby="dropdownHoverButton"
                >
                  {token && (
                    <li onClick={() => setOpenChangePasswordModal(true)}>
                      <span className="block px-4 py-2 select-none cursor-pointer hover:bg-gray-100">
                        Change Password
                      </span>
                    </li>
                  )}
                  {token ? (
                    <li>
                      <span
                        onClick={logout}
                        className="block px-4 py-2 select-none cursor-pointer hover:bg-gray-100"
                      >
                        Logout
                      </span>
                    </li>
                  ) : (
                    <li>
                      <span
                        onClick={() => setOpenLoginModal(true)}
                        className="block px-4 py-2 select-none cursor-pointer hover:bg-gray-100"
                      >
                        Login
                      </span>
                    </li>
                  )}
                </ul>
              </div>
            </div>
          </div>

          {/* Cards */}

          <div className="grid md:grid-cols-3 gap-4 md:gap-6">
            {data?.data?.map((item) => (
              <Card key={item.uuid} qrData={item} />
            ))}
          </div>
        </div>
      </div>
      <LoginModal
        hideCloseIcon={token ? false : true}
        refetch={refetch}
        setOpenResetPasswordModal={setOpenResetPasswordModal}
        open={openLoginModal}
        setOpen={setOpenLoginModal}
      />
      <ResetPasswordModal
        setOpenLoginModal={setOpenLoginModal}
        open={openResetPasswordModal}
        setOpenRegisterModal={setOpenRegisterModal}
        setOpen={setOpenResetPasswordModal}
      />
      <ChangePasswordModal
        open={openChangePasswordModal}
        setOpen={setOpenChangePasswordModal}
      />
    </Wrapper>
  );
};
