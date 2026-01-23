import { configureStore } from "@reduxjs/toolkit";
import { useDispatch, useSelector } from "react-redux";
import userReducer from "./slices/userSlice.js";

export const store = configureStore({
  reducer: {
    user: userReducer,
  },
});

export const useAppDispatch = useDispatch;
export const useAppSelector = useSelector;
