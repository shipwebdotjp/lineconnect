import React, { useState } from 'react';
import ChatTo from './ChatTo';
import ChatMessage from './ChatMessage';
import ChatChannel from './ChatChannel';
import ChatResult from './ChatResult';

const __ = wp.i18n.__;
const ChatForm = () => {
    const [message, setMessage] = useState('');
    const [to, setTo] = useState(lc_initdata['user_ids']);
    const [type, setType] = useState(lc_initdata['toType']);
    const [channel, setChannel] = useState(lc_initdata['channelChecked']);
    const [role, setRole] = useState(new Array());
    const [result, setResult] = useState(new Array());

    const channelList = lc_initdata['channels'];//['1番目のチャネル', '2番目のチャネル', '3番目のチャネル']
    const toTypeList = lc_initdata['toTypeList'];   //宛先タイプ一覧
    const roleList = lc_initdata['roleList'];   //ロール一覧
    const toUsers = lc_initdata['toUsers']; //宛先ユーザー一覧

    function handleTypeChange(text) {
        setType(text);
    }

    function handleToChange(text) {
        setTo(text);
    }

    function handleMessageChange(text) {
        setMessage(text);
    }

    function handleChannelChange(val, checked) {
        const newChannel = channel.slice();
        newChannel.splice(val, 1, !newChannel[val]);
        setChannel(newChannel);
        //setMessage(channel);
        // setChannel({ ...channel, [val]: checked });
        //setChannel({ ...channel, [val]: checked });
    }

    function handleRoleChange(val, checked) {
        if (role.includes(val)) {
            setRole([...role.filter((item) => item !== val)]);
        } else {
            setRole([...role.concat([val])]);
        }
    }
    /*
    function handleChannelChange(val, checked) {
        const newChannel = channel;
        if (checked) {
            const index = newChannel.indexOf(val);
            if (index == -1) {
                newChannel.push(val);
            }
        } else {
            const index = newChannel.indexOf(val);
            if (index > -1) {
                newChannel.splice(index, 1);
            }
        }
        setChannel(newChannel);
    }
    */


    const handleSubmit = (e) => {
        e.preventDefault();
        //console.log(channel);
        // alert('Type: ' + type + ' To: ' + to + " Message: " + message);
        jQuery.ajax({
            type: "POST",
            url: lc_initdata['ajaxurl'], // admin-ajax.php のURLが格納された変数
            data: {
                'action': 'lc_ajax_chat_send',
                'nonce': lc_initdata['ajax_nonce'],
                'type': type,
                'to': to,
                'role': role,
                'channel': channel,
                'message': message,
            },
            dataType: 'json'
        }).done(function (data) {
            // console.log("done...");
            // console.log(data);
            setResult(data);
        }).fail(function (XMLHttpRequest, textStatus, error) {
            // console.log('失敗' + error);
            // console.log(XMLHttpRequest.responseText);
            setResult({ "result": "failed", "error": [error, XMLHttpRequest.responseText] });
        });
    };

    return <div className="ChatForm">
        <header className="ChatHeader text-lg p-2 my-2">
            {__('Send LINE message', 'lineconnect')}			
        </header>
        <form onSubmit={handleSubmit}>
            <div className="ChatBody w-1/3">
                <div className="ChatRow">
                    <ChatChannel handleChannelChange={(val, checked) => handleChannelChange(val, checked)} channelCheked={channel} channelList={channelList} />
                </div>
                <div className="ChatRow">
                    <ChatTo handleToChange={setTo} handleTypeChange={setType} toValue={to} typeValue={type} toUsers={toUsers} toType={toTypeList}
                        handleRoleChange={(val, checked) => handleRoleChange(val, checked)} roleCheked={role} roleList={roleList} />
                </div>
                <div className="ChatRow">
                    <ChatMessage handleMessageChange={setMessage} defaultValue={message} />
                </div>
                <div className="ChatRow">
                    <button type="submit" className="btn-indigo">{__('Send', 'lineconnect')}</button>
                </div>
            </div>
        </form>
        <ChatResult result={result} />
    </div>
}

export default ChatForm

// const e = React.createElement;
/*
export default class ChatForm extends React.Component {
    constructor(props) {
        super(props);
        this.state = { to: '', message: '' };

        this.handleChange = this.handleChange.bind(this);
        this.handleSubmit = this.handleSubmit.bind(this);
    }

    handleChange(event) {
        const target = event.target;
        const value = target.type === 'checkbox' ? target.checked : target.value;
        const name = target.name;
        this.setState({
            [name]: value
        });
    }
    handleSubmit(event) {
        alert('A name was submitted: ' + this.state.to + " message:" + this.state.message);
        event.preventDefault();
    }

    render() {
        return (
            <div className="ChatForm">
                <form onSubmit={this.handleSubmit}>
                    <header className="ChatHeader">
                        LINE送信
                    </header>
                    <div className="ChatBody">
                        <div className="ChatRow">
                            <FormControl variant="standard">
                                <label htmlFor="outlined-to">To</label>
                                <Input id="outlined-to" margin="dense" name="to" value={this.state.to} onChange={this.handleChange} />
                            </FormControl>
                        </div>
                        <div className="ChatRow">
                            <FormControl variant="standard">
                                <label htmlFor="outlined-message">Message</label>
                                <Input id="outlined-message" margin="dense" name="message" multiline minRows={5} value={this.state.message} onChange={this.handleChange} />
                            </FormControl>
                        </div>
                        <Button variant="contained" type="submit">送信</Button>
                    </div>
                </form>
            </div>
        );
    }
}

*/