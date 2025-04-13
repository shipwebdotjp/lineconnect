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
    const [areaKey, setAreaKey] = useState(null);
    const [isCreating, setIsCreating] = useState(false);

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
        setResult([]);
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

        setIsCreating(true);
        setResult([]);

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
        }).always(function () {
            setIsCreating(false);
        });
    };

    const handleAreaChange = (updatedRichmenu) => {
        setAreaKey(JSON.stringify(updatedRichmenu.areas));
        setRichmenu(updatedRichmenu);
    };


    const NavigationTabs = ({ currentMode, onModeChange }) => {
        const baseButtonClasses = "py-4 px-6 block hover:text-blue-500 focus:outline-none";
        const activeButtonClasses = "text-blue-500 border-b-2 font-medium border-blue-500";
        const inactiveButtonClasses = "text-gray-600";

        return (
            <div className="border-b border-gray-200">
                <nav className="-mb-px flex">
                    <button
                        className={`${baseButtonClasses} 
                            ${currentMode === 'list' ? activeButtonClasses : inactiveButtonClasses}
                        `}
                        onClick={() => onModeChange('list')}
                    >
                        {__('Richmenu list', 'lineconnect')}
                    </button>
                    <button
                        className={`${baseButtonClasses} 
                            ${currentMode === 'layout' ? activeButtonClasses : inactiveButtonClasses}
                        `}
                        onClick={() => onModeChange('layout')}
                    >
                        {__('Create new richmenu from blank template', 'lineconnect')}
                    </button>
                </nav>
            </div>
        );
    };

    return (
        <div className="RichmenuForm">
            <NavigationTabs currentMode={mode} onModeChange={setMode} />
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
                            onAreaChange={handleAreaChange} 
                        />
                    </div>
                    <div className="mb-4">
                        <CreateRechmenu 
                            richmenu={richmenu} 
                            areaKey={areaKey}
                            isCreating={isCreating}
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