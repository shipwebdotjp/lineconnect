import React from 'react';
import PropTypes from 'prop-types';

const __ = wp.i18n.__;

const SystemBubble = ({ event }) => {
    const { event_type, date } = event;

    const getEventMessage = (type) => {
        const eventMessages = {
            2: __('Unsent message', 'lineconnect'),
            3: __('Followed', 'lineconnect'),
            4: __('Unfollowed', 'lineconnect'),
            5: __('Joined', 'lineconnect'),
            6: __('Left', 'lineconnect'),
            7: __('A member joined', 'lineconnect'),
            8: __('A member left', 'lineconnect'),
            9: __('Postback received', 'lineconnect'),
            10: __('Video play completed', 'lineconnect'),
            11: __('Beacon detected', 'lineconnect'),
            12: __('Account linked', 'lineconnect'),
            13: __('Thing connected', 'lineconnect'),
            14: __('Membership event', 'lineconnect'),
        };
        return eventMessages[type] || null;
    };

    const eventMessage = getEventMessage(event_type);

    if (!eventMessage) {
        return null;
    }

    return (
        <div className="flex justify-center items-center w-full my-2">
            <div className="text-base text-gray-500 px-4 py-1 bg-gray-200 rounded-full">
                <span>{eventMessage}</span>
                {date && <span className="ml-2 text-gray-400">{date}</span>}
            </div>
        </div>
    );
};

SystemBubble.propTypes = {
    event: PropTypes.shape({
        id: PropTypes.number.isRequired,
        event_type: PropTypes.number.isRequired,
        date: PropTypes.string,
    }).isRequired,
};

export default SystemBubble;
