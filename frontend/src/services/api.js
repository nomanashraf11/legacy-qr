import { request } from "../api";

export const loginAsync = (payload) => {
  return request.post("/signin", payload);
};
export const googleLogin = (payload) => {
  return request.post("/signin/google", payload);
};

export const registerAsync = (payload) => {
  return request.post("/signup", payload);
};

export const addTimelineAsync = (uuid, payload) => {
  return request.post(`/${uuid}/add_timeline`, payload);
};

export const updateTimelineAsync = (uuid, payload) => {
  return request.post(`/${uuid}/update_timeline`, payload);
};

export const deleteTimelineAsync = (uuid) => {
  return request.post(`/${uuid}/delete_timeline`);
};

export const addTributeAsync = (uuid, payload) => {
  return request.post(`/${uuid}/add_tribute`, payload);
};

export const deleteTributeAsync = (uuid) => {
  return request.post(`/${uuid}/delete_tribute`);
};

export const deletePhotoAsync = (uuid) => {
  return request.post("/delete_photo/" + uuid);
};

export const updatePhotoVideoCaptionAsync = (uuid, payload) => {
  return request.post("/edit/photo/" + uuid, payload);
};

export const resetPasswordAsync = (payload) => {
  return request.post("/reset-password-api", payload);
};

export const removeRelationAsync = (id) => {
  return request.post("/remove-relation/" + id);
};

export const forgotPasswordAsync = (payload) => {
  return request.post("/forgot-password-api", payload);
};

export const changePasswordAsync = (payload) => {
  return request.post("/changePassword", payload);
};

export const sendOTPRequestAsync = (payload) => {
  return request.post("/email/resend-api", payload);
};

export const verifyOTPRequestAsync = (payload) => {
  return request.post("/email/verify-api", payload);
};

export const connectUserToLinkAsync = (id) => {
  return request.post("/link/" + id);
};

export const getTributesData = (id) => {
  return request.get(id);
};

export const getMyQrCodes = () => {
  return request.get("my_qr_codes");
};

export const deleteRelationImage = (name) => {
  return request.post("/delete-relation-photo", {
    name,
  });
};
