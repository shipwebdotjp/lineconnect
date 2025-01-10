import { Rnd } from 'react-rnd';

// リッチメニューの画像をプレビューするコンポーネント
const RichMenuPreview = (props) => {
    // props.imageUrlがなければ何も表示しない
    if (!props.imageUrl && !props.richmenu.imageUrl) {
        return null;
    }

    const previewWidth = 500;
    const previewAspectRatio = props.richmenu.size.width ? props.richmenu.size.height / props.richmenu.size.width : 2 / 3;
    const scaleFactor = previewWidth / props.richmenu.size.width;

    // バウンディングボックスを描画する関数
    const renderBoundingBoxes = () => {
        return props.richmenu.areas?.map((area, index) => {
            console.log(props.areaFocusedIndex);
            const { x, y, width, height } = area.bounds;
            const scaledX = x * scaleFactor;
            const scaledY = y * scaleFactor;
            const scaledWidth = width * scaleFactor;
            const scaledHeight = height * scaleFactor;

            return (
                <Rnd
                    key={index}
                    default={{
                        x: scaledX,
                        y: scaledY,
                        width: scaledWidth,
                        height: scaledHeight
                    }}
                    size={{
                        width: scaledWidth,
                        height: scaledHeight
                    }}
                    position={{
                        x: scaledX,
                        y: scaledY
                    }}
                    style={{
                        border: props.areaFocusedIndex == index ? '2px solid rgba(255, 0, 0, 0.8)' : '2px solid rgba(64, 64, 64, 0.8)',
                        backgroundColor: props.areaFocusedIndex == index ?  'rgba(255, 0, 0, 0.2)' : 'rgba(64, 64, 64, 0.2)',
                        pointerEvents: 'none' // Disable interaction for now
                    }}
                    disableDragging={true}
                    enableResizing={false}
                    bounds="parent"
                >
                    <div className="absolute top-0 left-0 text-xs text-red-500 bg-white px-1 py-0.5">{index + 1}</div>
                </Rnd>
            );
        });
    };

    return (
        <div className="richmenu-preview-container relative" style={{ width: previewWidth, height: previewWidth * previewAspectRatio }}>
            <div className="richmenu-preview absolute top-0 left-0 w-full h-full bg-gray-200">
                <img src={props.imageUrl || props.richmenu.imageUrl} alt="richmenu preview" className="w-full h-full object-cover" />
                {renderBoundingBoxes()}
            </div>
        </div>
    );
}

export default RichMenuPreview;
