import React from 'react';

const MessageBubbleAudio = ({ file = '', url = '', className = '' }) => {
    const safeFile = file ?? '';
    const safeUrl = url ?? '';
    const audioUrl = safeFile
        ? lc_initdata['downloadurl'] + '&file=' + encodeURIComponent(safeFile)
        : '';
    const src = safeUrl || audioUrl;

    if (!src) return null;

    return (
        <audio className={`inline-block mb-1 max-w-full ${className}`} controls src={src}>
            Your browser does not support the audio element.
        </audio>
    );
};

export default MessageBubbleAudio;
