import React from 'react';
import PropTypes from 'prop-types';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";

const ChannelSelector = ({ channels = [], selectedChannelId = null, onSelect }) => {

    return (
        <div className="w-full">
            <label htmlFor="channel-select" className="sr-only">Select a channel</label>
            <Select
                id="channel-select"
                value={selectedChannelId || ''}
                onValueChange={(value) => onSelect(value)}
            >
                <SelectTrigger className="w-full">
                    <SelectValue placeholder={selectedChannelId || 'Select a channel'} />
                </SelectTrigger>
                <SelectContent>
                    {channels.map(channel => (
                        <SelectItem key={channel.prefix} value={channel.prefix}>
                            {channel.name}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
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
