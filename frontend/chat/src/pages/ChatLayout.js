
import React, { useCallback, useContext, useEffect, useRef, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import ChannelSelector from '../components/molecules/ChannelSelector';
import UserList from '../components/organisms/UserList';
import { ChatContext, actionTypes } from '../context/ChatContext';
import MessageArea from '../components/organisms/MessageArea';
import MessageForm from '../components/organisms/MessageForm';
import UserProfile from '../components/organisms/UserProfile';
import UserDataEditForm from '../components/organisms/UserDataEditForm';
const __ = wp.i18n.__;

const ChatLayout = () => {
    const { channelId, userId } = useParams();
    const navigate = useNavigate();
    const { state, dispatch } = useContext(ChatContext);
    const { users, messages, isMessageLoading, isUserLoading, isUserDataLoading, error, selectedUser } = state;
    const [buildMessages, setBuildMessages] = useState([]);
    const [isSending, setIsSending] = useState(false);
    const [notificationDisabled, setNotificationDisabled] = useState(false);
    const [results, setResults] = useState(null);
    const [isMessageFormOpen, setIsMessageFormOpen] = useState(false);
    const [hasMoreMessage, setHasMoreMessage] = useState(true);
    const [hasMoreUser, setHasMoreUser] = useState(true);
    const [nextCursor, setNextCursor] = useState(null);
    const [isUserDataEdiitFormOpen, setIsUserDataEditFormOpen] = useState(false);
    const [userDataEditType, setUserDataEditType] = useState(null);
    const [userDataEditId, setUserDataEditId] = useState(null);

    const channels = lc_initdata['channels'];

    const containerRef = useRef(null);

    // チャネルIDがない場合、最初のチャネルにリダイレクト
    useEffect(() => {
        if (!channelId && channels && channels.length > 0) {
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
                    setHasMoreUser(response.data.has_more);
                    setNextCursor(response.data.next_cursor);
                    dispatch({ type: actionTypes.FETCH_USERS_SUCCESS, payload: response.data.users });
                } catch (err) {
                    dispatch({ type: actionTypes.FETCH_USERS_FAILURE, payload: err.statusText });
                }
            };
            setHasMoreUser(true);
            setNextCursor(null);
            fetchUsers();
        }
    }, [channelId, dispatch]);

    // ユーザー選択時にメッセージとユーザーデータをフェッチ
    useEffect(() => {
        if (channelId && userId) {
            dispatch({ type: actionTypes.RESET_CHAT_STATE });
            setHasMoreMessage(true);
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
                setHasMoreMessage(response.data.has_more);
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
        if (!channelId || !userId || !hasMoreMessage) return;
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
                setHasMoreMessage(response.data.has_more);
                dispatch({ type: actionTypes.FETCH_OLDER_MESSAGES_SUCCESS, payload: response.data.messages });
            }
        } catch (err) {
            dispatch({ type: actionTypes.FETCH_MESSAGES_FAILURE, payload: err.statusText });
        }
    }, [channelId, userId, dispatch, hasMoreMessage, messages]);

    // 更にユーザーリストを読み込む
    const fetchMoreUsers = useCallback(async () => {
        if (!channelId || !hasMoreUser || !nextCursor) return;
        dispatch({ type: actionTypes.FETCH_USERS_START });
        try {
            const response = await window.jQuery.ajax({
                url: lc_initdata['ajaxurl'],
                type: 'POST',
                data: {
                    action: 'slc_fetch_users',
                    nonce: lc_initdata['ajax_nonce'],
                    channel_prefix: channelId,
                    cursor: nextCursor,
                },
            });
            if (response.success) {
                setHasMoreUser(response.data.has_more);
                setNextCursor(response.data.next_cursor);
                dispatch({ type: actionTypes.FETCH_OLDER_USERS_SUCCESS, payload: response.data.users });
            }
        } catch (err) {
            dispatch({ type: actionTypes.FETCH_USERS_FAILURE, payload: err.statusText });
        }
    }, [channelId, dispatch, hasMoreUser, nextCursor]);

    //ユーザーデータ編集フォームを開く
    const openEditForm = (type, id = null) => {
        console.log(`Opening edit form for type: ${type} id: ${id}`);
        setIsUserDataEditFormOpen(true);
        setUserDataEditType(type);
        setUserDataEditId(id);
    };

    // ユーザーデータ編集のハンドラー
    const handleUserDataEdit = async (data) => {
        if (!selectedUser || !userDataEditType) return;
        try {
            const response = await window.jQuery.ajax({
                url: lc_initdata['ajaxurl'],
                type: 'POST',
                data: {
                    action: 'slc_edit_user_data',
                    nonce: lc_initdata['ajax_nonce'],
                    channel_prefix: channelId,
                    line_id: selectedUser.lineId,
                    type: userDataEditType,
                    id: userDataEditId,
                    data: data,
                },
            });
            if (response.success) {
                // ユーザーデータの更新に成功した場合、再度ユーザーデータをフェッチ
                fetchUserData();
                setIsUserDataEditFormOpen(false);
            } else {
                console.error('Failed to update user data:', response.data);
            }
        } catch (error) {
            console.error('Error updating user data:', error);
        }
    };

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
            {isUserDataEdiitFormOpen && (
                <UserDataEditForm
                    user={selectedUser}
                    type={userDataEditType}
                    id={userDataEditId}
                    onEdit={handleUserDataEdit}
                    onClose={() => setIsUserDataEditFormOpen(false)}
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
                            {!error && <UserList users={users} selectedUserId={userId} onSelectUser={handleUserSelect} isLoading={isUserLoading} hasMore={hasMoreUser} fetchMore={fetchMoreUsers} />}
                        </div>
                    )}
                </div>

                {/* Main Content */}
                <div className="flex-1 flex flex-col h-full ">
                    {userId ? (
                        <>
                            {/* Message Area */}
                            {!error ? (
                                <MessageArea
                                    messages={messages}
                                    isLoading={isMessageLoading}
                                    onSendMessage={handleMessageSend}
                                    onMessageFormToggle={toggleMessageForm}
                                    hasMore={hasMoreMessage}
                                    fetchOlder={fetchOlder}
                                />
                            ) : (
                                <p>{__('Failed to load messages.', 'lineconnect')}</p>
                            )}
                        </>
                    ) : (
                        <div className="flex-1 flex items-center justify-center">
                            <p>{__('Please select a user to start chatting.', 'lineconnect')}</p>
                        </div>
                    )}
                </div>

                {/* Right Sidebar */}
                {userId && (
                    <div className="w-1/5 bg-gray-100 p-4 h-full overflow-y-auto">
                        {/* User Profile */}
                        <UserProfile user={selectedUser} isLoading={isUserDataLoading} openEditForm={openEditForm} />
                    </div>
                )}
            </div>
        </>
    );
};

export default ChatLayout;
