const MessageBubbleImage = ({ file = '', url = '', className = '' }) => {
    const safeFile = file ?? '';
    const safeUrl = url ?? '';
    const imageUrl = safeFile
        ? lc_initdata['downloadurl'] + '&file=' + encodeURIComponent(safeFile)
        : '';
    const src = safeUrl || imageUrl;

    if (!src) return null; // どちらもない場合は何も表示しない

    return (
        <img
            className={`inline-block mb-1 max-w-64 ${className}`}
            src={src}
            alt={safeFile}
        />
    );
};
export default MessageBubbleImage;