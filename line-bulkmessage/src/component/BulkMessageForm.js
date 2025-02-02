import React, { useState } from 'react';
import BulkMessage from './BulkMessage';
import BulkMessageAudience from './BulkMessageAudience';
import BulkMessageResult from './BulkMessageResult';

const __ = wp.i18n.__;
const BulkMessageForm = () => {
    const [messages, setMessage] = useState([]);
    const [audience, setAudience] = useState([]);
    const [results, setResults] = useState({});
    const [loadingStates, setLoadingStates] = useState({});
    const [notificationDisabled, setNotificationDisabled] = useState(false);

    const handleSubmit = (e) => {
        e.preventDefault();
        sendAjaxRequest('send');
    };

    const sendAjaxRequest = (mode) => {
        setLoadingStates((prev) => ({ ...prev, [mode]: true }));
        setResults((prev) => ({ ...prev, [mode]: null }));

        jQuery.ajax({
            type: "POST",
            url: lc_initdata['ajaxurl'],
            data: {
                'action': 'lc_ajax_chat_send',
                'nonce': lc_initdata['ajax_nonce'],
                'messages': messages,
                'audience': audience,
                'notificationDisabled': notificationDisabled,
                'mode': mode,
            },
            dataType: 'json'
        }).done((data) => {
            setResults((prev) => ({ ...prev, [mode]: data }));
        }).fail((XMLHttpRequest, textStatus, error) => {
            setResults((prev) => ({ ...prev, [mode]: { error: error } }));
        }).always(() => {
            setLoadingStates((prev) => ({ ...prev, [mode]: false }));
        });
    };

    return (
        <div className="ChatForm">
            <header className="ChatHeader text-lg mx-2 my-4 w-auto">
                {__('Send LINE bulk message', 'lineconnect')}			
            </header>
            <form onSubmit={handleSubmit}>
                <div className="ChatBody w-full bg-white">
                    <div className="ChatRow">
                        <BulkMessageAudience handleFormChange={setAudience} />
                    </div>
                    <div className="ChatRow px-4 py-2 my-2">
                        <button
                            type="button"
                            className="button button-secondary button-large"
                            disabled={loadingStates['count']}
                            onClick={() => sendAjaxRequest('count')}
                        >
                            {loadingStates['count'] ? (
                            <span className="flex items-center">
                                <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="black" strokeWidth="4"></circle>
                                    <path className="opacity-75" fill="black" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {__('Counting Recipients...', 'lineconnect')}
                            </span>
                        ) : (
                            __('Count Recipients', 'lineconnect')
                        )}
                        </button>
                        <BulkMessageResult result={results['count']} />
                    </div>
                    <div className="ChatRow">
                        <BulkMessage handleFormChange={setMessage} />
                    </div>
                    <div className="ChatRow px-4 py-2 my-2">
                        <div className="flex items-center">
                            <input 
                                type="checkbox"
                                id="notificationDisabled"
                                name="notificationDisabled"
                                checked={notificationDisabled}
                                onChange={(e) => setNotificationDisabled(e.target.checked)}
                            />
                            <label htmlFor="notificationDisabled" className="ml-2">
                                {__('Disable notification', 'lineconnect')}
                            </label>
                        </div>
                    </div>
                    <div className="ChatRow px-4 py-2 my-2 space-x-2">
                        <button
                            type="button"
                            className="button button-secondary button-large mr-2"
                            disabled={loadingStates['validate']}
                            onClick={() => sendAjaxRequest('validate')}
                        >
                            {loadingStates['validate'] ? (
                            <span className="flex items-center">
                                <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="black" strokeWidth="4"></circle>
                                    <path className="opacity-75" fill="black" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {__('Validating...', 'lineconnect')}
                            </span>
                            ) : (
                                __('Validate', 'lineconnect')
                            )}
                        </button>
                        <button
                            type="submit"
                            className="button button-primary button-large"
                            disabled={loadingStates['send']}
                        >
                            {loadingStates['send'] ? (
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
                        <BulkMessageResult result={results['validate']} />
                        <BulkMessageResult result={results['send']} />
                    </div>
                </div>
            </form>
        </div>
    );
};

export default BulkMessageForm;
