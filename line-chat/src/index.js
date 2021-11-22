import * as React from 'react';
import ReactDOM from 'react-dom';
import ChatForm from './component/ChatForm'; //拡張子は省略可能

ReactDOM.render(
    <ChatForm />,
    document.getElementById('line_chat_root')
);