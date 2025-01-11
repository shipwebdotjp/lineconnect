import { Rnd } from 'react-rnd';
import { useCallback, useState } from 'react';

const RichMenuPreview = ({ richmenu, imageUrl, areaFocusedIndex, onAreaChange }) => {
    const [activeIndex, setActiveIndex] = useState(null);

    // マウスダウン時のハンドラー
    const handleMouseDown = useCallback((index) => {
        setActiveIndex(index);
    }, []);

    const handleDragStop = useCallback((e, d, index) => {
        const updatedRichmenu = { ...richmenu };
        const unscaledX = Math.round(d.x / scaleFactor);
        const unscaledY = Math.round(d.y / scaleFactor);
        
        updatedRichmenu.areas[index] = {
            ...updatedRichmenu.areas[index],
            bounds: {
                ...updatedRichmenu.areas[index].bounds,
                x: unscaledX,
                y: unscaledY
            }
        };
        
        onAreaChange(updatedRichmenu);
    }, [richmenu, scaleFactor, onAreaChange]);

    // リサイズ終了時のハンドラー
    const handleResizeStop = useCallback((e, direction, ref, delta, position, index) => {
        const updatedRichmenu = { ...richmenu };
        const unscaledX = Math.round(position.x / scaleFactor);
        const unscaledY = Math.round(position.y / scaleFactor);
        const unscaledWidth = Math.round(ref.offsetWidth / scaleFactor);
        const unscaledHeight = Math.round(ref.offsetHeight / scaleFactor);
        
        updatedRichmenu.areas[index] = {
            ...updatedRichmenu.areas[index],
            bounds: {
                x: unscaledX,
                y: unscaledY,
                width: unscaledWidth,
                height: unscaledHeight
            }
        };
        
        onAreaChange(updatedRichmenu);
    }, [richmenu, scaleFactor, onAreaChange]);
    
    if (!imageUrl && !richmenu.imageUrl) {
        return null;
    }

    const previewWidth = 500;
    const previewAspectRatio = richmenu.size.width ? richmenu.size.height / richmenu.size.width : 2 / 3;
    const scaleFactor = previewWidth / richmenu.size.width;



    const renderBoundingBoxes = () => {
        return richmenu.areas?.map((area, index) => {
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
                        border: areaFocusedIndex === index ? '2px solid rgba(255, 0, 0, 0.8)' : '2px solid rgba(64, 64, 64, 0.8)',
                        backgroundColor: areaFocusedIndex === index ? 'rgba(255, 0, 0, 0.2)' : 'rgba(64, 64, 64, 0.2)',
                        zIndex: activeIndex === index ? 1000 : 100
                    }}
                    onMouseDown={(e) => {
                        e.stopPropagation();
                        handleMouseDown(index);
                    }}
                    onDragStop={(e, d) => handleDragStop(e, d, index)}
                    onResizeStop={(e, direction, ref, delta, position) => 
                        handleResizeStop(e, direction, ref, delta, position, index)
                    }
                    bounds="parent"
                    enableResizing={{
                        top: true,
                        right: true,
                        bottom: true,
                        left: true,
                        topRight: true,
                        topLeft: true,
                        bottomRight: true,
                        bottomLeft: true
                    }}
                >
                    <div className="absolute top-0 left-0 text-xs text-red-500 bg-white px-1 py-0.5">
                        {index + 1}
                    </div>
                </Rnd>
            );
        });
    };

    return (
        <div 
            className="richmenu-preview-container relative" 
            style={{ width: previewWidth, height: previewWidth * previewAspectRatio }}
        >
            <div className="richmenu-preview absolute top-0 left-0 w-full h-full bg-gray-200 overflow-hidden">
                <img 
                    src={imageUrl || richmenu.imageUrl} 
                    alt="richmenu preview" 
                    className="w-full h-full object-fill" 
                />
                {renderBoundingBoxes()}
            </div>
        </div>
    );
};

export default RichMenuPreview;