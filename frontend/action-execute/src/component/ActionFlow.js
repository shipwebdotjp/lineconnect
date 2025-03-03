import React, { useState, useEffect } from "react";
import validator from '@rjsf/validator-ajv8';
import Form from '@rjsf/material-ui';
import { TranslatableString, englishStringTranslator, replaceStringParameters } from '@rjsf/utils';
import Button from '@material-ui/core/Button';
const __ = wp.i18n.__;

const ActionFlow = (props) => {
    const slc_actionflows = lc_initdata['slc_actionflows'];

    const changeKeyLabel = (stringToTranslate, params) => {
        if (translateString[stringToTranslate]) {
            return translateString[stringToTranslate];
        } else {
            return englishStringTranslator(stringToTranslate, params);
        }
    }

    const AddButton = (props) => {
        const { icon, iconType, uiSchema, registry, ...btnProps } = props;
        const uiOptions = uiSchema['ui:options'] || {};
        return (
            <Button variant='outlined' color='primary' {...btnProps}>
                {uiOptions['addText'] || 'Add'}
            </Button>
        );
    }

    const onActionFlowIdChange = (post_id) => {
        if (post_id == '0') {
            // clear current formData and schema
            const newform = [...form];
            form.map((value, index) => {
                newform[index]["formData"] = null;
                if (index % 2 == 1) {
                    newform[index]["schema"] = {};
                }
            });
            setForm(newform);
            props.handleFormChange(null);
        } else {
            jQuery.ajax({
                type: "POST",
                url: lc_initdata['ajaxurl'],
                data: {
                    'action': 'lc_ajax_get_slc_actionflow',
                    'nonce': lc_initdata['ajax_nonce'],
                    'post_id': post_id,
                },
                dataType: 'json'
            }).done(function (data) {
                setData(data.formData);
                const newform = [...form];
                form.map((value, index) => {
                    newform[index]["formData"] = null;
                    if (index % 2 == 1) {
                        newform[index]["schema"] = {};
                    }
                });

                data.formData.map((value, index) => {
                    newform[index]["formData"] = value;
                });
                setForm(newform);
                props.handleFormChange(data.formData);

            }).fail(function (XMLHttpRequest, textStatus, error) {
                console.log("Error: " + error);
            });
        }
    }

    const [form, setForm] = useState(lc_initdata['actionFlowForm'] ? lc_initdata['actionFlowForm'] : []);
    const [data, setData] = useState(form[0] ? form[0].formData : null);
    const translateString = lc_initdata['translateString'];

    const onFormChange = (e, id) => {
        const newform = [...form];
        newform[id]["formData"] = e.formData;
        setForm(newform);

        const newData = [];
        form.map((value, index) => {
            newData[index] = value.formData;
        });

        props.handleFormChange(newData);
    };

    return (
        <>
            <div className="py-2 px-4 bg-blue-200">{__('Action Flow', 'lineconnect')}</div>
            <div className="py-2 px-4 my-2">
                {__('Template', 'lineconnect')}:
                <select id="slc_actionflow_id" name="slc_actionflow_id" onChange={(e) => onActionFlowIdChange(e.target.value)}>
                    <option value="0">{__('New Action Flow', 'lineconnect')}</option>
                    {slc_actionflows.map((value, index) => {
                        return (
                            <option key={index} value={value.post_id}>{value.title}</option>
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
                            onChange={(e) => onFormChange(e, id)}
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
                    );
                })}
            </div>
        </>
    );
}

export default ActionFlow;