import React from 'react';
import { useOutletContext } from 'react-router-dom';

// wp_localize_scriptによって 'lineConnectConfig' という名前で渡される
const __ = wp.i18n.__;

const Statistics = () => {
    const { interaction } = useOutletContext();

    if (!interaction) {
        return <div className="text-gray-600">{__('Loading statistics...', 'lineconnect')}</div>;
    }

    if (!interaction.statistics) {
        return (
            <div className="bg-blue-100 border border-blue-300 text-blue-800 px-4 py-3 rounded">
                <p>{__('No statistics available for this interaction.', 'lineconnect')}</p>
            </div>
        );
    }

    const { total, by_version } = interaction.statistics;

    return (
        <div className="space-y-6">
            {/* Overall Statistics */}
            <div className="bg-white border border-gray-300 rounded shadow-sm">
                <h2 className="bg-gray-100 px-4 py-2 border-b border-gray-300 font-semibold text-gray-800">
                    {__('Overall Statistics', 'lineconnect')}
                </h2>
                <div className="p-4">
                    <div className="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-7 gap-4 mb-5">
                        <div className="bg-gray-50 p-3 rounded border border-gray-200">
                            <strong className="text-gray-700">{__('Total Sessions:', 'lineconnect')}</strong> {total.total_sessions}
                        </div>
                        <div className="bg-gray-50 p-3 rounded border border-gray-200">
                            <strong className="text-gray-700">{__('Active:', 'lineconnect')}</strong> {total.active}
                        </div>
                        <div className="bg-gray-50 p-3 rounded border border-gray-200">
                            <strong className="text-gray-700">{__('Paused:', 'lineconnect')}</strong> {total.paused}
                        </div>
                        <div className="bg-gray-50 p-3 rounded border border-gray-200">
                            <strong className="text-gray-700">{__('Completed:', 'lineconnect')}</strong> {total.completed}
                        </div>
                        <div className="bg-gray-50 p-3 rounded border border-gray-200">
                            <strong className="text-gray-700">{__('Timeout:', 'lineconnect')}</strong> {total.timeout}
                        </div>
                        <div className="bg-gray-50 p-3 rounded border border-gray-200">
                            <strong className="text-gray-700">{__('Completion Rate:', 'lineconnect')}</strong> {total.completion_rate}%
                        </div>
                        <div className="bg-gray-50 p-3 rounded border border-gray-200">
                            <strong className="text-gray-700">{__('Unique Users:', 'lineconnect')}</strong> {total.unique_users}
                        </div>
                    </div>
                </div>
            </div>

            {/* Version-specific Statistics */}
            {Object.keys(by_version).length > 0 && (
                <div className="bg-white border border-gray-300 rounded shadow-sm">
                    <h2 className="bg-gray-100 px-4 py-2 border-b border-gray-300 font-semibold text-gray-800">
                        {__('Statistics by Version', 'lineconnect')}
                    </h2>
                    <div className="p-4">
                        {Object.entries(by_version).map(([version, stats]) => (
                            <div key={version} className="bg-gray-50 p-4 rounded mb-6">
                                <h3 className="text-lg font-semibold text-gray-800 mt-0 mb-3">
                                    {__('Version', 'lineconnect')} {version}
                                </h3>
                                <div className="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-7 gap-4">
                                    <div className="bg-white p-3 rounded border border-gray-200">
                                        <strong className="text-gray-700">{__('Total Sessions:', 'lineconnect')}</strong> {stats.total_sessions}
                                    </div>
                                    <div className="bg-white p-3 rounded border border-gray-200">
                                        <strong className="text-gray-700">{__('Active:', 'lineconnect')}</strong> {stats.active}
                                    </div>
                                    <div className="bg-white p-3 rounded border border-gray-200">
                                        <strong className="text-gray-700">{__('Paused:', 'lineconnect')}</strong> {stats.paused}
                                    </div>
                                    <div className="bg-white p-3 rounded border border-gray-200">
                                        <strong className="text-gray-700">{__('Completed:', 'lineconnect')}</strong> {stats.completed}
                                    </div>
                                    <div className="bg-white p-3 rounded border border-gray-200">
                                        <strong className="text-gray-700">{__('Timeout:', 'lineconnect')}</strong> {stats.timeout}
                                    </div>
                                    <div className="bg-white p-3 rounded border border-gray-200">
                                        <strong className="text-gray-700">{__('Completion Rate:', 'lineconnect')}</strong> {stats.completion_rate}%
                                    </div>
                                    <div className="bg-white p-3 rounded border border-gray-200">
                                        <strong className="text-gray-700">{__('Unique Users:', 'lineconnect')}</strong> {stats.unique_users}
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
};

export default Statistics;
