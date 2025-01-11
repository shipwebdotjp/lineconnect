const __ = wp.i18n.__;

const RichmenuList = (props) => {
    const handleSelect = (richmenu) => {
        if (props.onSelected) {
            props.onSelected(richmenu);
        }
    };

    const handleDelete = (richmenuId) => {
        if (props.onDelete && window.confirm(__('Are you sure you want to delete this richmenu?', 'lineconnect'))) {
            props.onDelete(richmenuId);
        }
    };

    return (
        <div className="richmenu-list">
            <div className="py-2 px-4 bg-blue-200">{__('Richmenu list', 'lineconnect')}</div>
            <div className="py-2 my-2">
                {Object.values(props.richmenuList).map((richmenu) => (
                    <div key={richmenu.richMenuId} className="border p-4 mb-4 rounded">
                        <div className="flex justify-between items-center">
                            <div className="w-1/2 max-w-sm">
                                <h3 className="text-lg font-bold">{richmenu.name}</h3>
                                <img src={richmenu.imageUrl} alt={richmenu.name} className="h-auto w-full max-w-xs" />
                            </div>
                            <div className="w-1/2 max-w-sm space-y-6 flex flex-col items-center">
                                <button
                                    type="button"
                                    className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-4 px-4 rounded w-full max-w-48"
                                    onClick={() => handleSelect(richmenu)}
                                >
                                    {__('Use as template', 'lineconnect')}
                                </button>
                                <button
                                    type="button"
                                    className="bg-red-500 hover:bg-red-700 text-white font-bold py-4 px-4 rounded w-full max-w-48"
                                    onClick={() => handleDelete(richmenu.richMenuId)}
                                >
                                    {__('Delete', 'lineconnect')}
                                </button>
                            </div>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
};

export default RichmenuList;