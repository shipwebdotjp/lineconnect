import React from 'react';
import PropTypes from 'prop-types';

// This is a placeholder implementation using a standard select.
// Please add the shadcn/ui Select component to your project
// by running `npx shadcn-ui@latest add select`
// and then replace the content of this file with the shadcn version.

const ChannelSelector = ({ channels = [], selectedChannelId = null, onSelect }) => {
    const handleSelectChange = (e) => {
        onSelect(e.target.value);
    };

    return (
        <div className="channel-selector">
            <select value={selectedChannelId || ''} onChange={handleSelectChange}>
                <option value="" disabled>Select a channel</option>
                {channels.map(channel => (
                    <option key={channel.prefix} value={channel.prefix}>
                        {channel.name}
                    </option>
                ))}
            </select>
        </div>
    );
};

ChannelSelector.propTypes = {
    channels: PropTypes.arrayOf(PropTypes.shape({
        prefix: PropTypes.string.isRequired,
        name: PropTypes.string.isRequired,
    })).isRequired,
    selectedChannelId: PropTypes.string,
    onSelect: PropTypes.func.isRequired,
};

export default ChannelSelector;
