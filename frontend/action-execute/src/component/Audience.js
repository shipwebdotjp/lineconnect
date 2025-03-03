import React, { useState, useEffect } from "react";
import validator from '@rjsf/validator-ajv8';
import Form from '@rjsf/material-ui';
import { TranslatableString, englishStringTranslator, replaceStringParameters } from '@rjsf/utils';
import Button from '@material-ui/core/Button';
const __ = wp.i18n.__;

const Audience = (props) => {
    const slc_audiences = lc_initdata['slc_audiences'];

    const [form, setForm] = useState(lc_initdata['audienceForm']);
    const translateString = lc_initdata['translateString'];
    const [data, setData] = useState([]);

    const log = (type) => console.log.bind(console, type);

    //　初期データを反映させる
    useEffect(() => {
        const newData = [...data];
        newData[0] = lc_initdata['audienceForm'][0]['formData'];
        setData(newData);
        props.handleFormChange(newData);
    }, [])

    // フォームが変更された時
    const onFormChange = (_form, id) => {
        if (id == undefined) {
            return;
        }
        const formIdx = parseInt(id.split('_')[1]);
        const field = id.split('_')[2];

        // set formData to form[formIdx]
        const newData = [...data];
        newData[formIdx] = _form.formData;
        setData(newData);
        props.handleFormChange(newData);
    }

    const changeKeyLabel = (stringToTranslate, params) => {
        if (translateString[stringToTranslate]) {
            return replaceStringParameters(translateString[stringToTranslate], params);
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

    const onAudienceIdChange = (post_id) => {
        if (post_id == '0') {
            // clear current formData and schema
            const newform = [...form];
            form.map((value, index) => {
                newform[index]["formData"] = null;
                if (index % 2 == 1) {
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
                'action': 'lc_ajax_get_slc_audience',
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
            setResult({ "result": "failed", "error": [error, XMLHttpRequest.responseText] });
        });
    }

    return (
        <>
            <div className="py-2 px-4 bg-blue-200">{__('Audience', 'lineconnect')}</div>
            <div className="py-2  px-4 my-2">
                {__('Template', 'lineconnect')}:
                <select id="slc_audience_id" name="slc_audience_id" onChange={(e) => onAudienceIdChange(e.target.value)}>
                    <option value="0">{__('New Audience', 'lineconnect')}</option>
                    {slc_audiences.map((value, index) => {
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
export default Audience;