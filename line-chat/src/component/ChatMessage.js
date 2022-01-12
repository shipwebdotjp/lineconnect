
import React, { useState, useEffect } from 'react';

const ChatMessage = (props) => {
    //const [message, setValue] = useState('');
    /*
        const handleChange = (e) => {
            setValue(e.target.value);
        }
    */
    /*
    useEffect(() => {
        document.title = `Your message ${message}`;
    })
    */

    return <>
        <div className="py-2 px-4 bg-blue-200"><label htmlFor="chat-message">Message</label></div>
        <textarea id="chat-message" name="message" type="text" value={props.defaultValue} onChange={(e) => props.handleMessageChange(e.target.value)} className="p-2 my-2 w-full h-36 border-indigo-600"></textarea>
    </>
}

export default ChatMessage