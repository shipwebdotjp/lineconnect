import React from 'react';

const MessageFormResult = ({ results }) => {
    const { success = [], error = [] } = results || {};
    if (success.length > 0 || error.length > 0) {
        return (
            <>
                {success.length > 0 && (
                    <div className='py-2 px-4 my-2 bg-green-100 border border-green-500 w-full max-w-screen-sm'>
                        <ul>
                            {success.map((value, index) => {
                                return (
                                    <li key={index} className="p-2 my-1 mr-2" dangerouslySetInnerHTML={{ __html: value }} />
                                );
                            })}
                        </ul>
                    </div>
                )}
                {error.length > 0 && (
                    <div className='py-2 px-4 my-2 bg-red-100 border border-red-500 w-full max-w-screen-sm'>
                        <ul>
                            {error.map((value, index) => {
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
