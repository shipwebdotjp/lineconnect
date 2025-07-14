import React from 'react';
const __ = wp.i18n.__;

const MessageBubbleSticker = () => {
    return (
        <div className="inline-block text-white/90 mb-1 max-w-full">
            {__('(This is a sticker message.)', 'lineconnect')}
        </div>
    );
};

export default MessageBubbleSticker;
