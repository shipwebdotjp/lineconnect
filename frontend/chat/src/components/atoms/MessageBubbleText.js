import React from 'react';
const __ = wp.i18n.__;


const TextWithLineBreaks = ({ text, last }) => {
    return (
        <span>
            {text.split('\n').map((line, index) => (
                <span key={index}>
                    {line}
                    {index === last ? '' : <br />}
                </span>
            ))}
        </span>
    );
};

const MessageBubbleText = ({ text, className }) => {
    const last = text && text.length > 0 ? text.split('\n').length - 1 : 0;
    return (
        <div className={`${className} p-2`}>
            {text && text.length > 0 ? (
                <TextWithLineBreaks text={text} last={last} />
            ) : (
                <div className="text-gray-500 italic">
                    {__('(No text provided)', 'lineconnect')}
                </div>
            )}
        </div>
    );
};

export default MessageBubbleText;