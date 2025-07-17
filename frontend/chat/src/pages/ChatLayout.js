
import React, { useContext, useEffect, useRef } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import ChannelSelector from '../components/molecules/ChannelSelector';
import UserList from '../components/organisms/UserList';
import { ChatContext, actionTypes } from '../context/ChatContext';
import MessageArea from '../components/organisms/MessageArea';
import MessageForm from '../components/organisms/MessageForm';

const ChatLayout = () => {
    const { channelId, userId } = useParams();
    const navigate = useNavigate();
    const { state, dispatch } = useContext(ChatContext);
    const { users, messages, isLoading, error, isMessageFormOpen, buildMessages, isSending, notificationDisabled } = state;
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

    const handleMessageSend = () => {
        dispatch({
            type: actionTypes.SEND_MESSAGE_START,
        });
        const buildMessages = state.buildMessages || [];
        jQuery.ajax({
            type: "POST",
            url: lc_initdata['ajaxurl'],
            data: {
                'action': 'lc_ajax_chat_send',
                'nonce': lc_initdata['ajax_nonce'],
                'messages': buildMessages,
                'channel': channelId,
                'to': userId,
                'notificationDisabled': notificationDisabled ? 1 : 0,
            },
            dataType: 'json'
        }).done((data) => {
            dispatch({ type: actionTypes.SEND_MESSAGE_SUCCESS, payload: data });
        }).fail((XMLHttpRequest, textStatus, error) => {
            dispatch({ type: actionTypes.SEND_MESSAGE_FAILURE, payload: error });
        });
    };

    const containerRef = useRef(null);

    useEffect(() => {
        const el = containerRef.current;
        if (!el) return;

        const setHeight = () => {
            const offset = el.getBoundingClientRect().top;
            // wpfooterの高さを引く
            const wpFooterHeight = document.querySelector('#wpfooter') ? document.querySelector('#wpfooter').offsetHeight : 0;
            const totalOffset = offset + wpFooterHeight;
            console.log(`Setting height: ${el.style.height} offset: ${offset} wpFooterOffset: ${wpFooterHeight} total offset: ${totalOffset}`);
            el.style.height = `calc(100dvh - ${totalOffset}px)`;
        };

        setHeight();
        window.addEventListener('resize', setHeight);

        const ro = new ResizeObserver(setHeight);
        ro.observe(document.body);

        return () => {
            window.removeEventListener('resize', setHeight);
            ro.disconnect();
        };
    }, []);

    return (
        <>
            {isMessageFormOpen && (<MessageForm onSendMessage={handleMessageSend} />)}
            <div ref={containerRef} className="flex overflow-hidden">
                {/* Left Sidebar */}
                <div className="w-1/4 bg-gray-100 p-4 h-full overflow-y-auto">
                    <div className="mb-4">
                        {/* Channel Selector */}
                        <ChannelSelector channels={channels} selectedChannelId={channelId} onSelect={handleChannelSelect} />
                    </div>
                    {channelId && (
                        <div>
                            {/* User List */}
                            {isLoading && <p>Loading users...</p>}
                            {!isLoading && !error && <UserList users={users} selectedUserId={userId} onSelectUser={handleUserSelect} />}
                        </div>
                    )}
                </div>

                {/* Main Content */}
                <div className="flex-1 flex flex-col h-full overflow-y-auto">
                    {userId ? (
                        <>
                            {/* Message Area */}
                            <div className="flex-1 p-4">
                                {isLoading && <p>Loading messages...</p>}
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
                    <div className="w-1/4 bg-gray-100 p-4 h-full overflow-y-auto">
                        {/* User Profile */}
                        <p>User Profile for {userId}</p>
                    </div>
                )}
            </div>
        </>
    );
};

export default ChatLayout;
