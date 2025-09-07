import React from 'react';
import { NavLink, useParams } from 'react-router-dom';

const __ = wp.i18n.__;

const InteractionTabs = () => {
    const { interactionId } = useParams();

    return (
        <div className="interaction-tabs">
            <nav className="nav-tab-wrapper">
                <NavLink
                    to={`/interactions/${interactionId}/sessions`}
                    className={({ isActive }) =>
                        `nav-tab ${isActive ? 'nav-tab-active' : ''}`
                    }
                >
                    {__('Sessions', 'lineconnect')}
                </NavLink>
                <NavLink
                    to={`/interactions/${interactionId}/statistics`}
                    className={({ isActive }) =>
                        `nav-tab ${isActive ? 'nav-tab-active' : ''}`
                    }
                >
                    {__('Statistics', 'lineconnect')}
                </NavLink>
            </nav>
        </div>
    );
};

export default InteractionTabs;
