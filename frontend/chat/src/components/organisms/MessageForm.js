import React, { useContext } from 'react';
import { ChatContext, actionTypes } from '../../context/ChatContext';

import MessageFormBuilder from '../molecules/MessageFormBuilder';
import MessageFormResult from '../molecules/MessageFormResult';

const __ = wp.i18n.__;
const MessageForm = ({ onSendMessage }) => {
    const { state, dispatch } = useContext(ChatContext);
    const { buildMessages, isSending, notificationDisabled } = state;

    return (
        <div className="relative z-10" aria-labelledby="dialog-title" role="dialog" aria-modal="true">
            <div className="fixed inset-0 bg-gray-500/75 transition-opacity" aria-hidden="true"></div>
            <div className="fixed inset-0 z-10 flex items-center justify-center p-4">
                <div className="ChatForm w-4/5 max-h-full overflow-y-auto bg-white rounded-lg shadow-xl">
                    <header className="ChatHeader text-lg mx-2 my-4 w-auto flex items-center">
                        <h2 id="dialog-title" className="font-semibold">
                            {__('Send LINE chat message', 'lineconnect')}
                        </h2>
                        <button
                            type="button"
                            className="ml-auto text-gray-500 hover:text-gray-700 focus:outline-none"
                            onClick={() => dispatch({ type: actionTypes.TOGGLE_MESSAGE_FORM })}
                        >
                            <svg className="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </header>
                    <div>
                        <div className="ChatBody w-full">

                            <div className="ChatRow">
                                <MessageFormBuilder />
                            </div>
                            <div className="ChatRow px-4 py-2 my-2">
                                <div className="flex items-center">
                                    <input
                                        type="checkbox"
                                        id="notificationDisabled"
                                        name="notificationDisabled"
                                        checked={notificationDisabled}
                                        onChange={() => dispatch({ type: actionTypes.TOGGLE_NOTIFICATION_DISABLED })}
                                    />
                                    <label htmlFor="notificationDisabled" className="ml-2">
                                        {__('Disable notification', 'lineconnect')}
                                    </label>
                                </div>
                            </div>
                            <div className="ChatRow px-4 py-2 mt-2">
                                <div className="space-x-2">
                                    <button
                                        type="button"
                                        onClick={() => dispatch({ type: actionTypes.TOGGLE_MESSAGE_FORM })}
                                        className="button button-secondary button-large"
                                    >
                                        {__('Close', 'lineconnect')}
                                    </button>
                                    <button
                                        type="button"
                                        onClick={onSendMessage}
                                        className="button button-primary button-large"
                                        disabled={isSending}
                                    >
                                        {isSending ? (
                                            <span className="flex items-center">
                                                <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="black" strokeWidth="4"></circle>
                                                    <path className="opacity-75" fill="black" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                {__('Sending...', 'lineconnect')}
                                            </span>
                                        ) : (
                                            __('Send', 'lineconnect')
                                        )}
                                    </button>
                                </div>
                                <div>
                                    <MessageFormResult />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default MessageForm;
