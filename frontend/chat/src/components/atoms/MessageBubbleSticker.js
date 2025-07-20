import React from 'react';
const __ = wp.i18n.__;

const MessageBubbleSticker = ({ className }) => {
    return (
        <div className={`inline-block p-2 text-white/90 mb-1 max-w-full ${className}`}>
            {__('(This is a sticker message.)', 'lineconnect')}
        </div>
    );
};

export default MessageBubbleSticker;
