import React from 'react';

const MessageBubbleLocation = ({ address, latitude, longitude, title = '' }) => {
    const mapUrl = `https://www.google.com/maps?q=${latitude},${longitude}`;
    return (
        <div className="inline-block mb-1 max-w-full">
            {title && <div>{title}</div>}
            {address && <div>{address}</div>}
            <a href={mapUrl} target="_blank" rel="noopener noreferrer">
                {latitude}, {longitude}
            </a>
        </div>
    );
};

export default MessageBubbleLocation;
