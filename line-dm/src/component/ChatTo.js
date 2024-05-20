import React, { useState } from 'react';
const __ = wp.i18n.__;

const ChatTo = (props) => {
    return <>
        <div className="py-2 px-4 bg-blue-200"><label htmlFor="chat-to">{__('To', 'lineconnect')}</label></div>
        {'profile' in props.toValue ? (
        <ul>
            <li className="p-2 my-1 mr-2 border border-gray-100 rounded-lg bg-gray-200 inline-block">
                {'displayName' in props.toValue['profile'] ? props.toValue['profile']['displayName']:props.toValue['line_id']}
            </li>
        </ul>
        ):
        (
            <div className='py-4 px-4 my-1 bg-green-100 border border-green-500'>
                {__('To specify individual users, use the check link on the User List page, or check the check boxes for the target users and "Apply" the "Send LINE Messages" option in the batch operation.', 'lineconnect')}
            </div>
        )}
        {/*<ChatToComponent typeValue={props.typeValue} toValue={props.toValue} toUsers={props.toUsers}<input id="chat-to" name="to" value={props.defaultValue} onChange={(e) => props.handleToChange(e.target.value)} className="p-2 my-2 border border-indigo-600 w-full" />*/}
    </>
}
    /*
const ChatToComponent = (props) => {
    return (<ChatToMulti toUsers={props.toUsers} />)

    if (props.typeValue == "multi") {
        return (<ChatToMulti toUsers={props.toUsers} />)
    } else if (props.typeValue == "broad" || props.typeValue == "linked") {
        return (<></>)
    } else {
        return (<ChatToRole handleRoleChange={(val, checked) => props.handleRoleChange(val, checked)} roleCheked={props.roleCheked} roleList={props.roleList} />)
    }
}


const ChatToMulti = (props) => {
    // const [to, setTo] = useState('');

    return <>
        <div className="py-2 px-4 bg-blue-200"><label htmlFor="chat-to">{__('To', 'lineconnect')}</label></div>
        {props.toUsers.length > 0 ? (
            <ul>
                {props.toUsers.map((value, index) => {
                    return (
                        <li key={index} className="p-2 my-1 mr-2 border border-gray-100 rounded-lg bg-gray-200 inline-block">
                            {value['user_url'] ? (<a href={value['user_url']}> {value['name']}</a>) : (value['name'])}
                        </li>
                    );
                })}
            </ul>
        )
            :
            (
                <div className='py-4 px-4 my-1 bg-green-100 border border-green-500'>
                    {__('To specify individual users, use the check link on the User List page, or check the check boxes for the target users and "Apply" the "Send LINE Messages" option in the batch operation.', 'lineconnect')}
                </div>
            )}
    </>
}

*/

export default ChatTo