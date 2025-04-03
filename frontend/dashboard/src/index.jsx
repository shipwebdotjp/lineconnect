import * as React from 'react';
import ReactDOM from 'react-dom/client'
import Root from "./routes/root";
import Dashboard from "./routes/dashboard";
import Daily from "./routes/daily";
import ErrorPage from "./error-page";

import {
    createHashRouter,
    RouterProvider,
} from "react-router-dom";
import "./styles/globals.css";

const router = createHashRouter([
    {
        path: "/",
        element: <Root />,
        errorElement: <ErrorPage />,
        children: [
            {
                index: true,
                element: <Dashboard />,
            },
            {
                path: "daily",
                element: <Daily />,
            },
        ],
    },
]);

ReactDOM.createRoot(document.getElementById('lineconnect-dashboard-root')).render(
    <React.StrictMode>
        <RouterProvider router={router} />
    </React.StrictMode>,
)