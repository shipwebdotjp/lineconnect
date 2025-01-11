import React, { useState } from 'react';
import CreateRechmenu from './CreateRechmenu';
import RichmenuList from './RichmenuList';
import RichmenuTemplates from './RichmenuTemplates';
import RichMenuUpload from './RichMenuUpload';
import RichMenuPreview from './RichMenuPreview';
import Result from './Result';

const __ = wp.i18n.__;

const RichmenuForm = ({ channel, richmenuList, setRichmenuList }) => {
    const [richmenu, setRichmenu] = useState(null);
    const [result, setResult] = useState([]);
    const [mode, setMode] = useState('list');
    const [selectedFile, setSelectedFile] = useState(null);
    const [previewUrl, setPreviewUrl] = useState(null);
    const [imageError, setImageError] = useState(null);
    const [areaFocusedIndex, setAreaFocusedIndex] = useState(null);

    const templateList = lc_initdata['templates'];

    const handleFileSelect = (file, error) => {
        setImageError(error);
        if (file) {
            setSelectedFile(file);
            setPreviewUrl(URL.createObjectURL(file));
        }
    };

    const handleRichmenuSelect = (richmenu) => {
        if (richmenu === null) {
            setMode('layout');
        } else {
            const newRichmenu = {
                ...richmenu,
                areas: richmenu.areas.map((area) => {
                    if (area.hasOwnProperty('action')) {
                        const action = area.action;
                        const actionType = action.type;
                        const newAction = {};
                        newAction[actionType] = action;
                        delete newAction[actionType].type;
                        return {
                            ...area,
                            action: newAction,
                        };
                    }
                    return area;
                }),
            };
            setRichmenu(newRichmenu);
            setMode('create');
        }
    };

    const handleListDelete = (richmenu_id) => {
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
            if (data.result === 'success') {
                setRichmenuList(data.richmenus);
            }
            setResult(data);
        }).fail(function (XMLHttpRequest, textStatus, error) {
            console.log('Error: ' + error);
            console.log(XMLHttpRequest.responseText);
        });
    };

    const createRichmenu = () => {
        const formData = new FormData();
        formData.append('action', 'lc_ajax_create_richmenu');
        formData.append('nonce', lc_initdata['ajax_nonce']);
        formData.append('channel', channel);
        formData.append('richmenu', JSON.stringify(richmenu));
        formData.append('file', selectedFile);

        jQuery.ajax({
            url: lc_initdata['ajaxurl'],
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
        }).done(function (data) {
            if (data.result === 'success') {
                setRichmenuList(data.richmenus);
            }
            setResult(data);
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
                        currentMode === 'layout' ? activeButtonClasses : inactiveButtonClasses
                    }`}
                    onClick={() => onModeChange('layout')}
                >
                    {wp.i18n.__('Create new richmenu from blank template', 'lineconnect')}
                </button>
                <button
                    type="button"
                    className={`${baseButtonClasses} ${
                        currentMode === 'list' ? activeButtonClasses : inactiveButtonClasses
                    }`}
                    onClick={() => onModeChange('list')}
                >
                    {wp.i18n.__('Richmenu list', 'lineconnect')}
                </button>
            </div>
        );
    };

    return (
        <div className="RichmenuForm">
            <ModeButtons currentMode={mode} onModeChange={setMode} />
            {mode === 'list' && (
                <div className="mb-4">
                    <RichmenuList 
                        onSelected={handleRichmenuSelect} 
                        onDelete={handleListDelete} 
                        richmenuList={richmenuList} 
                    />
                </div>
            )}
            {mode === 'layout' && (
                <div className="mb-4">
                    <RichmenuTemplates 
                        onSelected={handleRichmenuSelect} 
                        templateList={templateList} 
                    />
                </div>
            )}
            {mode === 'create' && (
                <>
                    <div className="py-2 px-4 bg-blue-200">
                        {__('Create Richmenu', 'lineconnect')}
                    </div>
                    <div className="py-2 px-4 bg-white space-y-2">
                        <RichMenuUpload 
                            onFileSelect={handleFileSelect} 
                            error={imageError}
                        />
                        <RichMenuPreview 
                            richmenu={richmenu} 
                            imageUrl={previewUrl} 
                            areaFocusedIndex={areaFocusedIndex} 
                            onAreaChange={setRichmenu} 
                        />
                    </div>
                    <div className="mb-4">
                        <CreateRechmenu 
                            richmenu={richmenu} 
                            onFormChange={setRichmenu} 
                            onFormSubmit={createRichmenu} 
                            onAreaFocus={setAreaFocusedIndex} 
                        />
                    </div>
                </>
            )}
            <Result result={result} />
        </div>
    );
};

export default RichmenuForm;