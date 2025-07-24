import React from 'react';
import PropTypes from 'prop-types';
import UserListItem from '../molecules/UserListItem';
const __ = wp.i18n.__;

const UserList = ({ users, selectedUserId = null, onSelectUser, isLoading }) => {
    return (
        <div className="user-list">
            {isLoading ? (
                <div>{__('Loading...', 'lineconnect')}</div>
            ) : (
                users.map((user) => (
                    <UserListItem
                        key={user.lineId}
                        user={user}
                        isSelected={user.lineId === selectedUserId}
                        onSelectUser={onSelectUser}
                    />
                ))
            )}
        </div>
    );
};

UserList.propTypes = {
    users: PropTypes.arrayOf(
        PropTypes.shape({
            lineId: PropTypes.string.isRequired,
        })
    ).isRequired,
    selectedUserId: PropTypes.string,
    onSelectUser: PropTypes.func.isRequired,
};

export default UserList;
