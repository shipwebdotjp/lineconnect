import React from 'react';
import { Outlet, useLoaderData, useParams, useNavigation } from 'react-router-dom';
import InteractionTabs from '../components/InteractionTabs';

const __ = wp.i18n.__;

export async function loader({ params }) {
    const lineConnectConfig = window.lineConnectConfig || {};
    const response = await fetch(
        `${lineConnectConfig.rest_url}lineconnect/interactions/${params.interactionId}`,
        {
            headers: {
                'X-WP-Nonce': lineConnectConfig.rest_nonce,
            },
            credentials: 'same-origin',
        }
    );
    if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
    }
    const interaction = await response.json();
    return { interaction };
}

const InteractionLayout = () => {
    const { interaction } = useLoaderData();
    const { interactionId } = useParams();
    const navigation = useNavigation();

    const displayTitle = interaction.title || `ID: ${interactionId}`;
    const isLoading = navigation.state === 'loading';

    return (
        <div className="wrap">
            <h1>
                {__('Interaction Details', 'lineconnect')} - {displayTitle}
                {isLoading && <span className="spinner is-active" style={{ float: 'none', marginLeft: '10px' }}></span>}
            </h1>
            <InteractionTabs />
            <div className="interaction-content">
                <Outlet context={{ interaction }} />
            </div>
        </div>
    );
};

export default InteractionLayout;
