import { useState, useEffect } from 'react';
import { useSearchParams, useNavigate } from 'react-router-dom';

export const useUrlFilters = (interactionId) => {
    const navigate = useNavigate();
    const [searchParams, setSearchParams] = useSearchParams();

    const [statusValues, setStatusValues] = useState([]);
    const [versionValues, setVersionValues] = useState([]);
    const [channelValues, setChannelValues] = useState([]);
    const [lineUserId, setLineUserId] = useState('');
    const [updatedAtStart, setUpdatedAtStart] = useState('');
    const [updatedAtEnd, setUpdatedAtEnd] = useState('');

    useEffect(() => {
        // URLからパラメータを取得して状態を初期化
        const statusFromUrl = searchParams.getAll('status');
        const versionFromUrl = searchParams.getAll('version');
        const channelFromUrl = searchParams.getAll('channel');
        const lineUserIdFromUrl = searchParams.get('line_user_id') || '';
        const updatedAtStartFromUrl = searchParams.get('updated_at_start') || '';
        const updatedAtEndFromUrl = searchParams.get('updated_at_end') || '';


        setStatusValues(statusFromUrl);
        setVersionValues(versionFromUrl);
        setChannelValues(channelFromUrl);
        setLineUserId(lineUserIdFromUrl);
        setUpdatedAtStart(updatedAtStartFromUrl);
        setUpdatedAtEnd(updatedAtEndFromUrl);
    }, [searchParams]);

    const updateFilter = (paramName, values, isSingleValue = false) => {
        const newSearchParams = new URLSearchParams(searchParams);

        // 既存のパラメータを削除
        newSearchParams.delete(paramName);

        // 新しい値を追加
        if (isSingleValue) {
            if (values && values.trim()) {
                newSearchParams.set(paramName, values.trim());
            }
        } else {
            values.forEach(value => {
                newSearchParams.append(paramName, value);
            });
        }

        // pageを1にリセット（フィルタ変更時は最初のページに戻る）
        newSearchParams.set('page', '1');

        // URLを更新（navigateでloader再実行）
        navigate(`/interactions/${interactionId}/sessions?${newSearchParams.toString()}`, { replace: true });
    };

    const handleStatusChange = (values) => {
        setStatusValues(values);
        updateFilter('status', values);
    };

    const handleVersionChange = (values) => {
        setVersionValues(values);
        updateFilter('version', values);
    };

    const handleChannelChange = (values) => {
        setChannelValues(values);
        updateFilter('channel', values);
    };

    const handleLineUserIdChange = (value) => {
        setLineUserId(value);
        updateFilter('line_user_id', value, true);
    };

    const handleUpdatedAtStartChange = (date) => {
        const isoString = date ? date.toISOString() : '';
        setUpdatedAtStart(isoString);
        updateFilter('updated_at_start', isoString, true);
    };

    const handleUpdatedAtEndChange = (date) => {
        const isoString = date ? date.toISOString() : '';
        setUpdatedAtEnd(isoString);
        updateFilter('updated_at_end', isoString, true);
    };

    const handlePageChange = (page) => {
        const newSearchParams = new URLSearchParams(searchParams);
        newSearchParams.set('page', page.toString());
        navigate(`/interactions/${interactionId}/sessions?${newSearchParams.toString()}`, { replace: true });
    };

    return {
        statusValues,
        versionValues,
        channelValues,
        lineUserId,
        updatedAtStart,
        updatedAtEnd,
        handleStatusChange,
        handleVersionChange,
        handleChannelChange,
        handleLineUserIdChange,
        handleUpdatedAtStartChange,
        handleUpdatedAtEndChange,
        handlePageChange,
    };
};
