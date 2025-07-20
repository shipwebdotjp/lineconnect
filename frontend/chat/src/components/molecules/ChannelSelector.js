import React from 'react';
import PropTypes from 'prop-types';

const ChannelSelector = ({ channels = [], selectedChannelId = null, onSelect }) => {
    const handleSelectChange = (e) => {
        onSelect(e.target.value);
    };

    return (
        <div className="w-full">
            <label htmlFor="channel-select" className="sr-only">Select a channel</label>
            <select
                id="channel-select"
                value={selectedChannelId || ''}
                onChange={handleSelectChange}
                className="w-full bg-transparent placeholder:text-slate-400 text-slate-700 text-sm border border-slate-200 rounded pl-3 pr-8 py-3 transition duration-300 ease focus:outline-none focus:border-slate-400 hover:border-slate-400 shadow-sm focus:shadow-md appearance-none cursor-pointer"
            >
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
