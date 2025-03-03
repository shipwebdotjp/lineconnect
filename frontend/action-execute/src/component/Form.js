import React, { useState } from 'react';
import Audience from './Audience';
import ActionFlow from './ActionFlow';
import Result from './Result';

const __ = wp.i18n.__;

const Form = () => {
    const [audience, setAudience] = useState([]);
    const [actionFlow, setActionFlow] = useState([]);
    const [results, setResults] = useState({});
    const [loadingStates, setLoadingStates] = useState({});

    const handleSubmit = (e) => {
        e.preventDefault();
        sendAjaxRequest('execute');
    };

    const sendAjaxRequest = (mode) => {
        setLoadingStates((prev) => ({ ...prev, [mode]: true }));
        setResults((prev) => ({ ...prev, [mode]: null }));

        jQuery.ajax({
            type: "POST",
            url: lc_initdata['ajaxurl'],
            data: {
                'action': 'lc_ajax_action_execute',
                'nonce': lc_initdata['ajax_nonce'],
                'audience': audience,
                'actionFlow': actionFlow,
                'mode': mode,
            },
            dataType: 'json'
        }).done((data) => {
            setResults((prev) => ({ ...prev, [mode]: data }));
        }).fail((XMLHttpRequest, textStatus, error) => {
            setResults((prev) => ({ ...prev, [mode]: { error: error, message: XMLHttpRequest.responseText } }));
        }).always(() => {
            setLoadingStates((prev) => ({ ...prev, [mode]: false }));
        });
    };

    return (
        <div className="ActionExecuteForm">
            <header className="ActionHeader text-lg mx-2 my-4 w-auto">
                {__('Execute Action Flow', 'lineconnect')}
            </header>
            <form onSubmit={handleSubmit}>
                <div className="ActionBody w-full bg-white">
                    <div className="ActionRow">
                        <Audience handleFormChange={setAudience} />
                    </div>
                    <div className="ActionRow px-4 py-2 my-2">
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
                        <Result result={results['count']} />
                    </div>
                    <div className="ActionRow">
                        <ActionFlow handleFormChange={setActionFlow} />
                    </div>
                    <div className="ActionRow px-4 py-2 mt-2">
                        <div className="space-x-2">
                            <button
                                type="submit"
                                className="button button-primary button-large"
                                disabled={loadingStates['execute']}
                            >
                                {loadingStates['execute'] ? (
                                    <span className="flex items-center">
                                        <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="black" strokeWidth="4"></circle>
                                            <path className="opacity-75" fill="black" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        {__('Executing...', 'lineconnect')}
                                    </span>
                                ) : (
                                    __('Execute', 'lineconnect')
                                )}
                            </button>
                        </div>
                        <div>
                            <Result result={results['execute']} />
                        </div>
                    </div>
                </div>
            </form>
        </div>
    );
};

export default Form;