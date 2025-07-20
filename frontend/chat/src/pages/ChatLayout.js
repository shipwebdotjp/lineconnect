
import React, { useCallback, useContext, useEffect, useRef, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import ChannelSelector from '../components/molecules/ChannelSelector';
import UserList from '../components/organisms/UserList';
import { ChatContext, actionTypes } from '../context/ChatContext';
import MessageArea from '../components/organisms/MessageArea';
import MessageForm from '../components/organisms/MessageForm';
import UserProfile from '../components/organisms/UserProfile';
const __ = wp.i18n.__;

const ChatLayout = () => {
    const { channelId, userId } = useParams();
    const navigate = useNavigate();
    const { state, dispatch } = useContext(ChatContext);
    const { users, messages, isLoading, error, selectedUser } = state;
    const [buildMessages, setBuildMessages] = useState([]);
    const [isSending, setIsSending] = useState(false);
    const [notificationDisabled, setNotificationDisabled] = useState(false);
    const [results, setResults] = useState(null);
    const [isMessageFormOpen, setIsMessageFormOpen] = useState(false);
    const [hasMore, setHasMore] = useState(true);

    const channels = lc_initdata['channels'];

    const containerRef = useRef(null);

    // チャネルIDがない場合、最初のチャネルにリダイレクト
    useEffect(() => {
        if (!channelId && channels && channels.length > 0) {
            console.log('No channelId provided, redirecting to first channel');
            navigate(`/channel/${channels[0].prefix}`);
        }
    }, [channelId, channels, navigate]);

    // チャネル選択時にユーザーリストをフェッチ
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
            console.log(`Fetching users for channel: ${channelId}`);
        }
    }, [channelId, dispatch]);

    // ユーザー選択時にメッセージとユーザーデータをフェッチ
    useEffect(() => {
        if (channelId && userId) {
            console.log(`Fetching messages and user data for : ${userId} in channel: ${channelId}`);
            dispatch({ type: actionTypes.RESET_CHAT_STATE });
            setHasMore(true);
            fetchMessages();
            fetchUserData();
        }
    }, [channelId, userId, dispatch]);

    // コンテナの高さを調整
    useEffect(() => {
        const el = containerRef.current;
        if (!el) return;

        const setHeight = () => {
            const offset = el.getBoundingClientRect().top;
            // wpfooterの高さを引く
            const wpFooterHeight = document.querySelector('#wpfooter') ? document.querySelector('#wpfooter').offsetHeight : 0;
            const totalOffset = offset + wpFooterHeight;
            // console.log(`Setting height: ${el.style.height} offset: ${offset} wpFooterOffset: ${wpFooterHeight} total offset: ${totalOffset}`);
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

    const fetchMessages = useCallback(async () => {
        if (!channelId || !userId) return;
        dispatch({ type: actionTypes.FETCH_MESSAGES_START });
        try {
            // const timestamp = messages.length > 0 ? messages[0].date : null;
            const response = await window.jQuery.ajax({
                url: lc_initdata['ajaxurl'],
                type: 'POST',
                data: {
                    action: 'slc_fetch_messages',
                    nonce: lc_initdata['ajax_nonce'],
                    channel_prefix: channelId,
                    user_id: userId,
                    // timestamp: timestamp,
                },
            });
            if (response.success) {
                if (response.data.messages.length === 0) {
                    setHasMore(false);
                }
                dispatch({ type: actionTypes.FETCH_MESSAGES_SUCCESS, payload: response.data.messages });
            }
        } catch (err) {
            dispatch({ type: actionTypes.FETCH_MESSAGES_FAILURE, payload: err.statusText });
        }
    }, [channelId, userId, dispatch]);

    const fetchUserData = useCallback(async () => {
        if (!channelId || !userId) return;
        dispatch({ type: actionTypes.FETCH_USER_DATA_START });
        try {
            const response = await window.jQuery.ajax({
                url: lc_initdata['ajaxurl'],
                type: 'POST',
                data: {
                    action: 'slc_fetch_user_data',
                    nonce: lc_initdata['ajax_nonce'],
                    channel_prefix: channelId,
                    line_id: userId,
                },
            });
            dispatch({ type: actionTypes.FETCH_USER_DATA_SUCCESS, payload: response });
        } catch (err) {
            dispatch({ type: actionTypes.FETCH_USER_DATA_FAILURE, payload: err.statusText });
        }
    }, [channelId, userId, dispatch]);

    const handleChannelSelect = (selectedChannelId) => {
        navigate(`/channel/${selectedChannelId}`);
    };

    const handleUserSelect = (selectedUserId) => {
        navigate(`/channel/${channelId}/user/${selectedUserId}`);
    };

    const handleMessageSend = () => {
        setIsSending(true);
        jQuery.ajax({
            type: "POST",
            url: lc_initdata['ajaxurl'],
            data: {
                action: 'lc_ajax_chat_send',
                nonce: lc_initdata['ajax_nonce'],
                messages: buildMessages,
                channel: channelId,
                to: userId,
                notificationDisabled: notificationDisabled ? 1 : 0,
            },
            dataType: 'json',
        }).done((data) => {
            setIsSending(false);
            setResults(data);
            fetchMessages();
        }).fail((XMLHttpRequest, textStatus, error) => {
            setIsSending(false);
            setResults({ error });
        });
    };

    // チャットメッセージフォームのトグル
    const toggleMessageForm = () => {
        setIsMessageFormOpen(prev => !prev);
        if (!isMessageFormOpen) {
            setResults(null); // フォームを開くときに結果をリセット
        }
    };

    const fetchOlder = useCallback(async () => {
        if (!channelId || !userId || !hasMore) return;
        dispatch({ type: actionTypes.FETCH_MESSAGES_START });
        try {
            const timestamp = messages.length > 0 ? messages[0].date : null;
            const response = await window.jQuery.ajax({
                url: lc_initdata['ajaxurl'],
                type: 'POST',
                data: {
                    action: 'slc_fetch_messages',
                    nonce: lc_initdata['ajax_nonce'],
                    channel_prefix: channelId,
                    user_id: userId,
                    timestamp: timestamp,
                },
            });
            if (response.success) {
                if (response.data.messages.length === 0) {
                    setHasMore(false);
                }
                dispatch({ type: actionTypes.FETCH_OLDER_MESSAGES_SUCCESS, payload: response.data.messages });
            }
        } catch (err) {
            dispatch({ type: actionTypes.FETCH_MESSAGES_FAILURE, payload: err.statusText });
        }
    }, [channelId, userId, dispatch, hasMore, messages]);

    return (
        <>
            {isMessageFormOpen && (
                <MessageForm
                    buildMessages={buildMessages}
                    setBuildMessages={setBuildMessages}
                    isSending={isSending}
                    notificationDisabled={notificationDisabled}
                    setNotificationDisabled={setNotificationDisabled}
                    results={results}
                    onSendMessage={handleMessageSend}
                    onClose={toggleMessageForm}
                />
            )}
            <div ref={containerRef} className="flex overflow-hidden">
                {/* Left Sidebar */}
                <div className="w-1/5 bg-gray-100 p-4 h-full overflow-y-auto">
                    <div className="mb-4">
                        {/* Channel Selector */}
                        <ChannelSelector channels={channels} selectedChannelId={channelId} onSelect={handleChannelSelect} />
                    </div>
                    {channelId && (
                        <div>
                            {/* User List */}
                            {isLoading && <p>{__('Loading users...')}</p>}
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
                                {isLoading && <p>{__('Loading messages...')}</p>}
                                {!isLoading && !error && <MessageArea messages={messages} isLoading={isLoading} onSendMessage={handleMessageSend} onMessageFormToggle={toggleMessageForm} hasMore={hasMore} fetchOlder={fetchOlder} />}
                            </div>
                        </>
                    ) : (
                        <div className="flex-1 flex items-center justify-center">
                            <p>{__('Please select a user to start chatting.')}</p>
                        </div>
                    )}
                </div>

                {/* Right Sidebar */}
                {userId && (
                    <div className="w-1/5 bg-gray-100 p-4 h-full overflow-y-auto">
                        {/* User Profile */}
                        <UserProfile user={selectedUser} />
                    </div>
                )}
            </div>
        </>
    );
};

export default ChatLayout;
