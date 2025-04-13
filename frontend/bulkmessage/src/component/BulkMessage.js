import React, { useState } from "react";
// import { createRoot } from 'react-dom/client';
import validator from '@rjsf/validator-ajv8';
// import Form from '@rjsf/core';
// import Form from '@rjsf/bootstrap-4';
import Form from '@rjsf/material-ui';
// import Form from '@rjsf/chakra-ui'
import { TranslatableString, englishStringTranslator, replaceStringParameters } from '@rjsf/utils';
import Button from '@material-ui/core/Button';
const __ = wp.i18n.__;

const BulkMessage = (props) => {
    const subSchema = lc_initdata['messageSubSchema'];
    const slc_messages = lc_initdata['slc_messages'];

    const [form, setForm] = useState(lc_initdata['messageForm']);
    const translateString = lc_initdata['translateString'];
    const [data, setData] = useState([]);

    const log = (type) => console.log.bind(console, type);

    const onFormChange = ( _form, id) => {
        // console.log(_form);
        // console.log(id);
        if(id == undefined){
            return;
        }
        const formIdx = parseInt(id.split('_')[1]);
        const field = id.split('_')[2];
        
        // set formData to form[formIdx]
        const newData = [...data];
        newData[formIdx] = _form.formData;
        setData(newData);

        if(lc_initdata['formName'] == 'chatform-data'){
            if(field == 'type'){
                if(_form.formData.type && lc_initdata['messageSubSchema'][_form.formData.type]){
                    const newform = [...form];
                    newform[formIdx+1]["schema"] = subSchema[_form.formData.type];
                    setForm(newform);
                }
            }
        }
        // console.log(newData);
        props.handleFormChange(newData);
    }

    const changeKeyLabel = (stringToTranslate, params) => {
        //check if the stringToTranslate in keys of translateString
        if (translateString[stringToTranslate]) {
            return replaceStringParameters(translateString[stringToTranslate], params);
        }else{
            return englishStringTranslator(stringToTranslate, params);
        } 
    }

    const AddButton = (props) => {
        const { icon, iconType, uiSchema, registry, ...btnProps } = props;
        // console.log(btnProps);
        const uiOptions = uiSchema['ui:options'] || {};
        return (
            <Button variant='outlined' color='primary' {...btnProps}>
                {uiOptions['addText'] || 'Add' }
            </Button>
        );
    }

    const onMessageIdChange = (post_id) => {
        if(post_id == '0'){
            // clear current formData and schema
            const newform = [...form];
            form.map((value, index) => {
                // console.log(index);
                // console.log(newform[index]);
                newform[index]["formData"] = null;
                if(index % 2 == 1){
                    newform[index]["schema"] = {};
                }
            });
            setForm(form);
            props.handleFormChange([]);
            return;
        }
        jQuery.ajax({
            type: "POST",
            url: lc_initdata['ajaxurl'], // admin-ajax.php のURLが格納された変数
            data: {
                'action': 'lc_ajax_get_slc_message',
                'nonce': lc_initdata['ajax_nonce'],
                'post_id': post_id,
            },
            dataType: 'json'
        }).done(function (data) {
            // console.log("done...");
            // console.log(data);
            setData(data.formData);
            const newform = [...form];
            // clear current formData and schema
            form.map((value, index) => {
                // console.log(index);
                // console.log(newform[index]);
                newform[index]["formData"] = null;
                if(index % 2 == 1){
                    newform[index]["schema"] = {};
                }
            });

            data.formData.map((value, index) => {
                newform[index]["formData"] = value;
                if(index % 2 == 0 && value.type && lc_initdata['messageSubSchema'][value.type]){
                    newform[index+1]["schema"] = subSchema[value.type];
                }
            });
            setForm(newform);
            props.handleFormChange(data.formData);

        }).fail(function (XMLHttpRequest, textStatus, error) {
            // console.log('失敗' + error);
            // console.log(XMLHttpRequest.responseText);
            setResult({ "result": "failed", "error": [error, XMLHttpRequest.responseText] });
        });
    }

    return (
        <>
            <div className="py-2 px-4 bg-blue-200">{__('Message', 'lineconnect')}</div>
            <div className="py-2  px-4 my-2">
            {__('Template', 'lineconnect')}: 
                <select id="slc_message_id" name="slc_message_id" onChange={(e) => onMessageIdChange(e.target.value)}>
                    <option value="0">{__('New message' , 'lineconnect')}</option>
                    {slc_messages.map((value, index) => {
                        return (
                            <option key={index} value={value['post_id']}>{value['title']}</option>
                        );
                    })}
                </select>
            {form.map((form, id) => {
                return (
                    <Form 
                        key={id}
                        schema={form.schema}
                        uiSchema={form.uiSchema}
                        formData={form.formData}
                        validator={validator}
                        translateString={changeKeyLabel}
                        onChange={onFormChange}
                        id={`rjsf_${id}`}
                        idPrefix={`root_${id}`}
                        liveOmit={form.props.liveOmit ?? false}
                        omitExtraData={form.props.omitExtraData ?? false}
                        liveValidate={form.props.liveValidate ?? false}
                        showErrorList={form.props.showErrorList ?? 'bottom'}
                        templates={{ ButtonTemplates: { AddButton } }}
                    />
                );
            })}
            </div>
        </>
    );
}
export default BulkMessage;