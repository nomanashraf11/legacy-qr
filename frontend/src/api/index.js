import axios from "axios";
import { API_BASE_URL } from "../config";
console.log(API_BASE_URL);
const instance = axios.create({
  baseURL: API_BASE_URL,
  headers: { "Content-Type": "application/json" },
});

instance.interceptors.request.use(
  async (config) => {
    console.log("Request URL: From interceptor", config.baseURL + config.url);
    const token = localStorage.getItem("living-token");
    if (token) {
      config.headers["Authorization"] = "Bearer " + token;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Used with the auth token
export const request = instance;
