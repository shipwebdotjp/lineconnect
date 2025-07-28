import React from 'react';
import PropTypes from 'prop-types';
import UserListItem from '../molecules/UserListItem';
const __ = wp.i18n.__;

const UserList = ({ users, selectedUserId = null, onSelectUser, isLoading, hasMore, fetchMore }) => {
    return (
        <div className="user-list">

            {users.map((user) => (
                <UserListItem
                    key={user.lineId}
                    user={user}
                    isSelected={user.lineId === selectedUserId}
                    onSelectUser={onSelectUser}
                />
            ))}
            {(hasMore && (
                <button
                    className="w-full text-center bg-blue-200 mb-4 p-4 text-blue-500 pointer-events-auto hover:bg-blue-100 disabled:opacity-50"
                    onClick={fetchMore}
                    disabled={isLoading}
                >
                    {isLoading ? __('Loading...', 'lineconnect') : __('Load more users', 'lineconnect')}
                </button>
            ))}

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
