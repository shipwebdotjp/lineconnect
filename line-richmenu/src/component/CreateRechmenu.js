import React, { useState, useEffect } from 'react';  // useEffectをインポート
import validator from '@rjsf/validator-ajv8';
// import Form from '@rjsf/material-ui';
import Form from '@rjsf/mui';
import { TranslatableString, englishStringTranslator, replaceStringParameters } from '@rjsf/utils';
import { createTheme, ThemeProvider } from "@mui/material/styles";
import Button from '@mui/material/Button';
// import RichmenuImage from './RichmenuImage';
const __ = wp.i18n.__;



const CreateRechmenu = (props) => {
    // formの初期状態でprops.richmenuをformDataとして設定
    const [form, setForm] = useState({
        ...lc_initdata['form'],
        formData: props.richmenu || {}  // props.richmenuが存在しない場合は空オブジェクト
    });
    // const [file, setFile] = useState(null);  // 選択されたファイルを管理するstate

        
    
    const translateString = lc_initdata['translateString'];

    // muiのテーマでデフォルトのTextFieldのvariantをfilledに設定
    const theme = createTheme({
        components: {
          MuiTextField: {
            defaultProps: {
              variant: "filled", // Default to the filled variant
            },
          },
        },
      });

    // propsのrichmenuが変更された時にformDataを更新
    useEffect(() => {
        setForm(prevForm => ({
            ...prevForm,
            formData: props.richmenu || {}
        }));
    }, [props.richmenu]);

    const onFormChange = ( _form, id) => {
        // console.log(JSON.stringify(form.schema));
        // console.log(JSON.stringify(form.formData));
        if(id == undefined){
            return;
        }
        props.onFormChange(_form.formData);
    }


    const changeKeyLabel = (stringToTranslate, params) => {
        if (translateString[stringToTranslate]) {
            return replaceStringParameters(translateString[stringToTranslate], params);
        }else{
            return englishStringTranslator(stringToTranslate, params);
        } 
    }

    const AddButton = (props) => {
        const { icon, iconType, uiSchema, registry, ...btnProps } = props;
        const uiOptions = uiSchema['ui:options'] || {};
        return (
            <Button variant='outlined' color='primary' {...btnProps}>
                {uiOptions['addText'] || 'Add' }
            </Button>
        );
    }

    return (
        <>
            <div className="py-2 px-4 bg-white">
                <ThemeProvider theme={theme}>
                    <Form 
                        schema={form.schema}
                        uiSchema={form.uiSchema}
                        formData={form.formData}
                        validator={validator}
                        translateString={changeKeyLabel}
                        onChange={onFormChange}
                        id={`rjsf_new_richmenu`}
                        idPrefix={`root_new_richmenu`}
                        liveOmit={form.props.liveOmit ?? false}
                        omitExtraData={form.props.omitExtraData ?? false}
                        liveValidate={form.props.liveValidate ?? false}
                        showErrorList={form.props.showErrorList ?? 'bottom'}
                        templates={{ ButtonTemplates: { AddButton } }}
                        experimental_defaultFormStateBehavior={{
                            constAsDefaults: 'skipOneOf'
                        }}
                    />
                </ThemeProvider>
            </div>
            <div className="py-2 px-4 bg-white">
                <Button variant="contained" color="primary" onClick={() => props.onFormSubmit()}>{__('Save', 'lineconnect')}</Button>
            </div>
        </>
    );
}

export default CreateRechmenu;