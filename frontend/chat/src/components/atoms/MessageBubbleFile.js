import React from 'react';

const MessageBubbleFile = ({ file, fileName, fileSize }) => {
    const fileUrl = lc_initdata['downloadurl'] + '&file=' + encodeURIComponent(file);
    const filename = fileName || file.split('/').pop();
    const filesize = fileSize ? `${fileSize} bytes` : '';
    return (
        <div className="block mb-1 max-w-full">
            <a className="" href={fileUrl} download>
                {filename}
            </a>
            <div className="text-xs text-gray-500">{filesize}</div>
        </div>
    );
};

export default MessageBubbleFile;
