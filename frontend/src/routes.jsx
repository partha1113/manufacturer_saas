import { BrowserRouter as Router, Routes, Route } from "react-router-dom";
import Login from "./pages/auth/Login";
import SuperAdminDashboard from "./pages/super_admin/SuperAdminDashboard";
import ManufacturerDashboard from "./pages/tenant/ManufacturerDashboard"; 
import DistributorDashboard from "./pages/distributor/DistributorDashboard";
import CustomerDashboard from "./pages/customer/CustomerDashboard";
import CustomerOrders from "./pages/customer/CustomerOrders";
import CustomerComplaints from "./pages/customer/CustomerComplaints";
import CustomerProfile from "./pages/customer/CustomerProfile";
import ManageTenants from "./pages/super_admin/ManageTenants";
import Products from "./pages/tenant/Products";
import Orders from "./pages/tenant/Orders";
import PrivateRoute from "./routes/PrivateRoute";
import { AuthProvider } from "./context/AuthContext";

function AppRoutes() {
  return (
    <AuthProvider>
      <Router>
        <Routes>
          <Route path="/" element={<Login />} />
          <Route
            path="/super-admin"
            element={
              <PrivateRoute allowedRoles={["super_admin"]}>
                <SuperAdminDashboard />
              </PrivateRoute>
            }
          />
          <Route
            path="/super-admin/tenants"
            element={
              <PrivateRoute allowedRoles={["super_admin"]}>
                <ManageTenants />
              </PrivateRoute>
            }
          />
          <Route
            path="/tenant"
            element={
              <PrivateRoute allowedRoles={["tenant"]}>
                <ManufacturerDashboard />
              </PrivateRoute>
            }
          />
          <Route
            path="/manufacturer/products"
            element={
              <PrivateRoute>
                <Products />
              </PrivateRoute>
            }
          />
            <Route
            path="/manufacturer/orders"
            element={
              <PrivateRoute>
                <Orders />
              </PrivateRoute>
            }
          />
          <Route
            path="/distributor"
            element={
              <PrivateRoute allowedRoles={["distributor"]}>
                <DistributorDashboard />
              </PrivateRoute>
            }
          />
           <Route
            path="/distributor/orders"
            element={
              <PrivateRoute>
                <Orders />
              </PrivateRoute>
            }
          />
          <Route
            path="/customer"
            element={
              <PrivateRoute allowedRoles={["customer"]}>
                <CustomerDashboard />
              </PrivateRoute>
            }
          />
          <Route
            path="/customer/orders"
            element={
              <PrivateRoute>
                <CustomerOrders />
              </PrivateRoute>
            }
          />
          <Route
            path="/customer/complaints"
            element={
              <PrivateRoute>
                <CustomerComplaints />
              </PrivateRoute>
            }
          />
          <Route
            path="/customer/profile"
            element={
              <PrivateRoute>
                <CustomerProfile />
              </PrivateRoute>
            }
          />
        </Routes>
      </Router>
    </AuthProvider>
  );
}

export default AppRoutes;






