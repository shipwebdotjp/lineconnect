import React, { useContext } from 'react';
import { ChatContext, actionTypes } from '../../context/ChatContext';


const __ = wp.i18n.__;

const MessageInput = () => {
    const { state, dispatch } = useContext(ChatContext);
    const { isMessageFormOpen } = state;

    const handleMessageFormOpen = (e) => {
        e.preventDefault();
        dispatch({ type: actionTypes.TOGGLE_MESSAGE_FORM, payload: !isMessageFormOpen });
    }

    return (
        <div className="p-4">
            <button onClick={handleMessageFormOpen} className="bg-blue-500 text-white p-4 rounded w-full">
                {isMessageFormOpen ? __('Cancel', 'lineconnect') : __('Open Message Input Form', 'lineconnect')}
            </button>
        </div>
    );
};


export default MessageInput;
