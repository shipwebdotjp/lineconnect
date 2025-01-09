import React, { useState, useEffect, useCallback } from "react";
import {useDropzone} from 'react-dropzone';
const __ = wp.i18n.__;
const fileTypes = ["JPG", "PNG"];

// アップロードコンポーネント
const RichMenuUpload = ({ onFileSelect, error }) => {
  const validateImage = useCallback((file) => {
    return new Promise((resolve, reject) => {
      if (file.size > 1024 * 1024) {
        reject(new Error(__('File size must be less than 1MB', 'lineconnect')));
        return;
      }

      const img = new Image();
      img.src = URL.createObjectURL(file);

      img.onload = () => {
        URL.revokeObjectURL(img.src);
        
        if (img.width < 800 || img.width > 2500) {
          reject(new Error(__('Image width must be between 800px and 2500px', 'lineconnect')));
          return;
        }
        
        if (img.height < 250) {
          reject(new Error(__('Image height must be 250px or higher', 'lineconnect')));
          return;
        }

        const aspectRatio = img.width / img.height;
        if (aspectRatio < 1.45) {
          reject(new Error(__('Image aspect ratio must be 1.45 or higher', 'lineconnect')));
          return;
        }

        resolve(file);
      };

      img.onerror = () => {
        URL.revokeObjectURL(img.src);
        reject(new Error(__('Invalid image file', 'lineconnect')));
      };
    });
  });

  const onDrop = useCallback(async (acceptedFiles) => {
    if (acceptedFiles.length === 0) {
      onFileSelect(null, __('Invalid file type', 'lineconnect'));
      return;
    }

    try {
      const file = acceptedFiles[0];
      await validateImage(file);
      onFileSelect(file, null);
    } catch (err) {
      onFileSelect(null, err.message);
    }
  }, [onFileSelect]);

  const { getRootProps, getInputProps, isDragActive } = useDropzone({
    onDrop,
    accept: {
      'image/jpeg': ['.jpg', '.jpeg'],
      'image/png': ['.png']
    },
    maxFiles: 1
  });

  return (
    <div className="w-full">
      <div
        {...getRootProps()}
        className={`p-8 border-2 border-dashed rounded-lg cursor-pointer transition-colors
          ${isDragActive 
            ? 'border-blue-500 bg-blue-50' 
            : 'border-gray-300 hover:border-gray-400'}`}
      >
        <input {...getInputProps()} />
        <div className="text-center">
          <p className="text-gray-600 mb-2">
            {isDragActive
              ? __('Drop the file here', 'lineconnect')
              : __('Drag and drop the file here, or click to select file', 'lineconnect')
            }
          </p>
          <p className="text-sm text-gray-500">
            {__('File type: JPEG or PNG', 'lineconnect')}
          </p>
        </div>
      </div>

      {error && (
        <div className="mt-4 p-4 bg-red-50 border border-red-200 rounded-md">
          <p className="text-red-600 text-sm">{error}</p>
        </div>
      )}
    </div>
  );
};


/*
const thumbsContainer = {
    display: 'flex',
    flexDirection: 'row',
    flexWrap: 'wrap',
    marginTop: 16
  };
  

  
  const thumbInner = {
    display: 'flex',
    minWidth: 0,
    overflow: 'hidden'
  };
  
  const img = {
    display: 'block',
    width: 'auto',
    height: '100%'
  };
*/
/*
const RichmenuImage = (props) => {
    const [files, setFiles] = useState([]);
    const {getRootProps, getInputProps} = useDropzone({
      accept: {
        'image/*': ['.jpg', '.jpeg', '.png']
      },
      maxFiles: 1,
      multiple: false,
      onDrop: acceptedFiles => {
        setFiles(acceptedFiles.map(file => Object.assign(file, {
          preview: URL.createObjectURL(file)
        })));
        if (props.onFileSelected) {
            props.onFileSelected(acceptedFiles[0]);
        }
      }
    });
    const imgWidth = 500;
    const imgAspectRatio = props.richmenu.size.width ?  props.richmenu.size.height / props.richmenu.size.width : 2/3;
    const thumb = {
        display: 'inline-flex',
        borderRadius: 2,
        border: '1px solid #eaeaea',
        marginBottom: 8,
        marginRight: 8,
        width: imgWidth,
        height: imgWidth * imgAspectRatio,
        padding: 4,
        boxSizing: 'border-box'
      };

    const thumbs = files.map(file => (
      <div style={thumb} key={file.name}>
        <div style={thumbInner}>
          <img
            src={file.preview}
            style={img}
            // Revoke data uri after image is loaded
            onLoad={() => { URL.revokeObjectURL(file.preview) }}
          />
        </div>
      </div>
    ));
  
    useEffect(() => {
      // Make sure to revoke the data uris to avoid memory leaks, will run on unmount
      return () => files.forEach(file => URL.revokeObjectURL(file.preview));
    }, [files]);
  
    return (
      <section className="container">
        <h4>{__('Richmenu Image', 'lineconnect')}</h4>
        <div {...getRootProps({className: 'dropzone border-dashed border-2 border-gray-300 rounded p-4'})}>
          <input {...getInputProps()} />
          <p>{__('Drag and drop richmenu image file here, or click to select file. File Type: JPEG or PNG. Max filesize: 1MB, Width: between 800px and 2500px, Height: 250px or higher. ', 'lineconnect')}</p>
        </div>
        <aside style={thumbsContainer}>
          {thumbs}
        </aside>
      </section>
    );
}
*/
export default RichMenuUpload;