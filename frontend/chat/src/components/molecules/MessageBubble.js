import React from 'react';
import PropTypes from 'prop-types';
import MessageBubbleText from '../atoms/MessageBubbleText';
import MessageBubbleImage from '../atoms/MessageBubbleImage';
import MessageBubbleVideo from '../atoms/MessageBubbleVideo';
import MessageBubbleAudio from '../atoms/MessageBubbleAudio';
import MessageBubbleFile from '../atoms/MessageBubbleFile';
import MessageBubbleLocation from '../atoms/MessageBubbleLocation';
import MessageBubbleSticker from '../atoms/MessageBubbleSticker';
import MessageBubbleFlex from '../atoms/MessageBubbleFlex'

const __ = wp.i18n.__;

const MessageBubble = ({ type, message, date, isMe }) => {
    // const { type, message: message, date, isMe } = message;

    const renderMessageContent = () => {
        const defaultClass = 'rounded-lg shadow-md ' + (isMe ? 'bg-green-500' : 'bg-gray-800');
        switch (type) {
            case 1:
            case 'text':
            case 'textV2':
                return <MessageBubbleText text={message.text} className={defaultClass} />;
            case 2:
                return <MessageBubbleImage file={message.file_path} className={defaultClass} />;
            case 'image':
                return <MessageBubbleImage url={message.originalContentUrl} className={defaultClass} />;
            case 91:
            case 'imagemap':
                return <MessageBubbleImage url={message.baseUrl} className={defaultClass} />;
            case 3:
                return <MessageBubbleVideo file={message.file_path} className={defaultClass} />;
            case 'video':
                return <MessageBubbleVideo url={message.originalContentUrl} className={defaultClass} />;
            case 4:
                return <MessageBubbleAudio file={message.file_path} className={defaultClass} />;
            case 'audio':
                return <MessageBubbleAudio url={message.originalContentUrl} className={defaultClass} />;
            case 5:
                return <MessageBubbleFile file={message.file_path} fileName={message.fileName} fileSize={message.fileSize} className={defaultClass} />;
            case 6:
            case 'location':
                return (
                    <MessageBubbleLocation
                        address={message.address}
                        latitude={message.latitude}
                        longitude={message.longitude}
                        title={message.title || __('Location', 'lineconnect')}
                        className={defaultClass}
                    />
                );
            case 7:
            case 'sticker':
                return <MessageBubbleSticker className={defaultClass} />;
            case 92:
            case 'template':
                return (
                    <div className={`${defaultClass} p-2 inline-block text-base leading-[180%] text-white/90 mb-1 max-w-full`}>
                        {'(' + (message.altText || __('(Template message.)', 'lineconnect')) + ')'}
                    </div>
                );
            case 93:
            case 'flex':
                return (
                    <MessageBubbleFlex flexJSON={message.contents} />
                );
            default:
                return (
                    <div className={`${defaultClass} p-2 inline-block text-base leading-[180%] text-white/90 mb-1 max-w-full`}>
                        {__('(Unsupported message.)', 'lineconnect')}
                    </div>
                );
        }
    };

    return (
        <div
            className={`flex items-end w-full mb-2.5 ${isMe ? 'justify-end' : 'justify-start'
                }`}
        >
            {isMe ? (
                <>
                    <span className="text-black/40 text-xs mr-2">{date}</span>
                    <div className="flex max-w-[80%] w-auto">
                        <div className="flex flex-col flex-1">
                            <div className="flex-1 max-w-full text-base leading-[180%] text-black/90 whitespace-pre-wrap wrap-break-word break-all">{renderMessageContent()}</div>
                        </div>
                    </div>
                </>
            ) : (
                <>
                    <div className="flex max-w-[80%] w-auto">
                        <div className="flex flex-col flex-1">
                            <div className="flex-1 max-w-full text-base leading-[180%] text-white/90 whitespace-pre-wrap wrap-break-word break-all">{renderMessageContent()}</div>
                        </div>
                    </div>
                    <span className="text-black/40 text-xs ml-2">{date}</span>
                </>
            )}
        </div>
    );
};

MessageBubble.propTypes = {
    type: PropTypes.oneOfType([PropTypes.string, PropTypes.number]),
    message: PropTypes.object.isRequired,
    date: PropTypes.string.isRequired,
    isMe: PropTypes.bool.isRequired,
};

export default MessageBubble;
