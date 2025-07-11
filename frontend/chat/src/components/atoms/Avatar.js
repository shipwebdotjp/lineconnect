import React from 'react';
import PropTypes from 'prop-types';

const Avatar = ({ src = null, alt = 'Avatar', style = {} }) => {
    const baseStyle = {
        width: '40px',
        height: '40px',
        borderRadius: '50%',
        display: 'inline-block',
        verticalAlign: 'middle',
    };

    const combinedStyle = { ...baseStyle, ...style };

    if (src) {
        return <img src={src} alt={alt} style={combinedStyle} />;
    }

    return <div style={{ ...combinedStyle, backgroundColor: '#ccc' }} />;
};

Avatar.propTypes = {
    src: PropTypes.string,
    alt: PropTypes.string,
    style: PropTypes.object,
};


export default Avatar;
