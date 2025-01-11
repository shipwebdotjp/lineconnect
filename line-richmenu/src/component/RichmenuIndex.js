import React, { useState } from 'react';
import Channel from './Channel';
import RichmenuForm from './RichmenuForm';
import RichmenuAlias from './RichmenuAlias';

const __ = wp.i18n.__;

const RichmenuIndex = () => {
    const [channel, setChannel] = useState(lc_initdata['channel_prefix']);
    const [richmenuList, setRichmenuList] = useState(lc_initdata['richmenus']);
    const [aliasList, setAliasList] = useState(lc_initdata['aliases']);
    const [mode, setMode] = useState('richmenu');
    const channelList = lc_initdata['channels'];

    const handleChannelChange = (channel) => {
        setChannel(channel);
        // ajaxでチャネルのリッチメニューを取得
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
        }).fail(function (XMLHttpRequest, textStatus, error) {
            console.log('Error: ' + error);
            console.log(XMLHttpRequest.responseText);
        });
    };

    const ModeButtons = ({ currentMode, onModeChange }) => {
        const baseButtonClasses = "font-bold py-2 px-4 rounded transition-colors duration-200";
        const activeButtonClasses = "bg-white text-blue-700 border-2 border-blue-700 shadow-sm";
        const inactiveButtonClasses = "bg-blue-500 hover:bg-blue-700 text-white";
    
        return (
            <div className="mb-4 space-x-4">
                <button
                    type="button"
                    className={`${baseButtonClasses} ${
                        currentMode === 'richmenu' ? activeButtonClasses : inactiveButtonClasses
                    }`}
                    onClick={() => onModeChange('richmenu')}
                >
                    {wp.i18n.__('Richmenu', 'lineconnect')}
                </button>
                <button
                    type="button"
                    className={`${baseButtonClasses} ${
                        currentMode === 'alias' ? activeButtonClasses : inactiveButtonClasses
                    }`}
                    onClick={() => onModeChange('alias')}
                >
                    {wp.i18n.__('Richmenu alias', 'lineconnect')}
                </button>
            </div>
        );
    };

    return (
        <div className="RichmenuIndex">
            <header className="RichmenuHeader text-lg p-2 my-2">
                {__('LINE Richmenu', 'lineconnect')}
            </header>

            <div className="w-4/5">
                <div className="mb-4">
                    <Channel 
                        handleChannelChange={handleChannelChange} 
                        channelCheked={channel} 
                        channelList={channelList} 
                    />
                </div>
            <ModeButtons currentMode={mode} onModeChange={setMode} />
            {mode === 'richmenu' && (
                <div className="mb-4">
                    <RichmenuForm 
                        channel={channel}
                        richmenuList={richmenuList}
                        setRichmenuList={setRichmenuList}
                    />
                </div>
            )}
            {mode === 'alias' && (
                <div className="mb-4">
                    <RichmenuAlias 
                        channel={channel}
                        richmenuList={richmenuList}
                        aliasList={aliasList}
                        setAliasList={setAliasList}
                    />
                </div>
            )}
            </div>
        </div>
    );
};

export default RichmenuIndex;