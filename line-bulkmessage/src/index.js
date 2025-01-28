import * as React from 'react';
import ReactDOM from 'react-dom';
import BulkMessageForm from './component/BulkMessageForm'; //拡張子は省略可能
import "./index.css";

ReactDOM.render(
    <BulkMessageForm />,
    document.getElementById('line_chat_root')
);