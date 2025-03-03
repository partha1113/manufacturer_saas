import { useContext } from "react";
import { AuthContext } from "../context/AuthContext";
import { Navigate } from "react-router-dom";

function PrivateRoute({ children, allowedRoles }) {
  const { user } = useContext(AuthContext);

  // Redirect to login if user is not authenticated
  if (!user) {
    return <Navigate to="/" />;
  }

  // Check if user's role is allowed to access this route
  if (allowedRoles && !allowedRoles.includes(user.role)) {
    return <Navigate to="/" />; // Redirect unauthorized users
  }

  return children;
}

export default PrivateRoute;
