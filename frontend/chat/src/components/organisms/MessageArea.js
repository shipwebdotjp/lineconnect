import React, { useState, useEffect, useRef, useCallback } from 'react';
import PropTypes from 'prop-types';
import MessageBubble from '../molecules/MessageBubble';
import SystemBubble from '../molecules/SystemBubble';
import MessageInput from '../molecules/MessageInput';
const __ = wp.i18n.__;

const MessageArea = ({ messages = [], isLoading = false, onMessageFormToggle, hasMore, fetchOlder }) => {
    const messagesEndRef = useRef(null);
    const containerRef = useRef(null);
    // const topSentinelRef = useRef(null);
    const [showed, setShowed] = useState(false);

    const scrollToBottom = useCallback(() => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    }, []);

    const waitForContentLoad = useCallback(() => {
        const container = containerRef.current;
        if (!container) return;

        // 画像やビデオが全て読み込まれるまで待つ
        const images = container.querySelectorAll('img');
        const videos = container.querySelectorAll('video');
        const allMedia = [...images, ...videos];

        if (allMedia.length === 0) {
            scrollToBottom();
            setShowed(true);
            return;
        }

        let loadedCount = 0;
        const totalCount = allMedia.length;

        const handleLoad = () => {
            loadedCount++;
            if (loadedCount === totalCount) {
                scrollToBottom();
                setShowed(true);

            }
        };

        allMedia.forEach((media) => {
            if (media.complete || media.readyState >= 3) {
                handleLoad();
            } else {
                media.addEventListener('load', handleLoad, { once: true });
                media.addEventListener('loadeddata', handleLoad, { once: true });
            }
        });

        // タイムアウト設定（5秒後に強制実行）
        setTimeout(() => {
            if (loadedCount < totalCount) {
                scrollToBottom();
            }
        }, 5000);
    }, [scrollToBottom]);

    useEffect(() => {
        // DOM更新を待ってからコンテンツロードを確認
        const timer = setTimeout(() => {
            waitForContentLoad();
        }, 100);

        return () => clearTimeout(timer);
    }, [messages, waitForContentLoad]);


    return (
        <div className="h-full flex flex-col">
            <div className="flex-1 overflow-y-auto" ref={containerRef}>

                {isLoading ? (
                    <div>{__('Loading...', 'lineconnect')}</div>
                ) : (
                    <>
                        {hasMore && (
                            <button className="w-full text-center bg-blue-200 mb-4 p-4 text-blue-500 hover:bg-blue-100" onClick={fetchOlder}>
                                {__('Load older messages', 'lineconnect')}
                            </button>
                        )}
                        {messages.map((msg) => {
                            if (msg.event_type === 1 || msg.event_type >= 91) {
                                if (Array.isArray(msg.message)) {
                                    return msg.message.map((message, idx) => (
                                        <MessageBubble key={msg.id + '-' + idx} type={message.type} message={message} date={msg.date} isMe={msg.isMe} />
                                    ));
                                } else if (msg.message && typeof msg.message === 'object') {
                                    return <MessageBubble key={msg.id} type={msg.type} message={msg.message} date={msg.date} isMe={msg.isMe} />;
                                }
                            } else if (msg.event_type >= 2 && msg.event_type < 91) {
                                return <SystemBubble key={msg.id} event={msg} />;
                            }
                        })}
                    </>
                )}
                <div ref={messagesEndRef} />
                <MessageInput onMessageFormToggle={onMessageFormToggle} />
            </div>
        </div>
    );
};

MessageArea.propTypes = {
    messages: PropTypes.arrayOf(
        PropTypes.shape({
            id: PropTypes.number.isRequired,
            event_type: PropTypes.number.isRequired,
        })
    ).isRequired,
    isLoading: PropTypes.bool.isRequired,
    onSendMessage: PropTypes.func.isRequired,
};

export default MessageArea;
