import { useContext } from "react";
import { AuthContext } from "../context/AuthContext";
import { Link } from "react-router-dom";

function Sidebar() {
  const { user, logout } = useContext(AuthContext);

  // Define menu based on role
  const menuItems = {
    super_admin: [
      { name: "Dashboard", path: "/super-admin" },
      { name: "Manage Tenants", path: "/super-admin/tenants" },
      { name: "Settings", path: "/super-admin/settings" },
    ],
    manufacturer: [
      { name: "Dashboard", path: "/manufacturer" },
      { name: "Products", path: "/manufacturer/products" },
      { name: "Orders", path: "/manufacturer/orders" },
      { name: "Distributors", path: "/manufacturer/distributors" },
    ],
    distributor: [
      { name: "Dashboard", path: "/distributor" },
      { name: "Orders", path: "/distributor/orders" },
      { name: "Manufacturers", path: "/distributor/manufacturers" },
    ],
    customer: [
      { name: "Orders", path: "/customer/orders" },
      { name: "Complaints", path: "/customer/complaints" },
      { name: "Profile", path: "/customer/profile" },
    ],
  };

  return (
    <div className="w-64 bg-gray-800 text-white h-screen p-4">
      <h2 className="text-xl font-bold mb-6">Dashboard</h2>
      <ul>
        {menuItems[user.role]?.map((item) => (
          <li key={item.path} className="mb-2">
            <Link to={item.path} className="block p-2 hover:bg-gray-700 rounded">
              {item.name}
            </Link>
          </li>
        ))}
      </ul>
      <button
        onClick={logout}
        className="mt-4 w-full p-2 bg-red-600 rounded hover:bg-red-700"
      >
        Logout
      </button>
    </div>
  );
}

export default Sidebar;
