const ChatTo = (props) => {
    return <>
        <div className="py-2 px-4 bg-blue-200">Channel</div>
        <div className="py-2 my-2">

            {props.channelList.map((value, index) => {
                return (
                    <label key={index} className="p-2 mr-2">
                        <input id={`chat-channel${index}`} name={`chat-channel${index}`} type="checkbox" value={index} onChange={(e) => props.handleChannelChange(e.target.value, e.target.checked)} checked={props.channelCheked[index]} />
                        {value['name']}
                    </label>
                );
            })}
        </div>
    </>
}

export default ChatTo