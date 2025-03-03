import axios from "axios";

const API_BASE_URL = "http://localhost/manufacturer_saas/backend/routes/";  // Use full URL

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    "Content-Type": "application/json",
  },
  withCredentials: false,
});

export const loginUser = async (email, password) => {
  try {
    const response = await api.post("/login.php", { email, password });
    return response.data;
  } catch (error) {
    console.error("Login Error:", error);
    return { success: false, message: "Login failed. Please try again." };
  }
};

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response && error.response.status === 401) {
      localStorage.clear();
      window.location.href = "/login"; // Force logout
    }
    return Promise.reject(error);
  }
);


export default api;
