import React from 'react';
import validator from '@rjsf/validator-ajv8';
import Form from '@rjsf/mui';
import { englishStringTranslator, replaceStringParameters } from '@rjsf/utils';
import { createTheme, ThemeProvider } from "@mui/material/styles";
import Button from '@mui/material/Button';

const __ = wp.i18n.__;

const UserDataEditForm = ({ user, type, onEdit, onClose }) => {
    const schema = lc_initdata['userDataSchema'][type] || {};
    const uiSchema = lc_initdata['userDataUiSchema'][type] || {};
    const formData = user ? user[type] : {};
    const translateString = lc_initdata['translateString'];

    const theme = createTheme({
        components: {
            MuiTextField: {
                defaultProps: {
                    variant: "filled",
                },
            },
        },
    });

    const changeKeyLabel = (stringToTranslate, params) => {
        if (translateString[stringToTranslate]) {
            return replaceStringParameters(translateString[stringToTranslate], params);
        } else {
            return englishStringTranslator(stringToTranslate, params);
        }
    }

    const AddButton = (props) => {
        const { uiSchema, ...btnProps } = props;
        const uiOptions = uiSchema['ui:options'] || {};
        return (
            <Button variant='outlined' color='primary' {...btnProps}>
                {uiOptions['addText'] || __('Add', 'lineconnect')}
            </Button>
        );
    }

    const handleSubmit = ({ formData }) => {
        onEdit(formData);
    };

    const returnTitle = () => {
        switch (type) {
            case 'profile':
                return __('Edit Profile', 'lineconnect');
            case 'tags':
                return __('Edit Tags', 'lineconnect');
            case 'scenarios':
                return __('Edit Scenarios', 'lineconnect');
            default:
                return __('Edit User Data', 'lineconnect');
        }
    };

    return (
        <div className="relative z-10" aria-labelledby="dialog-title" role="dialog" aria-modal="true">
            <div className="fixed inset-0 bg-gray-500/75 transition-opacity" aria-hidden="true"></div>
            <div className="fixed inset-0 z-10 flex items-center justify-center p-4">
                <div className="w-4/5 max-h-full overflow-y-auto bg-white rounded-lg shadow-xl flex flex-col">
                    <header className="text-lg p-4 border-b w-full flex items-center flex-shrink-0">
                        <h2 id="dialog-title" className="font-semibold">
                            {returnTitle()}
                        </h2>
                        <button
                            type="button"
                            className="ml-auto text-gray-500 hover:text-gray-700 focus:outline-none"
                            onClick={onClose}
                        >
                            <svg className="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </header>
                    <div className="w-full p-4 flex-grow overflow-y-auto">
                        <ThemeProvider theme={theme}>
                            <Form
                                schema={schema}
                                uiSchema={uiSchema}
                                formData={formData}
                                validator={validator}
                                onSubmit={handleSubmit}
                                translateString={changeKeyLabel}
                                id={'user-data-edit-form'}
                                omitExtraData={true}
                                templates={{
                                    ButtonTemplates: {
                                        AddButton: AddButton,
                                    },
                                }}
                            >
                                <div className="mt-4 flex justify-end space-x-2">
                                    <Button
                                        onClick={onClose}
                                        variant="outlined"
                                    >
                                        {__('Cancel', 'lineconnect')}
                                    </Button>
                                    <Button
                                        type="submit"
                                        variant="contained"
                                        color="primary"
                                    >
                                        {__('Save', 'lineconnect')}
                                    </Button>
                                </div>
                            </Form>
                        </ThemeProvider>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default UserDataEditForm;