import React, { useState, useEffect } from 'react';
import { useLoaderData, useParams, useNavigate } from 'react-router-dom';
import SessionDetailDrawer from '../components/SessionDetailDrawer';
import SessionPagination from '../components/SessionPagination';
import { MultiSelect } from "../components/ui/multi-select";
import { useUrlFilters } from '../lib/useUrlFilters';
import { Input } from "../components/ui/input";
import { DateTimePicker } from '../components/ui/datetime-picker';
import { X, Trash2, Download } from 'lucide-react';
import { Button } from "../components/ui/button";
// wp_localize_scriptによって 'lineConnectConfig' という名前で渡される
const lineConnectConfig = window.lineConnectConfig || {};
const __ = wp.i18n.__;

export async function loader({ params, request }) {
    const url = new URL(request.url);
    const status = url.searchParams.getAll('status');
    const version = url.searchParams.getAll('version');
    const channel = url.searchParams.getAll('channel');
    const lineUserId = url.searchParams.get('line_user_id') || '';
    const updatedAtStart = url.searchParams.get('updated_at_start') || '';
    const updatedAtEnd = url.searchParams.get('updated_at_end') || '';
    const page = url.searchParams.get('page') || '1';
    const perPage = url.searchParams.get('per_page') || '20';

    const queryParams = new URLSearchParams();
    if (status.length > 0) {
        status.forEach(s => queryParams.append('status[]', s));
    }
    if (version.length > 0) {
        version.forEach(v => queryParams.append('version[]', v));
    }
    if (channel.length > 0) {
        channel.forEach(c => queryParams.append('channel[]', c));
    }
    if (lineUserId) {
        queryParams.set('line_user_id', lineUserId);
    }
    if (updatedAtStart) {
        queryParams.set('updated_at_start', updatedAtStart);
    }
    if (updatedAtEnd) {
        queryParams.set('updated_at_end', updatedAtEnd);
    }
    queryParams.set('page', page);
    queryParams.set('per_page', perPage);

    const apiUrl = `${lineConnectConfig.rest_url}lineconnect/interactions/${params.interactionId}/sessions?${queryParams.toString()}`;

    const response = await fetch(apiUrl, {
        headers: {
            'X-WP-Nonce': lineConnectConfig.rest_nonce,
        },
        credentials: 'same-origin',
    });
    if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
    }
    const responseData = await response.json();
    const sessions = responseData.data;
    const totalItems = responseData.meta.pagination.total;
    const totalPages = responseData.meta.pagination.pages;
    const currentPage = parseInt(page) || 1;
    const filters = responseData.meta.filters;
    return { sessions, currentPage, totalPages, filters, totalItems };
}

