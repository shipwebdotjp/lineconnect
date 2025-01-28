import React, { useState } from "react";
// import { createRoot } from 'react-dom/client';
import validator from '@rjsf/validator-ajv8';
// import Form from '@rjsf/core';
// import Form from '@rjsf/bootstrap-4';
import Form from '@rjsf/material-ui';
// import Form from '@rjsf/chakra-ui'
import { TranslatableString, englishStringTranslator, replaceStringParameters } from '@rjsf/utils';
import Button from '@material-ui/core/Button';


const RJSFForm = () => {
    const [mainSchema, setMainSchema] = useState(lc_initdata['mainSchema']);
    const subSchema = lc_initdata['subSchema'];
    const mainUiSchema = lc_initdata['mainUiSchema'];
    const subUiSchema = lc_initdata['subUiSchema'];
    const formData = lc_initdata['formData'];

    const [form, setForm] = useState(lc_initdata['form']);
    const translateString = lc_initdata['translateString'];
    const formValueElement = document.getElementById(lc_initdata['formName']);
    // const liveOmit = lc_initdata['liveOmit'] ?? false;
    // const omitExtraData = lc_initdata['omitExtraData'] ?? false;
    // const liveValidate = lc_initdata['liveValidate'] ?? false;
    // const showErrorList = lc_initdata['showErrorList'] ?? 'bottom';

    const log = (type) => console.log.bind(console, type);


    const onFormChange = ( formData, id) => {
        // console.log(formData);
        // console.log(id);
        if(id == undefined){
            return;
        }
        //int : formIdx string to int
        const formIdx = parseInt(id.split('_')[1]);
        //string : field
        const field = id.split('_')[2];
        let originalValue = formValueElement.value;
        if(originalValue == ''){
            originalValue = '{}';
        }
        const originalFormData = JSON.parse(originalValue);
        originalFormData[formIdx] = formData.formData;
        formValueElement.value = JSON.stringify(originalFormData);
        if(lc_initdata['formName'] == 'slc_message-data' || 
        lc_initdata['formName'] == 'slc_trigger-data'){
            if(field == 'type'){
                if(formData.formData.type && lc_initdata['subSchema'][formData.formData.type]){
                    const newform = [...form];
                    newform[formIdx+1]["schema"] = subSchema[formData.formData.type];
                    setForm(newform);
                }
            }
        }
    }

    const recursiveAttachUiOption = (formObj, uiSchema, orginalUiSchema) => {
        //　再帰的なuiSchemaの適用
        // check if formObj is Object
        if(typeof formObj === 'object'){            
            Object.keys(formObj).map((key) => {
                if(uiSchema.hasOwnProperty(key) && uiSchema[key].hasOwnProperty('$ref')){
                    if(orginalUiSchema.hasOwnProperty(uiSchema[key]['$ref'])){
                        uiSchema[key] = orginalUiSchema[uiSchema[key]['$ref']]; //参照先のオブジェクトに置換
                    }
                }
                formObj = recursiveAttachUiOption(formObj[key], uiSchema[key], orginalUiSchema);

            });
        }else if(typeof formObj === 'array'){
            formObj.map((item) => {
                result = recursiveAttachUiOption(item, uiSchema, orginalUiSchema);
            });
        }
        else{
            return formObj;
        }

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

    return (
        <>
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
        </>
    );
}
export default RJSFForm;