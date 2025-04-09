import React, { useState, useRef, useEffect } from "react";
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
    const formRefs = useRef([]);

    useEffect(() => {
        // 外部からバリデーションできるようにグローバルに保存
        window.__rjsfFormRefs = formRefs.current;
    }, []);

    const onFormChange = (formData, id) => {
        // console.log(formData);
        // console.log(id);
        if (id == undefined) {
            return;
        }
        //int : formIdx string to int
        const formIdx = parseInt(id.split('_')[1]);
        //string : field
        const field = id.split('_')[2];
        let originalValue = formValueElement.value;
        if (originalValue == '') {
            originalValue = '{}';
        }
        const originalFormData = JSON.parse(originalValue);
        originalFormData[formIdx] = formData.formData;
        formValueElement.value = JSON.stringify(originalFormData);
        if (lc_initdata['formName'] == 'slc_message-data' ||
            lc_initdata['formName'] == 'slc_trigger-data') {
            if (field == 'type') {
                if (formData.formData.type && lc_initdata['subSchema'][formData.formData.type]) {
                    const newform = [...form];
                    newform[formIdx + 1]["schema"] = subSchema[formData.formData.type];
                    setForm(newform);
                }
            }
        }
    }

    const changeKeyLabel = (stringToTranslate, params) => {
        //check if the stringToTranslate in keys of translateString
        if (translateString[stringToTranslate]) {
            return replaceStringParameters(translateString[stringToTranslate], params);
        } else {
            return englishStringTranslator(stringToTranslate, params);
        }
    }

    const AddButton = (props) => {
        const { icon, iconType, uiSchema, registry, ...btnProps } = props;
        // console.log(btnProps);
        const uiOptions = uiSchema['ui:options'] || {};
        return (
            <Button variant='outlined' color='primary' {...btnProps}>
                {uiOptions['addText'] || 'Add'}
            </Button>
        );
    }

    const checkIfFormDataIsValid = () => {
        // console.log(formRef.current);
        if (formRef.current) {
            return formRef.current.validateForm();
        }
        return false;
    }

    return (
        <>
            {form.map((formItem, id) => {
                const ref = React.createRef();
                formRefs.current[id] = ref;

                return (
                    <Form
                        key={id}
                        schema={formItem.schema}
                        uiSchema={formItem.uiSchema}
                        formData={formItem.formData}
                        validator={validator}
                        translateString={changeKeyLabel}
                        onChange={onFormChange}
                        id={`rjsf_${id}`}
                        idPrefix={`root_${id}`}
                        liveOmit={formItem.props.liveOmit ?? false}
                        omitExtraData={formItem.props.omitExtraData ?? false}
                        liveValidate={formItem.props.liveValidate ?? false}
                        showErrorList={formItem.props.showErrorList ?? 'bottom'}
                        templates={{ ButtonTemplates: { AddButton } }}
                        ref={ref}
                    />
                );
            })}
        </>
    );
}
export default RJSFForm;