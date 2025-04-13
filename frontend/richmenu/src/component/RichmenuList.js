import DeleteIcon from '@mui/icons-material/Delete';
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
            <div className="py-2 my-2">
                {Object.values(props.richmenuList).map((richmenu) => (
                    <div key={richmenu.richMenuId} className="border p-4 mb-4 rounded">
                        <div className="flex justify-between items-center">
                            <div className="w-1/2 max-w-sm">
                                <h3 className="text-lg font-bold">{richmenu.name}</h3>
                                
                            </div>
                            <div className="">
                                <button
                                    type="button"
                                    className="text-red-500 hover:text-red-700 disabled:text-red-300 py-4 px-4 rounded w-full max-w-48 flex items-center"
                                    onClick={() => handleDelete(richmenu.richMenuId)}
                                >
                                    <DeleteIcon />{__('Delete', 'lineconnect')}
                                </button>
                            </div>
                        </div>
                        <div className="mt-2 flex juustify-start items-center">
                            <img src={richmenu.imageUrl} alt={richmenu.name} className="h-auto w-full max-w-xs" />
                            <div className="w-full flex justify-center items-center">
                                <button
                                        type="button"
                                        className="border border-blue-500 text-blue-600 hover:bg-blue-500 hover:text-white py-4 px-16 rounded "
                                        onClick={() => handleSelect(richmenu)}
                                    >
                                        {__('Use as template', 'lineconnect')}
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