
import React, { useState, useEffect } from 'react';

const __ = wp.i18n.__;
const ChatMessage = (props) => {
    return <>
        <div className="py-2 px-4 bg-blue-200"><label htmlFor="chat-message">{__('Message', 'lineconnect')}</label></div>
        <textarea id="chat-message" name="message" type="text" value={props.defaultValue} onChange={(e) => props.handleMessageChange(e.target.value)} className="p-2 my-2 w-full h-36 border-indigo-600"></textarea>
    </>
}

export default ChatMessage