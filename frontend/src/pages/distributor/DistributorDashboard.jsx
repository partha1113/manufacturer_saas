import { useContext } from "react";
import { AuthContext } from "../../context/AuthContext";
import DashboardLayout from "../../components/DashboardLayout";

function DistributorDashboard() {
  const { logout } = useContext(AuthContext);
    
  return (
    <div className="flex flex-col items-center justify-center h-screen">
      {/* <h1 className="text-3xl font-bold">Distributor Dashboard</h1> */}
      <DashboardLayout>
      <h1 className="text-3xl font-bold">Distributor Dashboard</h1>
    </DashboardLayout>
      <button
        onClick={logout}
        className="mt-4 px-4 py-2 bg-red-500 text-white rounded"
      >
        Logout
      </button>
    </div>
  );
    
  }
  
  export default DistributorDashboard;
  