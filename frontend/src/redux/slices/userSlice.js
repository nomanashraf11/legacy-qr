import { createSlice } from "@reduxjs/toolkit";

function getInitialState() {
  let initialState = {
    id: "",
    token: "",
    spotifyToken: "",
    data: undefined,
    remember: false,
    spouseOrbit: "main",
  };
  try {
    const token = localStorage.getItem("living-token");
    const spotifyToken = localStorage.getItem("living-spotify-token");
    const remembered = localStorage.getItem("remember");

    if (token !== null) {
      initialState.token = token;
      initialState.spotifyToken = spotifyToken;
    }
    initialState.remember =
      remembered === "true" || remembered === true ? true : false;
  } catch (e) {
    console.warn(e);
  }
  console.log(initialState);
  return initialState;
}

export const userSlice = createSlice({
  name: "user",
  initialState: getInitialState(),
  reducers: {
    setUserToken: (state, action) => {
      state.token = action.payload;
      localStorage.setItem("living-token", action.payload);
    },
    setSpotifyToken: (state, action) => {
      state.spotifyToken = action.payload;
      localStorage.setItem("living-spotify-token", action.payload);
    },
    setUserData: (state, action) => {
      state.data = action.payload;
    },
    setID: (state, action) => {
      state.id = action.payload;
    },
    changeOrbit: (state, action) => {
      state.spouseOrbit = action.payload;
    },
    removeUserToken: (state) => {
      state.token = "";
      localStorage.clear();
    },
    setRemembered: (state, action) => {
      state.remember = action.payload;
    },
  },
});

export const {
  setUserToken,
  setUserData,
  setRemembered,
  setID,
  changeOrbit,
  removeUserToken,
  setSpotifyToken,
} = userSlice.actions;

export const getAuthToken = (state) => state.user.token;
export const getSpotifyToken = (state) => state.user.spotifyToken;
export const getUserData = (state) => state.user.data;
export const getRemembered = (state) => state.user.remember;
export const getID = (state) => state.user.id;
export const getOrbitStatus = (state) => {
  return state?.user?.spouseOrbit ? state?.user?.spouseOrbit : "main";
};

export default userSlice.reducer;
