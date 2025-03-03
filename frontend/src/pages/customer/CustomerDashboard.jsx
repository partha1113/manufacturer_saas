import { useContext } from "react";
import { AuthContext } from "../../context/AuthContext";
import DashboardLayout from "../../components/DashboardLayout";


function CustomerDashboard() {
  const { logout } = useContext(AuthContext);

  return (
    <div className="flex flex-col items-center justify-center h-screen">
      <DashboardLayout>
      <h1 className="text-3xl font-bold">CustomerDashboard</h1>
    </DashboardLayout>    
    </div>
  );
}

export default CustomerDashboard;
