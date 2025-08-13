import React, { useState, useRef, useEffect } from "react";
// import { createRoot } from 'react-dom/client';
import validator from '@rjsf/validator-ajv8';
// import Form from '@rjsf/core';
// import Form from '@rjsf/bootstrap-4';
import Form from '@rjsf/material-ui';
// import Form from '@rjsf/chakra-ui'
import { TranslatableString, englishStringTranslator, replaceStringParameters } from '@rjsf/utils';
import Button from '@material-ui/core/Button';
import Modal from '@material-ui/core/Modal';
import { makeStyles } from '@material-ui/core/styles';
const __ = wp.i18n.__;

const RJSFForm = () => {
    const [mainSchema, setMainSchema] = useState(lc_initdata['mainSchema']);
    const subSchema = lc_initdata['subSchema'];
    const mainUiSchema = lc_initdata['mainUiSchema'];
    const subUiSchema = lc_initdata['subUiSchema'];
    // const formData = lc_initdata['formData'];
    const textareaName = lc_initdata['formName'];
    const [textAreaValue, setTextAreaValue] = useState('');
    const [form, setForm] = useState(lc_initdata['form']);
    const translateString = lc_initdata['translateString'];
    const formValueElement = document.getElementById(lc_initdata['formName']);
    const [errorMessage, setErrorMessage] = useState('');

    const log = (type) => console.log.bind(console, type);
    const formRefs = useRef([]);

    useEffect(() => {
        // 外部からバリデーションできるようにグローバルに保存
        window.__rjsfFormRefs = formRefs.current;
        //フォームデータの初期値をテキストエリアに設定
        let originalValue = {};
        form.map((formItem, index) => {
            if (formItem.formData) {
                originalValue[index] = formItem.formData;
            }
        });
        setTextAreaValue(JSON.stringify(originalValue, null, 2));
        formValueElement.value = JSON.stringify(originalValue);
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
        let originalValue = textAreaValue;
        if (originalValue == '') {
            originalValue = '{}';
        }
        const originalFormData = JSON.parse(originalValue);
        originalFormData[formIdx] = formData.formData;
        setTextAreaValue(JSON.stringify(originalFormData, null, 2));
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

    const getModalStyle = () => {
        const top = 50;
        const left = 50;

        return {
            top: `${top}%`,
            left: `${left}%`,
            transform: `translate(-${top}%, -${left}%)`,
        };
    }
    const useStyles = makeStyles((theme) => ({
        paper: {
            position: 'absolute',
            width: '80%',
            backgroundColor: theme.palette.background.paper,
            border: '2px solid #000',
            boxShadow: theme.shadows[5],
            padding: theme.spacing(2, 4, 3),
        },
    }));
    const classes = useStyles();
    const [open, setOpen] = useState(false);
    const handleOpen = () => {
        setOpen(true);
        setErrorMessage(''); // モーダルを開く際にエラーメッセージをクリア
    }
    const handleClose = () => {
        setOpen(false);
        setErrorMessage(''); // モーダルを閉じる際にエラーメッセージをクリア
    }
    const handleTextareaChange = (event) => {
        setTextAreaValue(event.target.value);
    }
    const handleCopy = () => {
        navigator.clipboard.writeText(textAreaValue);
    }
    const handleApply = () => {
        try {
            const formData = JSON.parse(textAreaValue);
            form.map((formItem, index) => {
                if (formData[index]) {
                    formItem.formData = formData[index];
                    if (lc_initdata['formName'] == 'slc_message-data' ||
                        lc_initdata['formName'] == 'slc_trigger-data') {
                        if (index % 2 === 1) {
                            if (formData[index - 1].type && lc_initdata['subSchema'][formData[index - 1].type]) {
                                formItem["schema"] = subSchema[formData[index - 1].type];
                            }
                        }
                    }
                }
            });
            formValueElement.value = textAreaValue;
            setErrorMessage(''); // エラーメッセージをクリア
            handleClose(); // 正常な場合のみモーダルを閉じる
        } catch (error) {
            console.error('Invalid JSON format:', error);
            setErrorMessage(__('Invalid JSON format: ', 'lineconnect') + error.message); // JSONが無効な場合はエラーメッセージを設定
            // モーダルは閉じない
        }
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
            <Modal
                open={open}
                onClose={handleClose}
                aria-labelledby="simple-modal-title"
                aria-describedby="simple-modal-description"
            >
                <div style={getModalStyle()} className={classes.paper}>
                    <h2 id="simple-modal-title">{__('JSON Data', 'lineconnect')}</h2>
                    <p id="simple-modal-description">
                        <textarea
                            rows={40}
                            cols={40}
                            value={textAreaValue}
                            onChange={handleTextareaChange}
                            style={{ width: '100%', height: '100%' }}
                        />
                    </p>
                    <p style={{ textAlign: 'right' }}>
                        <Button variant="outlined" color="default" onClick={handleCopy}>
                            {__('Copy', 'lineconnect')}
                        </Button>
                        <Button variant="outlined" color="secondary" onClick={handleClose}>
                            {__('Close', 'lineconnect')}
                        </Button>
                        <Button variant="outlined" color="primary" onClick={handleApply}>
                            {__('Apply', 'lineconnect')}
                        </Button>
                    </p>
                    <p style={{ color: 'red', textEmphasisStyle: 'bold' }}>
                        {errorMessage}
                    </p>
                </div>
            </Modal>
            <Button variant="outlined" color="primary" onClick={handleOpen}>{__('View as JSON', 'lineconnect')}</Button>
        </>
    );
}
export default RJSFForm;