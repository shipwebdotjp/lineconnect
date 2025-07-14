import React, { useEffect, useRef } from 'react';
import PropTypes from 'prop-types';
import MessageBubble from '../molecules/MessageBubble';
import SystemBubble from '../molecules/SystemBubble';
import MessageInput from '../molecules/MessageInput';
const __ = wp.i18n.__;

const MessageArea = ({ messages = [], isLoading = false, onSendMessage }) => {
    const messagesEndRef = useRef(null);

    const scrollToBottom = () => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    };

    useEffect(() => {
        scrollToBottom();
    }, [messages]);

    return (
        <div className="chat-window">
            {isLoading ? (
                <div>{__('Loading...', 'lineconnect')}</div>
            ) : (
                messages.map((msg) => {
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
                })
            )}
            <div ref={messagesEndRef} />
            <MessageInput onSendMessage={onSendMessage} disabled={isLoading} />
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
