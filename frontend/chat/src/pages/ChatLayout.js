
import React, { useContext, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import ChannelSelector from '../components/molecules/ChannelSelector';
import UserList from '../components/organisms/UserList';
import { ChatContext, actionTypes } from '../context/ChatContext';
import MessageArea from '../components/organisms/MessageArea';

const ChatLayout = () => {
    const { channelId, userId } = useParams();
    const navigate = useNavigate();
    const { state, dispatch } = useContext(ChatContext);
    const { users, messages, isLoading, error } = state;
    const channels = lc_initdata['channels'];

    useEffect(() => {
        if (channelId) {
            const fetchUsers = async () => {
                dispatch({ type: actionTypes.FETCH_USERS_START });
                try {
                    const response = await window.jQuery.ajax({
                        url: lc_initdata['ajaxurl'],
                        type: 'POST',
                        data: {
                            action: 'slc_fetch_users',
                            nonce: lc_initdata['ajax_nonce'],
                            channel_prefix: channelId,
                        },
                    });
                    dispatch({ type: actionTypes.FETCH_USERS_SUCCESS, payload: response });
                } catch (err) {
                    dispatch({ type: actionTypes.FETCH_USERS_FAILURE, payload: err.statusText });
                }
            };
            fetchUsers();
        }
    }, [channelId, dispatch]);

    useEffect(() => {
        if (channelId && userId) {
            const fetchMessages = async () => {
                dispatch({ type: actionTypes.FETCH_MESSAGES_START });
                try {
                    const response = await window.jQuery.ajax({
                        url: lc_initdata['ajaxurl'],
                        type: 'POST',
                        data: {
                            action: 'slc_fetch_messages',
                            nonce: lc_initdata['ajax_nonce'],
                            channel_prefix: channelId,
                            user_id: userId,
                        },
                    });
                    dispatch({ type: actionTypes.FETCH_MESSAGES_SUCCESS, payload: response.data.messages });
                } catch (err) {
                    dispatch({ type: actionTypes.FETCH_MESSAGES_FAILURE, payload: err.statusText });
                }
            };
            fetchMessages();
        }
    }, [channelId, userId, dispatch]);

    const handleChannelSelect = (selectedChannelId) => {
        navigate(`/channel/${selectedChannelId}`);
    };

    const handleUserSelect = (selectedUserId) => {
        navigate(`/channel/${channelId}/user/${selectedUserId}`);
    };

    const handleMessageSend = (message) => {
        // メッセージ送信のロジックを実装
    };

    return (
        <div className="flex h-screen">
            {/* Left Sidebar */}
            <div className="w-1/4 bg-gray-100 p-4">
                <div className="mb-4">
                    {/* Channel Selector */}
                    <ChannelSelector channels={channels} selectedChannelId={channelId} onSelect={handleChannelSelect} />
                </div>
                {channelId && (
                    <div>
                        {/* User List */}
                        {isLoading && <p>Loading users...</p>}
                        {error && <p>Error: {error}</p>}
                        {!isLoading && !error && <UserList users={users} selectedUserId={userId} onSelectUser={handleUserSelect} />}
                    </div>
                )}
            </div>

            {/* Main Content */}
            <div className="flex-1 flex flex-col">
                {userId ? (
                    <>
                        {/* Message Area */}
                        <div className="flex-1 p-4">
                            {isLoading && <p>Loading messages...</p>}
                            {error && <p>Error: {error}</p>}
                            {!isLoading && !error && <MessageArea messages={messages} isLoading={isLoading} onSendMessage={handleMessageSend} />}
                        </div>
                    </>
                ) : (
                    <div className="flex-1 flex items-center justify-center">
                        <p>Please select a user to start chatting.</p>
                    </div>
                )}
            </div>

            {/* Right Sidebar */}
            {userId && (
                <div className="w-1/4 bg-gray-100 p-4">
                    {/* User Profile */}
                    <p>User Profile for {userId}</p>
                </div>
            )}
        </div>
    );
};

export default ChatLayout;
