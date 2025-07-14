import React from 'react';
import { render } from 'flex-render';
import 'flex-render/css';
const __ = wp.i18n.__;

const MessageBubbleFlex = ({ flexJSON }) => {
    return (
        <div dangerouslySetInnerHTML={{ __html: render(flexJSON) }} />
    );
};

export default MessageBubbleFlex;
