import React, { useState } from 'react';
import CreateRechmenu from './CreateRechmenu';
import RichmenuList from './RichmenuList';
import RichmenuTemplates from './RichmenuTemplates';
import RichMenuUpload from './RichMenuUpload';
import RichMenuPreview from './RichMenuPreview';
import Channel from './Channel';
import Result from './Result';
import { data } from 'autoprefixer';

const __ = wp.i18n.__;
const RichmenuForm = () => {
    const [richmenu, setRichmenu] = useState(null);
    const [richmenuList, setRichmenuList] = useState(lc_initdata['richmenus']);
    const [channel, setChannel] = useState(lc_initdata['channel_prefix']);
    const [result, setResult] = useState(new Array());
    const [mode, setMode] = useState('list');
    const [selectedFile, setSelectedFile] = useState(null);
    const [previewUrl, setPreviewUrl] = useState(null);
    const [imageError, setImageError] = useState(null);

    const templateList = lc_initdata['templates'];
    const channelList = lc_initdata['channels'];//['1番目のチャネル', '2番目のチャネル', '3番目のチャネル']

    // 新規アップロード時のハンドラー
    const handleFileSelect = (file, error) => {
        setImageError(error);
        if (file) {
            setSelectedFile(file);
            setPreviewUrl(URL.createObjectURL(file));
        }
    };

    // リッチメニュー選択時のハンドラー
    const handleRichmenuSelect = (richmenu) => {
        if( richmenu === null ) {
            setMode('layout');
        } else {
            const newRichmenu = {
                ...richmenu,
                areas: richmenu.areas.map((area) => {
                    if( area.hasOwnProperty('action') ) {
                        const action = area.action;
                        const actionType = action.type;
                        const newAction = {};
                        newAction[actionType] = action;
                        delete newAction[actionType].type;
                        return {
                            ...area,
                            action: newAction,
                        };
                    }else{
                        return area;
                    }
                }),
            };
            console.log(newRichmenu);
            setRichmenu(newRichmenu);
            setMode('create');
        }
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

    const createRichmenu = () => {
        console.log(richmenu);
        // ajaxでリッチメニューを作成し、setRichmenuListでリッチメニューを更新
        const formData = new FormData();
        formData.append('action', 'lc_ajax_create_richmenu');
        formData.append('nonce', lc_initdata['ajax_nonce']);
        formData.append('channel', channel);
        formData.append('richmenu', JSON.stringify(richmenu));
        formData.append('file', selectedFile);
        // ajax送信
        jQuery.ajax({
            url: lc_initdata['ajaxurl'],
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
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


    return <div className="RichmenuForm">
        <header className="RichmenuHeader text-lg p-2 my-2">
            {__('LINE Richmenu', 'lineconnect')}
        </header>

        <div className="w-4/5">
            <div className="mb-4">
                <Channel handleChannelChange={handleChannelChange} channelCheked={channel} channelList={channelList} />
            </div>
            { mode === 'list' &&
                <div className="mb-4">
                    <RichmenuList onSelected={handleRichmenuSelect} onDelete={handleListDelete} richmenuList={richmenuList} />
                </div>
            }
            { mode === 'layout' &&
                <div className="mb-4">
                    <RichmenuTemplates onSelected={handleRichmenuSelect} templateList={templateList} />
                </div>
            }
            {  mode === 'create' && 
                <>
                    <div className="py-2 px-4 bg-blue-200">{__('Create Richmenu', 'lineconnect')}</div>
                    <div className="py-2 px-4 bg-white space-y-2">
                        <RichMenuUpload onFileSelect={handleFileSelect} error={imageError}/>
                        <RichMenuPreview richmenu={richmenu} imageUrl={previewUrl} />
                    </div>
                    <div className="mb-4">
                        <CreateRechmenu richmenu={richmenu} onFormChange={setRichmenu} onFormSubmit={createRichmenu} />
                    </div>
                </>
            }
        </div>

        <Result result={result} />
    </div>;
};

export default RichmenuForm;