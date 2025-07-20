import React, { useContext } from 'react';

const __ = wp.i18n.__;

const MessageInput = ({ onMessageFormToggle }) => {

    const handleMessageFormOpen = (e) => {
        e.preventDefault();
        onMessageFormToggle();
    }

    return (
        <div className="p-4">
            <button onClick={handleMessageFormOpen} className="bg-blue-500 text-white p-4 rounded w-full">
                {__('Open Message Input Form', 'lineconnect')}
            </button>
        </div>
    );
};


export default MessageInput;
