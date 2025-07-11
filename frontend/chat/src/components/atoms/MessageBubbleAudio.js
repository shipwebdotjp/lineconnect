import React from 'react';

const MessageBubbleAudio = ({ file }) => {
    const audioUrl = lc_initdata['downloadurl'] + '&file=' + encodeURIComponent(file);
    return (
        <audio className="inline-block mb-1 max-w-full" controls src={audioUrl}>
            Your browser does not support the audio element.
        </audio>
    );
};

export default MessageBubbleAudio;
