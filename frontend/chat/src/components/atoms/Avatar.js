import React from 'react';
import PropTypes from 'prop-types';

const Avatar = ({ src = null, alt = 'Avatar', className = '' }) => {
    const baseClasses = 'w-10 h-10 mr-2 rounded-full inline-block align-middle';

    if (src) {
        return (
            <img
                src={src}
                alt={alt}
                className={`${baseClasses} ${className}`.trim()}
            />
        );
    }

    return (
        <div
            className={`${baseClasses} bg-gray-300 ${className}`.trim()}
        />
    );
};

Avatar.propTypes = {
    src: PropTypes.string,
    alt: PropTypes.string,
    className: PropTypes.string,
};

export default Avatar;
