import React, { useContext } from 'react';
import { ChatContext, actionTypes } from '../../context/ChatContext';

const MessageFormResult = () => {
    const { state, dispatch } = useContext(ChatContext);
    const { error } = state;
    if (error) {
        return (
            <>
                {error.success.length > 0 && (
                    <div className='py-2 px-4 my-2 bg-green-100 border border-green-500 w-full max-w-screen-sm'>
                        <ul>
                            {error.success.map((value, index) => {
                                return (
                                    <li key={index} className="p-2 my-1 mr-2" dangerouslySetInnerHTML={{ __html: value }} />
                                );
                            })}
                        </ul>
                    </div>
                )}
                {error.error.length > 0 && (
                    <div className='py-2 px-4 my-2 bg-red-100 border border-red-500 w-full max-w-screen-sm'>
                        <ul>
                            {error.error.map((value, index) => {
                                return (
                                    <li key={index} className="p-2 my-1 mr-2" dangerouslySetInnerHTML={{ __html: value }} />
                                );
                            })}
                        </ul>
                    </div>
                )}
            </>

        )
    } else {
        return (<></>)
    }
}

export default MessageFormResult
