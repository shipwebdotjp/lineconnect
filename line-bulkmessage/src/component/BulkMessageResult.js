import React, { useState } from 'react';

const BulkMessageResult = (props) => {
    if (props.result.hasOwnProperty("result")) {
        return (
            <>
                {props.result.success.length > 0 && (
                    <div className='py-2 px-4 my-2 bg-green-100 border border-green-500 w-1/3'>
                        <ul>
                            {props.result.success.map((value, index) => {
                                return (
                                    <li key={index} className="p-2 my-1 mr-2">
                                        {value}
                                    </li>
                                );
                            })}
                        </ul>
                    </div>
                )}
                {props.result.error.length > 0 && (
                    <div className='py-2 px-4 my-2 bg-red-100 border border-red-500 w-1/3'>
                        <ul>
                            {props.result.error.map((value, index) => {
                                return (
                                    <li key={index} className="p-2 my-1 mr-2">
                                        {value}
                                    </li>
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

export default BulkMessageResult