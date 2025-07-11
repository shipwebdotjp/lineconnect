const MessageBubbleText = ({ text }) => {
    const last = text.split('\n').length - 1;
    return text.split('\n').map((line, i) => (
        <React.Fragment key={i}>
            {line}
            {i !== last && <br />}
        </React.Fragment>
    ));
};

export default MessageBubbleText;