import { useEffect, useState } from "react";
import Sidebar from "../../components/Sidebar";
import api from "../../services/api"; // Assuming you have API setup

const SuperAdminDashboard = () => {
  const [stats, setStats] = useState({
    totalTenants: 0,
    activeTenants: 0,
    pendingComplaints: 0,
  });

  const [tenants, setTenants] = useState([]);

  useEffect(() => {
    const fetchStats = async () => {
      try {
        const token = localStorage.getItem("token"); // Retrieve token from localStorage
        if (!token) {
          console.error("No token found. User may not be logged in.");
          return;
        }

        const response = await api.get("/super-admin/stats", {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });

        console.log("Stats Response:", response.data); // Debugging response

        // Ensure API response keys match state keys
        setStats({
          totalTenants: response.data.total_tenants || 0,
          activeTenants: response.data.active_tenants || 0,
          pendingComplaints: response.data.pending_complaints || 0,
        });

      } catch (error) {
        console.error("Error fetching stats", error);
      }
    };

    const fetchTenants = async () => {
      try {
        const token = localStorage.getItem("token"); // Ensure token is included
        if (!token) {
          console.error("No token found. User may not be logged in.");
          return;
        }

        const response = await api.get("/super-admin/tenants", {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });

        console.log("Tenants Response:", response.data); // Debugging response
        setTenants(response.data);

      } catch (error) {
        console.error("Error fetching tenants", error);
      }
    };

    fetchStats();
    fetchTenants();
  }, []);

  return (
    <div className="flex">
      <Sidebar />
      <div className="flex-1 p-6">
        <h1 className="text-2xl font-bold mb-4">Super Admin Dashboard</h1>

        {/* Stats Section */}
        <div className="grid grid-cols-3 gap-4 mb-6">
          <div className="bg-blue-500 text-white p-4 rounded-lg shadow-md">
            <h2 className="text-lg font-semibold">Total Tenants</h2>
            <p className="text-xl">{stats.totalTenants}</p>
          </div>

          <div className="bg-green-500 text-white p-4 rounded-lg shadow-md">
            <h2 className="text-lg font-semibold">Active Tenants</h2>
            <p className="text-xl">{stats.activeTenants}</p>
          </div>

          <div className="bg-red-500 text-white p-4 rounded-lg shadow-md">
            <h2 className="text-lg font-semibold">Pending Complaints</h2>
            <p className="text-xl">{stats.pendingComplaints}</p>
          </div>
        </div>

        {/* Tenants List Table */}
        <div className="bg-white p-6 rounded-lg shadow-md">
          <h2 className="text-xl font-semibold mb-4">Registered Tenants</h2>
          <div className="overflow-x-auto">
            <table className="w-full border-collapse border border-gray-300">
              <thead>
                <tr className="bg-gray-100">
                  <th className="border border-gray-300 p-2">Tenant Name</th>
                  <th className="border border-gray-300 p-2">Email</th>
                  <th className="border border-gray-300 p-2">Status</th>
                </tr>
              </thead>
              <tbody>
                {tenants.length > 0 ? (
                  tenants.map((tenant) => (
                    <tr key={tenant.id} className="text-center">
                      <td className="border border-gray-300 p-2">{tenant.name}</td>
                      <td className="border border-gray-300 p-2">{tenant.email}</td>
                      <td
                        className={`border border-gray-300 p-2 ${
                          tenant.status === "active" ? "text-green-600" : "text-red-600"
                        }`}
                      >
                        {tenant.status}
                      </td>
                    </tr>
                  ))
                ) : (
                  <tr>
                    <td colSpan="3" className="text-center p-4">
                      No tenants found.
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  );
};

export default SuperAdminDashboard;
