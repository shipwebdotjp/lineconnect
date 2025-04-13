/*
# リッチメニューエイリアスの作成・更新・削除を行うためのコンポーネント
## リッチメニューエイリアスの作成
* エイリアスIDを入力するテキストフィールド
* リッチメニューを選択するセレクトボックス
* 新規作成ボタン

## 既存のリッチメニューエイリアスの一覧
* リッチメニューエイリアスIDの表示
* セレクトボックス(セットされたリッチメニューが選択された状態)

### 更新
* 更新ボタン押下で、セレクトボックスで選択されたリッチメニューに紐づけ先を変更
### 削除
* 削除ボタン押下で、リッチメニューエイリアスを削除
*/

import React, { useState } from 'react';
import Result from './Result';
import DeleteIcon from '@mui/icons-material/Delete';

const __ = wp.i18n.__;

const RichmenuAlias = ({ channel, richmenuList, aliasList, setAliasList }) => {
    const [result, setResult] = useState([]);
    const [aliasId, setAliasId] = useState('');
    const [selectedRichmenu, setSelectedRichmenu] = useState('');
    const [isCreating, setIsCreating] = useState(false);
    // const [selectedRichmenuIds, setSelectedRichmenuIds] = useState({});
    const [updatingStates, setUpdatingStates] = useState({});
    const [deletingStates, setDeletingStates] = useState({});

    const handleUpdateAlias = async (aliasId) => {
        const newRichmenuId = aliasList[aliasId];

        if (!newRichmenuId) {
            setResult({'result': 'error', 'error': [ __('Please select a richmenu', 'lineconnect')], 'success': []});
            return;
        }

        // setIsUpdating(true);
        setUpdatingStates(prev => ({...prev, [aliasId]: true}));
        setResult([]);

        jQuery.ajax({
            url: lc_initdata['ajaxurl'],
            type: 'POST',
            data: {
                action: 'lc_ajax_update_richmenu_alias',
                nonce: lc_initdata['ajax_nonce'],
                channel: channel,
                richMenuAliasId: aliasId,
                richMenuId: newRichmenuId,
            },
            dataType: 'json',
        }).done(function (data) {
            if (data.result === 'success') {
                setAliasList(data.aliases);
            }
            setResult(data);
        }).fail(function (XMLHttpRequest, textStatus, error) {
            console.log('Error: ' + error);
            console.log(XMLHttpRequest.responseText);
        }).always(function () {
            // setIsUpdating(false);
            setUpdatingStates(prev => ({...prev, [aliasId]: false}));
        });
        
    };

    const handleDeleteAlias = async (aliasId) => {
        if (!window.confirm(__('Are you sure you want to delete this alias?', 'lineconnect'))) {
            return;
        }

        setDeletingStates(prev => ({...prev, [aliasId]: true}));
        setResult([]);

        jQuery.ajax({
            url: lc_initdata['ajaxurl'],
            type: 'POST',
            data: {
                action: 'lc_ajax_delete_richmenu_alias',
                nonce: lc_initdata['ajax_nonce'],
                channel: channel,
                richMenuAliasId: aliasId,
            },
            dataType: 'json',
        }).done(function (data) {
            if (data.result === 'success') {
                setAliasList(data.aliases);
            }
            setResult(data);
        }).fail(function (XMLHttpRequest, textStatus, error) {
            console.log('Error: ' + error);
            console.log(XMLHttpRequest.responseText);
        }).always(function () {
            setDeletingStates(prev => ({...prev, [aliasId]: false}));
        });
    };

    const handleCreateAlias = async () => {
        if (!aliasId || !selectedRichmenu) {
            setResult({'result': 'error', 'error': [ __('Please fill all fields', 'lineconnect')], 'success': []});
            return;
        }

        if (!/^[a-zA-Z0-9_-]{1,32}$/.test(aliasId)) {
            setResult({'result': 'error', 'error': [ __('Invalid Alias ID format', 'lineconnect')], 'success': []});
            return;
        }

        setIsCreating(true);
        setResult([]);

        jQuery.ajax({
            url: lc_initdata['ajaxurl'],
            type: 'POST',
            data: {
                action: 'lc_ajax_create_richmenu_alias',
                nonce: lc_initdata['ajax_nonce'],
                channel: channel,
                richMenuAliasId: aliasId,
                richMenuId: selectedRichmenu,
            },
            dataType: 'json',
        }).done(function (data) {
            if (data.result === 'success') {
                setAliasList(data.aliases);
                setAliasId('');
                setSelectedRichmenu('');
            }
            setResult(data);
        }).fail(function (XMLHttpRequest, textStatus, error) {
            console.log('Error: ' + error);
            console.log(XMLHttpRequest.responseText);
            setResult({'result': 'error', 'error': [ __('Failed to create alias', 'lineconnect')], 'success': []});
        }
        ).always(function () {
            setIsCreating(false);
        });
    }

    return (
        <div className="RichmenuAlias">
            <header className="RichmenuHeader text-lg p-2 my-2">
                {__('LINE Richmenu Alias', 'lineconnect')}
            </header>

            <Result result={result} />

            {/* エイリアス作成フォーム */}
            <div className="p-4 border rounded-lg mb-4">
                <h3 className="text-lg font-bold mb-2">{__('Create New Alias', 'lineconnect')}</h3>
                
                <div className="mb-4">
                    <label className="block text-sm font-medium mb-1">
                        {__('Alias ID', 'lineconnect')}
                    </label>
                    <input
                        type="text"
                        className="w-64 p-2 border rounded"
                        placeholder="alias_id"
                        maxLength={32}
                        value={aliasId}
                        onChange={(e) => setAliasId(e.target.value)}
                    />
                    <div className="text-sm text-gray-500 ml-2">{__('(1-32 characters, a-z, A-Z, 0-9, _ and -)', 'lineconnect')}</div>
                </div>

                <div className="mb-4">
                    <label className="block text-sm font-medium mb-1">
                        {__('Richmenu', 'lineconnect')}
                    </label>
                    <select
                        className="w-full p-2 border rounded"
                        value={selectedRichmenu}
                        onChange={(e) => setSelectedRichmenu(e.target.value)}
                    >
                        <option value="">{__('Select Richmenu', 'lineconnect')}</option>
                        {Object.entries(richmenuList).map(([id, richmenu]) => (
                            <option key={id} value={id}>
                                {richmenu.name}
                            </option>
                        ))}
                    </select>
                    <div className="text-sm text-gray-500 ml-2">{__('(Select a richmenu to link)', 'lineconnect')}</div>
                </div>

                <button
                    className="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 disabled:bg-blue-300"
                    onClick={handleCreateAlias}
                    disabled={isCreating}
                >
                    {isCreating ? (
                        <span className="flex items-center">
                            <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {__('Creating...', 'lineconnect')}
                        </span>
                    ) : (
                        __('Create', 'lineconnect')
                    )}
                </button>
            </div>

            {/* 既存エイリアス一覧 */}
            <div className="p-4 border rounded-lg">
                <h3 className="text-lg font-bold mb-2">{__('Aliases list', 'lineconnect')}</h3>
                
                {Object.entries(aliasList).length === 0 ? (
                    <p className="text-gray-500">{__('No aliases found', 'lineconnect')}</p>
                ) : (
                    <div className="space-y-4">
                        {Object.entries(aliasList).map(([aliasId, richmenuId]) => (
                            <div key={aliasId} className="p-4 border rounded-lg">
                                <div className="flex items-center justify-between mb-4">
                                    <span className="text-lg font-bold">{aliasId}</span>
                                    <button
                                        className=" flex items-center text-red-500 hover:text-red-700 disabled:text-red-300"
                                        onClick={() => handleDeleteAlias(aliasId)}
                                        disabled={deletingStates[aliasId]}
                                    >
                                        {deletingStates[aliasId] ? (
                                            <span className="flex items-center">
                                                <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                <DeleteIcon />{__('Deleting...', 'lineconnect')}
                                            </span>
                                        ) : (
                                            <>
                                                <DeleteIcon />{__('Delete', 'lineconnect')}
                                            </>
                                        )}
                                    </button>
                                </div>
                                
                                <div className="mb-4">
                                    <label className="block text-sm font-medium mb-1">
                                        {__('Linked Richmenu', 'lineconnect')}
                                    </label>
                                    <select
                                        className="w-full p-4 border rounded disabled:opacity-50"
                                        value={richmenuId}
                                        onChange={(e) => setAliasList({...aliasList, [aliasId]: e.target.value})}
                                        disabled={updatingStates[aliasId]}
                                    >
                                        <option value="">{__('Select Richmenu', 'lineconnect')}</option>
                                        {Object.entries(richmenuList).map(([id, richmenu]) => (
                                            <option key={id} value={id}>
                                                {richmenu.name}
                                            </option>
                                        ))}
                                    </select>
                                    <button
                                        className="border border-blue-500 text-blue-500 px-4 py-2 m-2 rounded hover:bg-blue-500 hover:text-white disabled:bg-blue-300"
                                        onClick={() => handleUpdateAlias(aliasId)}
                                        disabled={updatingStates[aliasId]}
                                    >
                                        {updatingStates[aliasId] ? (
                                            <span className="flex items-center">
                                                <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                {__('Updating...', 'lineconnect')}
                                            </span>
                                        ) : (
                                            __('Update', 'lineconnect')
                                        )}
                                    </button>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </div>
    );
}

export default RichmenuAlias;
