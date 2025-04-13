const __ = wp.i18n.__;
const Channel = (props) => {
    return <>
        <div className="py-2 px-4 bg-blue-200">{__('Channel', 'lineconnect')}</div>
        <div className="py-2 my-2">
            {props.channelList.map((value, index) => {
                return (
                    <label key={index} className="p-2 mr-2">
                        <input id={`richmenu-channel${index}`}
                            name="richmenu-channel"
                            type="radio"
                            value={value['prefix']}
                            onChange={(e) => props.handleChannelChange(e.target.value)}
                            checked={props.channelCheked == value['prefix']} />
                        {value['name']}
                    </label>
                );
            })}
        </div>
    </>
}

export default Channel