import React, { useState } from 'react';
import Result from './Result';

const __ = wp.i18n.__;

const RichmenuAlias = ({ channel, richmenuList, aliasList, setAliasList }) => {
    const [result, setResult] = useState([]);

    return (
        <div className="RichmenuAlias">
            <header className="RichmenuHeader text-lg p-2 my-2">
                {__('LINE Richmenu Alias', 'lineconnect')}
            </header>

            <Result result={result} />
        </div>
    );
}

export default RichmenuAlias;