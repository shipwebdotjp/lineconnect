import React, { useState } from 'react';
import PropTypes from 'prop-types';

const __ = wp.i18n.__;

const MessageInput = ({ onSendMessage, disabled }) => {
    const [message, setMessage] = useState('');

    const handleSubmit = (e) => {
        e.preventDefault();
        if (message.trim() && !disabled) {
            onSendMessage(message);
            setMessage('');
        }
    };

    return (
        <form onSubmit={handleSubmit} className="chat-input">
            <input
                type="text"
                value={message}
                onChange={(e) => setMessage(e.target.value)}
                placeholder={__('Type a message...', 'lineconnect')}
                disabled={disabled}
            />
            <button type="submit" disabled={disabled || !message.trim()}>
                {__('Send', 'lineconnect')}
            </button>
        </form>
    );
};

MessageInput.propTypes = {
    onSendMessage: PropTypes.func.isRequired,
    disabled: PropTypes.bool.isRequired,
};

export default MessageInput;
