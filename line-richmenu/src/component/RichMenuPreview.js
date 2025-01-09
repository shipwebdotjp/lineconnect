// リッチメニューの画像をプレビューするコンポーネント
const RichMenuPreview = (props) => {
    // props.imageUrlがなければ何も表示しない
    if (!props.imageUrl && !props.richmenu.imageUrl) {
        return null;
    }

    const previewWidth = 500;
    const previewAspectRatio = props.richmenu.size.width ? props.richmenu.size.height / props.richmenu.size.width : 2 / 3;

    return (
        <div className="richmenu-preview-container relative" style={{ width: previewWidth, height: previewWidth * previewAspectRatio }}>
            <div className="richmenu-preview absolute top-0 left-0 w-full h-full bg-gray-200">
                <img src={props.imageUrl || props.richmenu.imageUrl} alt="richmenu preview" className="w-full h-full object-cover" />
            </div>
        </div>
    );
}

export default RichMenuPreview;