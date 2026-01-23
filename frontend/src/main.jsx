import { StrictMode } from "react";
import ReactDOM from "react-dom/client";
import App from "./App.jsx";
import "./index.css";
import { ToastContainer } from "react-toastify";
import "react-toastify/dist/ReactToastify.css";
import { GoogleOAuthProvider } from "@react-oauth/google";

ReactDOM.createRoot(document.getElementById("root")).render(
  <GoogleOAuthProvider clientId="991528837111-j1q87bulk26nop7944g0p2fihriiq147.apps.googleusercontent.com">
    <StrictMode>
      <App />
      <ToastContainer
        position="bottom-right"
        closeOnClick
        autoClose={5000}
        draggable
        theme="dark"
      />
    </StrictMode>
  </GoogleOAuthProvider>
);
