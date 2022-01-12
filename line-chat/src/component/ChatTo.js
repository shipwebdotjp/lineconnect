
import React, { useState } from 'react';

const ChatTo = (props) => {
    return <>
        <div className="py-2 px-4 bg-blue-200">Type</div>
        <div className="py-2 my-2">
            {props.toType.map((value, index) => {
                return (
                    <label key={index} className="p-2 mr-2">
                        <input id={`chat-type${index}`}
                            name={`chat-type${index}`}
                            type="radio"
                            value={value['name']}
                            onChange={(e) => props.handleTypeChange(e.target.value)}
                            checked={props.typeValue == value['name']} />
                        {value['label']}
                    </label>
                );
            })}
        </div>
        <ChatToComponent typeValue={props.typeValue} toValue={props.toValue} toUsers={props.toUsers}
            handleRoleChange={(val, checked) => props.handleRoleChange(val, checked)} roleCheked={props.roleCheked} roleList={props.roleList} />
        {/*<input id="chat-to" name="to" value={props.defaultValue} onChange={(e) => props.handleToChange(e.target.value)} className="p-2 my-2 border border-indigo-600 w-full" />*/}
    </>
}

const ChatToComponent = (props) => {
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
        <div className="py-2 px-4 bg-blue-200"><label htmlFor="chat-to">To</label></div>
        {props.toUsers.length > 0 ? (
            <ul>
                {props.toUsers.map((value, index) => {
                    return (
                        <li key={index} className="p-2 my-1 mr-2 border border-gray-100 rounded-lg bg-gray-200 inline-block">
                            {value['user_url'] ? (<a href={value['user_url']}> {value['user_login']}</a>) : (value['user_login'])}
                        </li>
                    );
                })}
            </ul>
        )
            :
            (
                <div className='py-4 px-4 my-1 bg-green-100 border border-green-500'>
                    ユーザーを個別に指定するには、ユーザー一覧ページでLINE連携の「連携済」リンクを利用するか、対象ユーザーにチェックを入れて一括操作で「LINEメッセージ送信」を「適用」してください。
                </div>
            )}
    </>
}

const ChatToRole = (props) => {
    return <>
        <div className="py-2 px-4 bg-blue-200"><label htmlFor="Role-to">Role</label></div>
        <div className="py-2 my-2">
            {props.roleList.map((value, index) => {
                return (
                    <label key={index} className="p-2 mr-2">
                        <input id={`chat-role${index}`} name={`chat-role${index}`} type="checkbox" value={value['name']} onChange={(e) => props.handleRoleChange(e.target.value, e.target.checked)} checked={props.roleCheked.includes(value['name'])} />
                        {value['label']}
                    </label>
                );
            })}
        </div>
    </>
}

export default ChatTo