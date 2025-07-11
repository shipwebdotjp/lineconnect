import React from 'react';
import PropTypes from 'prop-types';
import Avatar from '../atoms/Avatar';

const UserListItem = ({ user, isSelected, onSelectUser }) => {
    const { line_id, profile } = user;
    const displayName = profile?.displayName || 'Unknown User';
    const avatarUrl = profile?.pictureUrl;

    // Add a class if the item is selected to highlight it.
    const itemClassName = `user-list-item ${isSelected ? 'selected' : ''}`;

    const handleSelect = () => {
        onSelectUser(line_id);
    };

    return (
        <div className={itemClassName} onClick={handleSelect} style={{ display: 'flex', alignItems: 'center', padding: '8px', cursor: 'pointer' }}>
            <Avatar src={avatarUrl} alt={displayName} style={{ marginRight: '12px' }} />
            <div className="user-name">{displayName}</div>
        </div>
    );
};

UserListItem.propTypes = {
    user: PropTypes.shape({
        line_id: PropTypes.string.isRequired,
        profile: PropTypes.shape({
            displayName: PropTypes.string,
            pictureUrl: PropTypes.string,
        }),
    }).isRequired,
    isSelected: PropTypes.bool.isRequired,
    onSelectUser: PropTypes.func.isRequired,
};

export default UserListItem;
