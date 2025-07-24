import React, { useState, useEffect, useRef, useCallback, useLayoutEffect } from 'react';
import PropTypes from 'prop-types';
import MessageBubble from '../molecules/MessageBubble';
import SystemBubble from '../molecules/SystemBubble';
import MessageInput from '../molecules/MessageInput';
const __ = wp.i18n.__;

const MessageArea = ({ messages = [], isLoading = false, onMessageFormToggle, hasMore, fetchOlder }) => {
    const containerRef = useRef(null);
    const prevMessagesLength = useRef(messages.length);
    const prevScrollHeight = useRef(0);
    const isFetchingOlder = useRef(false);

    const scrollToBottom = useCallback((behavior = 'auto') => {
        const container = containerRef.current;
        if (!container) return;

        const images = container.querySelectorAll('img');
        const videos = container.querySelectorAll('video');
        const allMedia = [...images, ...videos];

        const executeScroll = () => {
            if (container) {
                container.scrollTo({ top: container.scrollHeight, behavior });
            }
        };

        if (allMedia.length === 0) {
            executeScroll();
            return;
        }

        let loadedCount = 0;
        const totalCount = allMedia.length;

        const handleLoad = () => {
            loadedCount++;
            if (loadedCount === totalCount) {
                executeScroll();
            }
        };

        allMedia.forEach((media) => {
            if (media.complete || media.readyState >= 3) {
                handleLoad();
            } else {
                media.addEventListener('load', handleLoad, { once: true });
                media.addEventListener('loadeddata', handleLoad, { once: true });
                media.addEventListener('error', handleLoad, { once: true });
            }
        });

        setTimeout(() => {
            if (loadedCount < totalCount) {
                executeScroll();
            }
        }, 3000);
    }, []);

    useLayoutEffect(() => {
        const container = containerRef.current;
        if (!container) return;

        const currentMessagesLength = messages.length;
        const currentScrollHeight = container.scrollHeight;

        if (isFetchingOlder.current) {
            // 古いメッセージを読み込んだ後の処理
            const heightDiff = currentScrollHeight - prevScrollHeight.current;
            if (heightDiff > 0) {
                container.scrollTop += heightDiff;
            }
            isFetchingOlder.current = false;
        } else if (currentMessagesLength > prevMessagesLength.current) {
            // 新しいメッセージが追加された場合
            const { scrollTop, clientHeight } = container;
            const wasAtBottom = prevScrollHeight.current - scrollTop - clientHeight <= 20;
            if (wasAtBottom) {
                scrollToBottom('smooth');
            }
        } else if (prevMessagesLength.current === 0 && currentMessagesLength > 0) {
            // 初回ロード
            scrollToBottom();
        }

        prevMessagesLength.current = currentMessagesLength;
        prevScrollHeight.current = currentScrollHeight;

    }, [messages, scrollToBottom]);

    const handleFetchOlder = () => {
        if (isLoading) return;
        prevScrollHeight.current = containerRef.current?.scrollHeight || 0;
        isFetchingOlder.current = true;
        fetchOlder();
    };

    return (
        <div className="h-full flex flex-col">
            <div className="flex-1 px-4 py-2 h-full overflow-y-auto" ref={containerRef}>
                {hasMore && (
                    <button 
                        className="w-full text-center bg-blue-200 mb-4 p-4 text-blue-500 hover:bg-blue-100 disabled:opacity-50"
                        onClick={handleFetchOlder}
                        disabled={isLoading}
                    >
                        {isLoading ? __('Loading...', 'lineconnect') : __('Load older messages', 'lineconnect')}
                    </button>
                )}
                {messages.map((msg) => {
                    if (msg.event_type === 1 || msg.event_type >= 91) {
                        if (Array.isArray(msg.message)) {
                            return msg.message.map((message, idx) => (
                                <MessageBubble key={`${msg.id}-${idx}`} type={message.type} message={message} date={msg.date} isMe={msg.isMe} />
                            ));
                        } else if (msg.message && typeof msg.message === 'object') {
                            return <MessageBubble key={msg.id} type={msg.type} message={msg.message} date={msg.date} isMe={msg.isMe} />;
                        }
                    } else if (msg.event_type >= 2 && msg.event_type < 91) {
                        return <SystemBubble key={msg.id} event={msg} />;
                    }
                    return null;
                })}
            </div>
            <MessageInput onMessageFormToggle={onMessageFormToggle} />
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
    onMessageFormToggle: PropTypes.func.isRequired,
    hasMore: PropTypes.bool,
    fetchOlder: PropTypes.func,
};

export default MessageArea;