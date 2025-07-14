import React from 'react';

const MessageBubbleVideo = ({ file = '', url = '' }) => {
    const videoUrl = lc_initdata['downloadurl'] + '&file=' + encodeURIComponent(file);
    return (
        <video className="inline-block mb-1 max-w-64" controls>
            <source src={url || videoUrl} type="video/mp4" />
            Your browser does not support the video tag.
        </video>
    );
};

export default MessageBubbleVideo;
