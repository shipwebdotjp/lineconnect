import React from 'react';
import PropTypes from 'prop-types';
import Avatar from '../atoms/Avatar';

const UserListItem = ({ user, isSelected, onSelectUser }) => {
    const { lineId, displayName, pictureUrl = null, last_message = null, last_sent_at = null } = user;

    const itemClassName = `flex items-center p-2 cursor-pointer ${isSelected ? 'bg-gray-200' : ''}`;

    const handleSelect = () => {
        onSelectUser(lineId);
    };

    const formatTimestamp = (timestamp) => {
        if (!timestamp) return '';
        const date = new Date(timestamp);
        const today = new Date();
        if (date.toDateString() === today.toDateString()) {
            return date.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' });
        } else {
            return date.toLocaleDateString(undefined);
        }
    };

    return (
        <div className={itemClassName} onClick={handleSelect}>
            <Avatar src={pictureUrl} alt={displayName} />
            <div className="flex-grow ml-2 overflow-hidden">
                <div className="font-semibold truncate">{displayName}</div>
                <div className="text-sm text-gray-600 truncate">{last_message}</div>
            </div>
            <div className="text-xs text-gray-500 whitespace-nowrap ml-2">
                {formatTimestamp(last_sent_at)}
            </div>
        </div>
    );
};

UserListItem.propTypes = {
    user: PropTypes.shape({
        lineId: PropTypes.string.isRequired,
        displayName: PropTypes.string,
        pictureUrl: PropTypes.string,
        last_message: PropTypes.string,
        last_sent_at: PropTypes.string,
    }).isRequired,
    isSelected: PropTypes.bool.isRequired,
    onSelectUser: PropTypes.func.isRequired,
};

export default UserListItem;
