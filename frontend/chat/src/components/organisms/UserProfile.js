import React from 'react';
const __ = wp.i18n.__;
import Avatar from '../atoms/Avatar';

const UserProfile = ({ user }) => {
    if (!user) return <div>{__('No user selected', 'lineconnect')}</div>;

    const formatDate = (dateString) => {
        const options = { year: 'numeric', month: 'short', day: 'numeric' };
        return new Date(dateString).toLocaleDateString(undefined, options);
    };

    return (
        <div className="p-4 flex flex-col space-y-4 w-full bg-white rounded-lg shadow">
            {/* Avatar and Name */}
            <div className="flex flex-col items-center">
                <Avatar
                    className="w-24 h-24 mb-2"
                    src={user.profile.pictureUrl}
                    alt={user.profile.displayName}
                />
                <p className="text-lg font-semibold">
                    {user.profile.displayName}
                </p>
            </div>

            {/* Follow Status */}
            <div className="flex items-center justify-center">
                <span className="font-medium mr-2">
                    {__('Follow Status:', 'lineconnect')}
                </span>
                <span
                    className={`px-2 py-1 rounded-full text-xs ${user.follow === '1'
                        ? 'bg-green-100 text-green-800'
                        : 'bg-gray-100 text-gray-800'
                        }`}
                >
                    {user.follow === '1'
                        ? __('Following', 'lineconnect')
                        : __('Not Following', 'lineconnect')}
                </span>
            </div>

            {/* Created/Updated Dates */}
            <div className="space-y-1 text-sm text-center">
                <p>
                    {__('Created:', 'lineconnect')} {formatDate(user.created_at)}
                </p>
                <p>
                    {__('Updated:', 'lineconnect')} {formatDate(user.updated_at)}
                </p>
            </div>

            {/* Dynamic Profile Details */}
            <div className="space-y-2">
                <h3 className="font-semibold">
                    {__('Profile Details', 'lineconnect')}
                </h3>
                {Object.entries(user.profile).map(([key, value]) => {
                    if (key === 'pictureUrl' || key === 'displayName') return null;
                    return (
                        <div
                            key={key}
                            className="flex justify-between border-b pb-1 text-sm"
                        >
                            <span className="font-medium">{__(key, 'lineconnect')}:</span>
                            <span>
                                {Array.isArray(value) ? value.join(', ') : value}
                            </span>
                        </div>
                    );
                })}
            </div>

            {/* Tags */}
            <div>
                <h3 className="font-semibold mb-2">
                    {__('Tags', 'lineconnect')}
                </h3>
                <div className="flex flex-wrap justify-start gap-2">
                    {user.tags && user.tags.length > 0 && (
                        user.tags.map((tag, index) => (
                            <span
                                key={index}
                                className="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-sm"
                            >
                                {tag}
                            </span>
                        ))
                    ) || (
                            <span className="text-gray-500">
                                {__('No tags available', 'lineconnect')}
                            </span>
                        )}
                </div>
            </div>


            {/* Scenarios */}
            {user.scenarios && Object.keys(user.scenarios).length > 0 && (
                <div className="space-y-2">
                    <h3 className="font-semibold">
                        {__('Scenario Subscriptions', 'lineconnect')}
                    </h3>
                    <div className="mb-3 p-1 bg-gray-50 rounded text-sm">
                        <div className="flex items-center justify-between mb-1">
                            <p className="font-medium">
                                {__('ID', 'lineconnect')}
                            </p>
                            <p className="font-medium">
                                {__('Status', 'lineconnect')}
                            </p>
                            <p className="font-medium">
                                {__('Start', 'lineconnect')}
                            </p>
                            <p className="font-medium">
                                {__('Updated', 'lineconnect')}
                            </p>
                        </div>
                        {Object.entries(user.scenarios).map(([id, scenario]) => (
                            <div key={id} className="flex items-center justify-between mb-1">
                                <p className="font-medium">
                                    {`${id}`}
                                </p>
                                <p>
                                    <span className="capitalize">{scenario.status}</span>
                                </p>
                                <p>
                                    {`${formatDate(
                                        scenario.started_at
                                    )}`}
                                </p>
                                <p>
                                    {`${formatDate(
                                        scenario.updated_at
                                    )}`}
                                </p>
                            </div>
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
};

export default UserProfile;
