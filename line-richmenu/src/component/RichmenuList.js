const __ = wp.i18n.__;
const RichmenuList = (props) => {
    return <>
        <div className="py-2 px-4 bg-blue-200">{__('Richmenu list', 'lineconnect')}</div>
        <div className="py-2 my-2">
            {Object.values(props.richmenuList).map((value, index) => {
                return (
                    <label key={index} className="p-2 mr-2">
                        {value['name']}
                    </label>
                );
            })}
        </div>
    </>
}

export default RichmenuList