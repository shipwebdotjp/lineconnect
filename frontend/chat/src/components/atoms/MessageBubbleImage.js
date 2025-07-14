const MessageBubbleImage = ({ file = '', url = '' }) => {
    const imageUrl = lc_initdata['downloadurl'] + '&file=' + encodeURIComponent(file);
    return <img className="inline-block mb-1 max-w-64" src={url || imageUrl} alt="{file}" />;
};
export default MessageBubbleImage;