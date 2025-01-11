import React, { useState } from 'react';
import Channel from './Channel';
import RichmenuForm from './RichmenuForm';

const __ = wp.i18n.__;

const RichmenuIndex = () => {
    const [channel, setChannel] = useState(lc_initdata['channel_prefix']);
    const [richmenuList, setRichmenuList] = useState(lc_initdata['richmenus']);
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
                <div className="mb-4">
                    <RichmenuForm 
                        channel={channel}
                        richmenuList={richmenuList}
                        setRichmenuList={setRichmenuList}
                    />
                </div>
            </div>
        </div>
    );
};

export default RichmenuIndex;