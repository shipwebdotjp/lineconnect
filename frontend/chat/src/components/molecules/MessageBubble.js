import React from 'react';
import PropTypes from 'prop-types';
import MessageBubbleText from '../atoms/MessageBubbleText';
import MessageBubbleImage from '../atoms/MessageBubbleImage';
import MessageBubbleVideo from '../atoms/MessageBubbleVideo';
import MessageBubbleAudio from '../atoms/MessageBubbleAudio';
import MessageBubbleFile from '../atoms/MessageBubbleFile';
import MessageBubbleLocation from '../atoms/MessageBubbleLocation';
import MessageBubbleSticker from '../atoms/MessageBubbleSticker';

const __ = wp.i18n.__;

const MessageBubble = ({ message }) => {
    const { type, message: messageContent, date, isMe } = message;

    const renderMessageContent = () => {
        switch (type) {
            case 1:
                return <MessageBubbleText text={messageContent.text} />;
            case 2:
                return <MessageBubbleImage file={messageContent.file_path} />;
            case 3:
                return <MessageBubbleVideo file={messageContent.file_path} />;
            case 4:
                return <MessageBubbleAudio file={messageContent.file_path} />;
            case 5:
                return <MessageBubbleFile file={messageContent.file_path} fileName={messageContent.fileName} fileSize={messageContent.fileSize} />;
            case 6:
                return (
                    <MessageBubbleLocation
                        address={messageContent.address}
                        latitude={messageContent.latitude}
                        longitude={messageContent.longitude}
                    />
                );
            case 7:
                return <MessageBubbleSticker />;
            default:
                return (
                    <div className="inline-block text-base leading-[180%] text-white/90 mb-1 max-w-full">
                        {__('(This is a message of an unsupported type.)', 'lineconnect')}
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
                    <div className="flex px-3 py-2 max-w-[80%] w-auto shadow-md bg-green-500 rounded-lg">
                        <div className="flex flex-col flex-1">
                            <div className="flex-1 max-w-full text-base leading-[180%] text-black/90">{renderMessageContent()}</div>
                        </div>
                    </div>
                </>
            ) : (
                <>
                    <div className="flex px-3 py-2 max-w-[80%] w-auto shadow-md bg-gray-800 rounded-lg">
                        <div className="flex flex-col flex-1">
                            <div className="flex-1 max-w-full text-base leading-[180%] text-white/90">{renderMessageContent()}</div>
                        </div>
                    </div>
                    <span className="text-black/40 text-xs ml-2">{date}</span>
                </>
            )}
        </div>
    );
};

MessageBubble.propTypes = {
    message: PropTypes.shape({
        id: PropTypes.number.isRequired,
        type: PropTypes.number.isRequired,
        message: PropTypes.object.isRequired,
        date: PropTypes.string.isRequired,
        isMe: PropTypes.bool.isRequired,
    }).isRequired,
};

export default MessageBubble;
