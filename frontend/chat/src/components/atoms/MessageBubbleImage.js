const MessageBubbleImage = ({ file }) => {
    const imageUrl = lc_initdata['downloadurl'] + '&file=' + encodeURIComponent(file);
    return <img className="inline-block mb-1 max-w-64" src={imageUrl} alt="{file}" />;
};
export default MessageBubbleImage;