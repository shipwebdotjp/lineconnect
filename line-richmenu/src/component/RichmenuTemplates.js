const __ = wp.i18n.__;

const RichmenuTemplates = (props) => {
    const handleSelect = (richmenu) => {
        if (props.onSelected) {
            props.onSelected(richmenu);
        }
    };

    return (
        <div className="richmenu-templates">
            <div className="py-2 my-2 flex justify-between items-center flex-wrap">
                {props.templateList.map((template) => (
                    <div key={template.richMenuId} className="border p-4 mb-4 rounded">
                        <div className="mb-4">
                            <h3 className="text-lg font-bold">{template.title}</h3>
                            <button
                                type="button"
                                className="hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                                onClick={() => handleSelect(template.data)}
                            >
                                <img src={template.image} alt={template.title} />
                            </button>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}

export default RichmenuTemplates;