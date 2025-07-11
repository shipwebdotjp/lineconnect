import React, { useEffect, useRef } from 'react';
import PropTypes from 'prop-types';
import MessageBubble from '../molecules/MessageBubble';
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
                messages.map((msg) => <MessageBubble key={msg.id} message={msg} />)
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
            type: PropTypes.number.isRequired,
            message: PropTypes.object.isRequired,
            date: PropTypes.string.isRequired,
            isMe: PropTypes.bool.isRequired,
        })
    ).isRequired,
    isLoading: PropTypes.bool.isRequired,
};

export default MessageArea;
