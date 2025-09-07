import * as React from 'react';
import ReactDOM from 'react-dom/client'
import Root from "./routes/root";
import InteractionLayout, { loader as interactionLoader } from "./routes/InteractionLayout";
import InteractionSessionsPage, { loader as sessionsLoader } from "./routes/sessions";
import InteractionStatisticsPage from "./routes/statistics";
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
                path: "interactions/:interactionId",
                element: <InteractionLayout />,
                loader: interactionLoader,
                shouldRevalidate: ({ currentParams, nextParams }) => {
                    return currentParams.interactionId !== nextParams.interactionId;
                },
                children: [
                    {
                        path: "sessions/:sessionId?",
                        element: <InteractionSessionsPage />,
                        loader: sessionsLoader,

                    },
                    {
                        path: "statistics",
                        element: <InteractionStatisticsPage />,
                    },
                ],
            },
        ],
    },
]);

ReactDOM.createRoot(document.getElementById('lineconnect-interaction-root')).render(
    <React.StrictMode>
        <RouterProvider router={router} />
    </React.StrictMode>,
)
