import React, { useState, useEffect } from "react";
// import { createRoot } from 'react-dom/client';
import validator from '@rjsf/validator-ajv8';
import Form from '@rjsf/mui';
// import Form from '@rjsf/chakra-ui'
import { TranslatableString, englishStringTranslator, replaceStringParameters, getTemplate, getUiOptions, titleId } from '@rjsf/utils';
import { createTheme, ThemeProvider } from "@mui/material/styles";
import Button from '@mui/material/Button';

const __ = wp.i18n.__;

const MessageFormBuilder = ({ buildMessages, setBuildMessages }) => {

    const subSchema = lc_initdata['messageSubSchema'];
    const slc_messages = lc_initdata['slc_messages'];

    const [form, setForm] = useState(lc_initdata['messageForm']);
    const translateString = lc_initdata['translateString'];
    const [data, setData] = useState([]);

    const log = (type) => console.log.bind(console, type);
    const theme = createTheme({
        components: {
            MuiTextField: {
                defaultProps: {
                    variant: "filled", // Default to the filled variant
                },
            },
        },
    });

    useEffect(() => {
        // Initialize form data from buildMessages if available
        if (buildMessages && buildMessages.length > 0) {
            setData(buildMessages);
            const newform = [...form];
            // clear current formData and schema
            form.map((value, index) => {
                // console.log(index);
                // console.log(newform[index]);
                newform[index]["formData"] = null;
                if (index % 2 == 1) {
                    newform[index]["schema"] = {};
                }
            });

            buildMessages.map((value, index) => {
                newform[index]["formData"] = value;
                if (index % 2 == 0 && value.type && lc_initdata['messageSubSchema'][value.type]) {
                    newform[index + 1]["schema"] = subSchema[value.type];
                }
            });
            setForm(newform);
        }
    }, []);

    const onFormChange = (_form, id) => {
        // console.log(_form);
        // console.log(id);
        if (id == undefined) {
            return;
        }
        const formIdx = parseInt(id.split('_')[1]);
        const field = id.split('_')[2];

        // set formData to form[formIdx]
        const newData = [...data];
        newData[formIdx] = _form.formData;
        setData(newData);

        if (lc_initdata['formName'] == 'chatform-data') {
            if (field == 'type') {
                if (_form.formData.type && lc_initdata['messageSubSchema'][_form.formData.type]) {
                    const newform = [...form];
                    newform[formIdx + 1]["schema"] = subSchema[_form.formData.type];
                    setForm(newform);
                }
            }
        }
        // console.log(newData);
        setBuildMessages(newData);
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

    const onMessageIdChange = (post_id) => {
        if (post_id == '0') {
            // New Message
            const newform = [...form];
            form.map((value, index) => {
                // console.log(index);
                // console.log(newform[index]);
                newform[index]["formData"] = null;
                if (index % 2 == 1) {
                    newform[index]["schema"] = {};
                }
            });
            setForm(form);
            setBuildMessages([]);
            return;
        }
        // Load message template
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
                if (index % 2 == 1) {
                    newform[index]["schema"] = {};
                }
            });

            data.formData.map((value, index) => {
                newform[index]["formData"] = value;
                if (index % 2 == 0 && value.type && lc_initdata['messageSubSchema'][value.type]) {
                    newform[index + 1]["schema"] = subSchema[value.type];
                }
            });
            setForm(newform);
            setBuildMessages(data.formData);

        }).fail(function (XMLHttpRequest, textStatus, error) {
            console.error('Failed to load template message:', error, XMLHttpRequest.responseText);
        });
    }

    return (
        <>
            <div className="py-2 px-4 bg-blue-200">{__('Message', 'lineconnect')}</div>
            <div className="py-2  px-4 my-2">
                {__('Template', 'lineconnect')}:
                <select id="slc_message_id" name="slc_message_id" onChange={(e) => onMessageIdChange(e.target.value)}>
                    <option value="0">{__('New message', 'lineconnect')}</option>
                    {slc_messages.map((value) => {
                        return (
                            <option key={value['post_id']} value={value['post_id']}>{value['title']}</option>
                        );
                    })}
                </select>
                {form.map((form, id) => {
                    return (
                        <ThemeProvider theme={theme} key={id}>
                            <Form
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
                                experimental_defaultFormStateBehavior={{
                                    constAsDefaults: 'skipOneOf'
                                }}
                            />
                        </ThemeProvider>
                    );
                })}
            </div>
        </>
    );
}
export default MessageFormBuilder;
