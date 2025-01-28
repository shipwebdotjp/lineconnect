import React, { useState } from 'react';
// import ChatTo from './ChatTo';
import BulkMessage from './BulkMessage';
import BulkMessageAudience from './BulkMessageAudience';
// import ChatChannel from './ChatChannel';
import BulkMessageResult from './BulkMessageResult';

const __ = wp.i18n.__;
const BulkMessageForm = () => {
    const [messages, setMessage] = useState(new Array());
    const [audience, setAudience] = useState(new Array());
    const [result, setResult] = useState(new Array());

    const handleSubmit = (e) => {
        console.log(e.target);
        e.preventDefault();
        sendAjaxRequest('send');
    };

    const sendAjaxRequest = (mode) => {        
        jQuery.ajax({
            type: "POST",
            url: lc_initdata['ajaxurl'], // admin-ajax.php のURLが格納された変数
            data: {
                'action': 'lc_ajax_chat_send',
                'nonce': lc_initdata['ajax_nonce'],
                'messages': messages,
                'audience': audience,
                'mode': mode,
            },
            dataType: 'json'
        }).done(function (data) {
            setResult(data);
        }).fail(function (XMLHttpRequest, textStatus, error) {
            setResult({ "result": "failed", "error": [error, XMLHttpRequest.responseText] });
        });        
    }


    return <div className="ChatForm">
        <header className="ChatHeader text-lg mx-2 my-2 w-auto">
            {__('Send LINE message', 'lineconnect')}			
        </header>
        <BulkMessageResult result={result} />
        <form onSubmit={handleSubmit}>
            <div className="ChatBody w-full bg-white">
                <div className="ChatRow">
                    <BulkMessageAudience handleFormChange={setAudience} />
                </div>
                <div className="ChatRow px-4 py-2 my-2">
                    <button type="button" className="button button-secondary button-large" onClick={() => sendAjaxRequest('count')}>{__('Count Recipients', 'lineconnect')}</button>
                </div>
                <div className="ChatRow">
                    <BulkMessage handleFormChange={setMessage} />
                </div>
                <div className="ChatRow px-4 py-2 my-2">
                    <button type="submit" className="button button-primary button-large">{__('Send', 'lineconnect')}</button>
                </div>
            </div>
        </form>
    </div>
}

export default BulkMessageForm