const Sessions = () => {
    const { sessions, currentPage, totalPages, filters, totalItems } = useLoaderData();
    const { interactionId, sessionId } = useParams();
    const navigate = useNavigate();
    // local state for sessions so we can update list immediately after edit
    const [sessionsState, setSessionsState] = useState(sessions || []);
    const [selectedSession, setSelectedSession] = useState(null);
    const [drawerOpen, setDrawerOpen] = useState(false);

    const {
        statusValues,
        versionValues,
        channelValues,
        lineUserId,
        updatedAtStart,
        updatedAtEnd,
        handleStatusChange,
        handleVersionChange,
        handleChannelChange,
        handleLineUserIdChange,
        handleUpdatedAtStartChange,
        handleUpdatedAtEndChange,
        handlePageChange,
    } = useUrlFilters(interactionId);

    const handleCsvDownload = () => {
        const url = new URL(window.location.href);
        // The search params from the current URL are already the filters we want
        const queryParams = url.searchParams;

        // Remove pagination params as we want all data
        queryParams.delete('page');
        queryParams.delete('per_page');

        const csvUrl = `${lineConnectConfig.interaction_session_download_url}&interaction_id=${interactionId}&${queryParams.toString()}`;

        // To trigger download, we can open the URL in a new tab, or create a link and click it.
        // The latter is cleaner as it doesn't leave a blank tab open if the browser can't close it.
        const link = document.createElement('a');
        link.href = csvUrl;
        link.setAttribute('download', ''); // this is important for browsers to treat it as a download
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };

    const statusOptions = [
        { value: "active", label: __("Active", "lineconnect") },
        { value: "paused", label: __("Paused", "lineconnect") },
        { value: "completed", label: __("Completed", "lineconnect") },
        { value: "timeout", label: __("Timeout", "lineconnect") },
    ];

    const versionOptions = filters?.versions?.map(v => ({ value: v.toString(), label: __(`Version ${v}`, "lineconnect") })) || [];
    const channelOptions = filters?.channels || [];

    // keep local sessionsState in sync when loader data changes (e.g., page navigation)
    useEffect(() => {
        setSessionsState(sessions || []);
    }, [sessions]);

    useEffect(() => {
        if (sessionId && sessionsState.length > 0) {
            const session = sessionsState.find(s => s.id.toString() === sessionId);
            if (session) {
                setSelectedSession(session);
                setDrawerOpen(true);
            } else {
                handleDrawerClose(false);
            }
        } else if (!sessionId) {
            setDrawerOpen(false);
            setSelectedSession(null);
        }
    }, [sessionId, sessionsState]);

    const handleSessionClick = (session) => {
        setSelectedSession(session);
        setDrawerOpen(true);
        navigate(`/interactions/${interactionId}/sessions/${session.id}`, { replace: true });
    };

    const handleDrawerClose = (open) => {
        setDrawerOpen(open);
        if (!open) {
            setSelectedSession(null);
            navigate(`/interactions/${interactionId}/sessions`, { replace: true });
        }
    };

    const handleDeleteClick = async (sessionId) => {
        if (!confirm(__('Are you sure you want to delete this session?', 'lineconnect'))) {
            return;
        }
        const deleteUrl = `${lineConnectConfig.rest_url}lineconnect/interactions/${interactionId}/sessions/${sessionId}`;
        try {
            const response = await fetch(deleteUrl, {
                method: 'DELETE',
                headers: {
                    'X-WP-Nonce': lineConnectConfig.rest_nonce,
                },
                credentials: 'same-origin',
            });
            if (!response.ok) {
                const err = await response.json().catch(() => ({}));
                alert(err.message || __('Failed to delete session.', 'lineconnect'));
                return;
            }
            // success, update local state
            setSessionsState(prev => prev.filter(s => s.id.toString() !== sessionId.toString()));
        } catch (e) {
            console.error(e);
            alert(__('Error deleting session.', 'lineconnect'));
        }
    };

    return (
        <div>
            <div className='my-2 flex gap-4'>
                <MultiSelect
                    options={statusOptions}
                    onValueChange={handleStatusChange}
                    defaultValue={statusValues}
                    placeholder={__("Filter by status", "lineconnect")}
                    maxCount={3}
                    searchable={false}
                    autoSize={true}
                />
                <MultiSelect
                    options={versionOptions}
                    onValueChange={handleVersionChange}
                    defaultValue={versionValues}
                    placeholder={__("Filter by version", "lineconnect")}
                    maxCount={3}
                    autoSize={true}
                />
                <MultiSelect
                    options={channelOptions}
                    onValueChange={handleChannelChange}
                    defaultValue={channelValues}
                    placeholder={__("Filter by channel", "lineconnect")}
                    maxCount={3}
                    searchable={false}
                    autoSize={true}
                />
                <Input
                    type="text"
                    value={lineUserId}
                    onChange={(e) => handleLineUserIdChange(e.target.value)}
                    placeholder={__("Search by LINE User ID", "lineconnect")}
                    className="w-64"
                />
                <div className="flex items-center">
                    <DateTimePicker
                        value={updatedAtStart ? new Date(updatedAtStart) : undefined}
                        defaultPopupValue={updatedAtStart ? new Date(updatedAtStart) : undefined}
                        onChange={(date) => handleUpdatedAtStartChange(date)}
                        placeholder={__("Updated at start", "lineconnect")}
                        displayFormat={{ hour24: 'yyyy/MM/dd HH:mm:ss' }}
                        className="w-64"
                    />
                    <button
                        type="button"
                        onClick={() => handleUpdatedAtStartChange(undefined)}
                        className="ml-2 inline-flex items-center px-2 py-1 border rounded text-gray-600 hover:bg-gray-100"
                        aria-label={__("Clear start date", "lineconnect")}
                        title={__("Clear", "lineconnect")}
                    >
                        <X className="h-4 w-4" />
                    </button>
                </div>
                <div className="flex items-center">
                    <DateTimePicker
                        value={updatedAtEnd ? new Date(updatedAtEnd) : undefined}
                        defaultPopupValue={updatedAtEnd ? new Date(updatedAtEnd) : undefined}
                        onChange={(date) => handleUpdatedAtEndChange(date)}
                        placeholder={__("Updated at end", "lineconnect")}
                        displayFormat={{ hour24: 'yyyy/MM/dd HH:mm:ss' }}
                        className="w-48"
                    />
                    <button
                        type="button"
                        onClick={() => handleUpdatedAtEndChange(undefined)}
                        className="ml-2 inline-flex items-center px-2 py-1 border rounded text-gray-600 hover:bg-gray-100"
                        aria-label={__("Clear end date", "lineconnect")}
                        title={__("Clear", "lineconnect")}
                    >
                        <X className="h-4 w-4" />
                    </button>
                </div>
                <Button
                    variant="outline"
                    onClick={handleCsvDownload}
                    className="flex items-center"
                >
                    <Download className="h-4 w-4 mr-2" />
                    {__("CSV Download", "lineconnect")}
                </Button>
            </div>
            <table className="wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th scope="col">{__('Session ID', 'lineconnect')}</th>
                        <th scope="col">{__('Version', 'lineconnect')}</th>
                        <th scope="col">{__('Channel', 'lineconnect')}</th>
                        <th scope="col">{__('LINE User ID', 'lineconnect')}</th>
                        <th scope="col">{__('Status', 'lineconnect')}</th>
                        <th scope="col">{__('Current Step', 'lineconnect')}</th>
                        <th scope="col">{__('Updated At', 'lineconnect')}</th>
                        <th scope="col">{__('Actions', 'lineconnect')}</th>
                    </tr>
                </thead>
                <tbody>
                    {sessionsState.length === 0 && (
                        <tr>
                            <td colSpan={8}>{__('No sessions found for this interaction.', 'lineconnect')}</td>
                        </tr>
                    )}
                    {sessionsState.map((session) => (
                        <tr
                            key={session.id}
                            onClick={() => handleSessionClick(session)}
                            className="cursor-pointer hover:bg-gray-50"
                        >
                            <td>{session.id}</td>
                            <td>{session.interaction_version}</td>
                            <td>{session.channel_name || session.channel_prefix}</td>
                            <td>{session.displayName || session.line_user_id}</td>
                            <td>{statusOptions.find(option => option.value === session.status)?.label || session.status}</td>
                            <td>{session.status === 'active' ? session.current_step_id : ''}</td>
                            <td>{new Date(session.updated_at).toLocaleString('ja-JP')}</td>
                            <td>
                                <button
                                    type="button"
                                    onClick={(e) => {
                                        e.stopPropagation();
                                        handleDeleteClick(session.id);
                                    }}
                                    className="text-red-600 hover:text-red-800 p-1"
                                    aria-label={__('Delete session', 'lineconnect')}
                                    title={__('Delete', 'lineconnect')}
                                >
                                    <Trash2 className="h-4 w-4" />
                                </button>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
            <div className='my-2 text-sm flex justify-end'>
                <SessionPagination
                    currentPage={currentPage}
                    totalPages={totalPages}
                    totalItems={totalItems}
                    onPageChange={handlePageChange}
                />
            </div>
            <SessionDetailDrawer
                session={selectedSession}
                isOpen={drawerOpen}
                onClose={handleDrawerClose}
                onEdit={(updatedSession) => {
                    // update selectedSession and sessions list immediately
                    if (updatedSession) {
                        setSelectedSession(updatedSession);
                        setSessionsState(prev => prev.map(s => {
                            try {
                                return s.id.toString() === updatedSession.id.toString() ? updatedSession : s;
                            } catch (e) {
                                return s;
                            }
                        }));
                    }
                }}
                onDelete={handleDeleteClick}
            />
        </div>
    );
};

export default Sessions;
