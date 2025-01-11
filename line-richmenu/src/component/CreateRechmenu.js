import React, { useState, useEffect } from 'react';  // useEffectをインポート
import validator from '@rjsf/validator-ajv8';
// import Form from '@rjsf/material-ui';
import Form from '@rjsf/mui';
import { TranslatableString, englishStringTranslator, replaceStringParameters, getTemplate, getUiOptions, titleId } from '@rjsf/utils';
import { createTheme, ThemeProvider } from "@mui/material/styles";
import Button from '@mui/material/Button';
import Grid from '@mui/material/Grid';
import Box from '@mui/material/Box';
import Divider from '@mui/material/Divider';
import Typography from '@mui/material/Typography';
const __ = wp.i18n.__;

// カスタムフィールドテンプレート
const HorizontalFieldTemplate = ({ id, children, classNames, disabled, label, description, errors, rawErrors }) => {
  return (
    <div className={classNames}>
      <Grid container spacing={2} alignItems="center">
        {children}
      </Grid>
    </div>
  );
};


const ObjectFieldTemplate = (props) => {
    const { registry, properties, title, description, uiSchema, required, schema, idSchema } = props;
    const options = getUiOptions(uiSchema);
    const TitleFieldTemplate = getTemplate('TitleFieldTemplate', registry, options);
    
    return (
      <div>
        {title && (
          <TitleFieldTemplate
            id={titleId(idSchema)}
            title={title}
            required={required}
            schema={schema}
            uiSchema={uiSchema}
            registry={registry}
          />
        )}{' '}
        {description}
        <Grid container spacing={2}>
          {properties.map((prop) => (
            (
                <Grid item xs={prop.content.props.uiSchema["ui:column"] ?? 12} key={prop.content.key}>
                {prop.content}
                </Grid>
            )
          ))}
        </Grid>
      </div>
    );
  }

// カスタムArrayFieldTitleTemplate
const ArrayFieldTitleTemplate = (props) => {
    const { id, title, schema, uiSchema, required, registry } = props;
    // console.log(title); //root_new_richmenu_areas_4__title
    const match = id.match(/areas_(\d+)__title$/);
    if(match){

        const index = match ? parseInt(match[1]) + 1 : '';
        
        return (
            <Box id={id} mb={1} mt={1}>
                <Typography variant='h5'>{title} #{index}</Typography>
                <Divider />
            </Box>
        );
    }else{
        // return default title
        return (
            <Box id={id} mb={1} mt={1}>
                <Typography variant='h5'>{title}</Typography>
                <Divider />
            </Box>
        );
    }
};

const CreateRechmenu = (props) => {
    // formの初期状態でprops.richmenuをformDataとして設定
    const [form, setForm] = useState({
        ...lc_initdata['form'],
        formData: props.richmenu || {}  // 空オブジェクト
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

    // カスタム UI Schema
    const customUiSchema = {
        ...form.uiSchema,
        size: {
            "ui:ObjectFieldTemplate": ObjectFieldTemplate,
            width: {
                "ui:widget": "updown",
                "ui:column": 6,
            },
            height: {
                "ui:widget": "updown",
                "ui:column": 6,
            },
        },
        areas: {
            items: {
                bounds: {
                    "ui:ObjectFieldTemplate": ObjectFieldTemplate,
                    x: {
                        "ui:column": 3,
                    },
                    y: {
                        "ui:column": 3,
                    },
                    width: {
                        "ui:column": 3,
                    },
                    height: {
                        "ui:column": 3,
                    },
                },
                "ui:order": ["bounds", "action"],
            },
            "ui:options": {
                addText: __('Add tap areas', 'lineconnect'),
                copyable: true,
            },
        },
    };

    useEffect(() => {
        console.log('Form Data Updated:', props.richmenu);
        console.log('Internal Form State:', form.formData);
        setForm(prevForm => ({
            ...prevForm,
            formData: { ...props.richmenu }, // 新しいオブジェクトを設定
        }));
    }, [props.richmenu]);

    // useEffect(() => {
    //     console.log(props.richmenu);
    //     setForm(prevForm => ({
    //         ...prevForm,
    //         formData: props.richmenu || {}
    //     }));
    // }, [props.richmenu]);

    const onFormChange = ( _form, id) => {
        // console.log(JSON.stringify(form.schema));
        // console.log(JSON.stringify(form.formData));
        if(id == undefined){
            return;
        }
        // フォームの状態を更新
        setForm(prevForm => ({
            ...prevForm,
            formData: _form.formData
        }));
        props.onFormChange(_form.formData);
    }

    // フォームにフォーカスがあたった場合
    const onFormFocus = (id) => {
        //id: root_new_richmenu_areas_0_bounds_x
        // get areas index
        const index = id.match(/areas_(\d+)_/);
        if(index){
            props.onAreaFocus(parseInt(index[1]));
        }
    }

    // フォームからフォーカスが外れた場合
    const onFormBlur = (id) => {
        // get areas index
        const index = id.match(/areas_(\d+)_/);
        if(index){
            props.onAreaFocus(null);
        }
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
    // const ArrayFieldItemTemplate = (props) => {
    //     const { children, className } = props;
    //     return <div className={className}>{children}</div>;
    // }



    return (
        <>
            <div className="py-2 px-4 bg-white">
                <ThemeProvider theme={theme}>
                    <Form 
                        key={JSON.stringify(props.richmenu)}
                        schema={form.schema}
                        uiSchema={customUiSchema}
                        formData={form.formData}
                        validator={validator}
                        translateString={changeKeyLabel}
                        onChange={onFormChange}
                        onFocus={onFormFocus}
                        onBlur={onFormBlur}
                        id={`rjsf_new_richmenu`}
                        idPrefix={`root_new_richmenu`}
                        liveOmit={form.props.liveOmit ?? false}
                        omitExtraData={form.props.omitExtraData ?? false}
                        liveValidate={form.props.liveValidate ?? false}
                        showErrorList={form.props.showErrorList ?? 'bottom'}
                        templates={{ ButtonTemplates: { AddButton }, TitleFieldTemplate: ArrayFieldTitleTemplate }}
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