import React, { useState } from 'react';
// import RechmenuAdd from './RechmenuAdd';
import RichmenuList from './RichmenuList';
// import RichmenuTemplates from './RichmenuTemplates';
import Channel from './Channel';
import Result from './Result';
import { data } from 'autoprefixer';

const __ = wp.i18n.__;
const RichmenuForm = () => {
    const [richmenu, setRichmenu] = useState(null);
    const [richmenuList, setRichmenuList] = useState(lc_initdata['richmenus']);
    const [channel, setChannel] = useState(lc_initdata['channel_prefix']);
    const [result, setResult] = useState(new Array());

    const templateList = lc_initdata['templates'];
    const channelList = lc_initdata['channels'];//['1番目のチャネル', '2番目のチャネル', '3番目のチャネル']

    const handleSelected = (richmenu) => {
        setRichmenu(richmenu);
    };

    const handleChannelChange = (channel) => {
        setChannel(channel);
        console.log(channel);
        // ajaxでチャネルのリッチメニューを取得し、setRichmenuListでリッチメニューを更新
        jQuery.ajax({
            url: lc_initdata['ajaxurl'],
            type: 'POST',
            data: {
                action: 'lc_ajax_get_richmenus',
                nonce: lc_initdata['ajax_nonce'],
                channel: channel,
            },
            dataType: 'json',
        }).done(function (data) {
            setRichmenuList(data);
            console.log(data);
        }).fail(function (XMLHttpRequest, textStatus, error) {
            console.log('失敗' + error);
            console.log(XMLHttpRequest.responseText);
        });
    };

    const handleListDelete = (richmenu_id) => {
        // ajaxでリッチメニューを削除し、setRichmenuListでリッチメニューを更新
        jQuery.ajax({
            url: lc_initdata['ajaxurl'],
            type: 'POST',
            data: {
                action: 'lc_ajax_delete_richmenu',
                nonce: lc_initdata['ajax_nonce'],
                channel: channel,
                richmenu_id: richmenu_id,
            },
            dataType: 'json',
        }).done(function (data) {
            // if result is success, then update richmenuList
            if (data.result === 'success') {
                setRichmenuList(data.richmenus);
            }
            setResult(data);
            
            console.log(data);
        }).fail(function (XMLHttpRequest, textStatus, error) {
            console.log('失敗' + error);
            console.log(XMLHttpRequest.responseText);
        });
    };

    const handleSubmit = (e) => {
    };

    return <div className="RichmenuForm">
        <header className="RichmenuHeader text-lg p-2 my-2">
            {__('LINE Richmenu', 'lineconnect')}
        </header>
        <form onSubmit={handleSubmit}>
            <div className="w-1/3">
                <div className="border border-gray-300 border-b-2">
                    <Channel handleChannelChange={handleChannelChange} channelCheked={channel} channelList={channelList} />
                </div>
                
                <div className="border border-gray-300 border-b-2">
                    <RichmenuList onSelected={handleSelected} onDelete={handleListDelete} richmenuList={richmenuList} />
                </div>
                {/*
                <div className="border border-gray-300 border-b-2">
                    <RichmenuTemplates onSelected={handleSelected} templateList={templateList} />
                </div>
                <div className="border border-gray-300 border-b-2">
                    <RechmenuAdd richmenu={richmenu} />
                </div>
                */}
            </div>
        </form>
        <Result result={result} />
    </div>;
};

export default RichmenuForm;