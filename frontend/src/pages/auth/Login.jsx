import { useState, useContext, useEffect } from "react";
import { AuthContext } from "../../context/AuthContext";
import api from "../../services/api";
import { useNavigate } from "react-router-dom";

const API_BASE_URL = "http://localhost/manufacturer_saas/backend/routes";

function Login() {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const { user, login } = useContext(AuthContext);
  const navigate = useNavigate();
  const [loading, setLoading] = useState(false);

  // ✅ Prevent logged-in users from accessing the login page
  useEffect(() => {
    if (user) {
      switch (user.role) {
        case "super_admin":
          navigate("/super-admin", { replace: true });
          break;
        case "tenant":
          navigate("/tenant", { replace: true });
          break;
        case "distributor":
          navigate("/distributor", { replace: true });
          break;
        case "customer":
          navigate("/customer", { replace: true });
          break;
        default:
          break;
      }
    }
  }, [user, navigate]);

  const handleLogin = async (e) => {
    e.preventDefault();
    setError(""); // Clear previous errors

    if (!email || !password) {
      setError("Email and Password are required.");
      return;
    }

    setLoading(true); // Start loading
    try {
      const response = await api.post(`${API_BASE_URL}/api.php?routes=login`, { email, password });

      console.log("Login response:", response.data); // Debugging

      if (!response?.data || typeof response.data !== "object") {
        throw new Error("Invalid response format from server.");
      }

      const { user, token } = response.data;

      if (!user || !user.role || !token) {
        throw new Error("User data or token is missing from response.");
      }

      login(user, token);

      // ✅ Navigate based on role
      switch (user.role) {
        case "super_admin":
          navigate("/super-admin", { replace: true });
          break;
        case "tenant":
          navigate("/tenant", { replace: true });
          break;
        case "distributor":
          navigate("/distributor", { replace: true });
          break;
        case "customer":
          navigate("/customer", { replace: true });
          break;
        default:
          throw new Error("Unknown role, unable to navigate.");
      }
    } catch (error) {
      console.error("Login failed:", error.response?.data || error.message);
      setError(error.response?.data?.message || "Login failed. Please try again.");
    } finally {
    setLoading(false); // Stop loading
  }
  };

  return (
    <div className="flex h-screen items-center justify-center bg-gray-100">
      <div className="bg-white p-6 rounded-2xl shadow-xl w-96">
        <h2 className="text-2xl font-bold text-center mb-4">Login</h2>

        {error && <p className="text-red-500 text-center">{error}</p>}

        <input
          type="email"
          placeholder="Email"
          className="w-full p-2 border rounded-md mb-2"
          onChange={(e) => setEmail(e.target.value)}
          value={email}
        />
        <input
          type="password"
          placeholder="Password"
          className="w-full p-2 border rounded-md mb-4"
          onChange={(e) => setPassword(e.target.value)}
          value={password}
        />
        <button
          onClick={handleLogin}
          className={`w-full p-2 rounded-md ${loading ? "bg-gray-400" : "bg-blue-500 hover:bg-blue-600"} text-white`}
          disabled={loading}
        >
          {loading ? "Logging in..." : "Login"}
        </button>
      </div>
    </div>
  );
}

export default Login;
