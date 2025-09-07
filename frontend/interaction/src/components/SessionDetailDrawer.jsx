import React, { useState, useEffect } from 'react';
import { useParams } from 'react-router-dom';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetDescription,
    SheetFooter,
} from './ui/sheet';
import { Button } from './ui/button'; // 必要に応じてButtonコンポーネントをインポート
const __ = wp.i18n.__;

// wp_localize_script により window.lineConnectConfig が利用可能である前提
const lineConnectConfig = window.lineConnectConfig || {};

const STATUS_OPTIONS = [
    { value: "active", label: __("Active", "lineconnect") },
    { value: "paused", label: __("Paused", "lineconnect") },
    { value: "completed", label: __("Completed", "lineconnect") },
    { value: "timeout", label: __("Timeout", "lineconnect") },
];

const SessionDetailDrawer = ({ session, isOpen, onClose, onEdit, onDelete }) => {
    const { sessionId } = useParams();

    // URLにsessionIdがある場合、またはisOpenがtrueの場合に開く
    const shouldOpen = sessionId || isOpen;

    const [isEditing, setIsEditing] = useState(false);
    const [formAnswers, setFormAnswers] = useState({});
    const [formStatus, setFormStatus] = useState(session ? session.status : '');
    const [isSaving, setIsSaving] = useState(false);
    const [errorMessage, setErrorMessage] = useState(null);
    const [successMessage, setSuccessMessage] = useState(null);

    // セッションが切り替わったらフォームをリセット
    useEffect(() => {
        if (session) {
            const initial = {};
            if (session.answers) {
                Object.entries(session.answers).forEach(([stepId, answerData]) => {
                    initial[stepId] = answerData && answerData.answer ? answerData.answer : '';
                });
            }
            setFormAnswers(initial);
            setFormStatus(session.status || '');
            setIsEditing(false);
            setErrorMessage(null);
            setSuccessMessage(null);
        }
    }, [session]);

    if (!session) {
        return null;
    }

    const handleStartEdit = () => {
        setIsEditing(true);
        setErrorMessage(null);
        setSuccessMessage(null);
    };

    const handleCancelEdit = () => {
        // フォームをセッションデータに戻す
        const initial = {};
        if (session.answers) {
            Object.entries(session.answers).forEach(([stepId, answerData]) => {
                initial[stepId] = answerData && answerData.answer ? answerData.answer : '';
            });
        }
        setFormAnswers(initial);
        setFormStatus(session.status || '');
        setIsEditing(false);
        setErrorMessage(null);
    };

    const handleChangeAnswer = (stepId, value) => {
        setFormAnswers(prev => ({ ...prev, [stepId]: value }));
    };

    const handleSave = async () => {
        setErrorMessage(null);
        setIsSaving(true);

        try {
            const body = {
                answers: formAnswers,
                status: formStatus,
            };

            const apiUrl = `${lineConnectConfig.rest_url}lineconnect/interactions/${session.interaction_id}/sessions/${session.id}`;
            const resp = await fetch(apiUrl, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': lineConnectConfig.rest_nonce,
                },
                credentials: 'same-origin',
                body: JSON.stringify(body),
            });

            if (!resp.ok) {
                const err = await resp.json().catch(() => ({}));
                throw new Error(err.message || `HTTP error! status: ${resp.status}`);
            }

            const data = await resp.json();
            const updatedSession = data.data;

            // 成功時の UX: 編集モード解除して詳細表示に戻す（drawer は閉じない）
            setIsEditing(false);
            setSuccessMessage(__('Saved successfully', 'lineconnect'));
            // 親に更新済セッションを渡して一覧に反映してもらう
            if (typeof onEdit === 'function') {
                onEdit(updatedSession);
            }
            // 成功メッセージは短時間表示
            setTimeout(() => setSuccessMessage(null), 3000);
        } catch (e) {
            setErrorMessage(e.message || __('Failed to save', 'lineconnect'));
        } finally {
            setIsSaving(false);
        }
    };

    return (
        <Sheet open={shouldOpen} onOpenChange={onClose}>
            <SheetContent side="right" className="w-96 flex flex-col">
                <SheetHeader className="px-4 py-4 border-b">
                    <SheetTitle>{__('Session Details', 'lineconnect')}</SheetTitle>
                    <SheetDescription className="text-sm text-gray-500">
                        {__('Session ID: ', 'lineconnect')}{session.id}
                    </SheetDescription>
                </SheetHeader>

                <div className="flex-1 overflow-y-auto max-h-[calc(100vh-140px)] px-4 py-4">
                    <div className="flex items-center justify-between mb-3">
                        <h3 className="text-lg font-semibold">{__('Answer List', 'lineconnect')}</h3>
                    </div>

                    {session.answers && Object.keys(session.answers).length > 0 ? (
                        <div className="space-y-3">
                            {Object.entries(session.answers).map(([stepId, answerData]) => (
                                <div key={stepId} className="p-3 border rounded-md bg-gray-50">
                                    <strong className="text-sm font-medium">{answerData.title}</strong>
                                    {!isEditing ? (
                                        <div className="mt-1 text-sm whitespace-pre-line text-gray-800">
                                            {answerData.answer}
                                        </div>
                                    ) : (
                                        <textarea
                                            value={formAnswers[stepId] ?? ''}
                                            onChange={(e) => handleChangeAnswer(stepId, e.target.value)}
                                            className="mt-2 w-full p-2 border rounded text-sm"
                                            rows={4}
                                        />
                                    )}
                                </div>
                            ))}
                        </div>
                    ) : (
                        <p className="text-gray-500 text-sm">{__('No answer data available.', 'lineconnect')}</p>
                    )}

                    <div className="mt-6">
                        <h4 className="text-sm font-medium text-gray-700 mb-2">{__('Session Information', 'lineconnect')}</h4>
                        <div className="text-sm text-gray-600 space-y-2">
                            <p><span className="font-medium">{__('Version: ', 'lineconnect')}</span>{session.interaction_version}</p>
                            <p><span className="font-medium">{__('LINE User: ', 'lineconnect')}</span>{session.displayName || session.line_user_id}</p>

                            <div>
                                <span className="font-medium">{__('Status: ', 'lineconnect')}</span>
                                {!isEditing ? (
                                    <span className="ml-1">{STATUS_OPTIONS.find(option => option.value === session.status)?.label || session.status}</span>
                                ) : (
                                    <select
                                        value={formStatus}
                                        onChange={(e) => setFormStatus(e.target.value)}
                                        className="ml-1 border px-2 py-1 rounded text-sm"
                                    >
                                        {STATUS_OPTIONS.map(opt => (
                                            <option key={opt.value} value={opt.value}>{opt.label}</option>
                                        ))}
                                    </select>
                                )}
                            </div>

                            {session.status === 'active' && (
                                <p><span className="font-medium">{__('Current Step: ', 'lineconnect')}</span>{session.current_step_id}</p>
                            )}
                            <p><span className="font-medium">{__('Created At: ', 'lineconnect')}</span>{new Date(session.created_at).toLocaleString('ja-JP')}</p>
                            <p><span className="font-medium">{__('Updated At: ', 'lineconnect')}</span>{new Date(session.updated_at).toLocaleString('ja-JP')}</p>
                        </div>
                    </div>

                    {errorMessage && (
                        <div className="mt-4 text-sm text-red-600">
                            {errorMessage}
                        </div>
                    )}
                    {successMessage && (
                        <div className="mt-4 text-sm text-green-600">
                            {successMessage}
                        </div>
                    )}
                </div>

                <SheetFooter className="px-4 py-4 border-t bg-gray-50 flex justify-end gap-2">
                    {!isEditing ? (
                        <>
                            <Button variant="outline" size="sm" onClick={handleStartEdit} disabled={isSaving}>
                                {__('Edit', 'lineconnect')}
                            </Button>
                            <Button variant="destructive" size="sm" onClick={() => onDelete(session.id)} disabled={isSaving}>
                                {__('Delete', 'lineconnect')}
                            </Button>
                        </>
                    ) : (
                        <>
                            <Button variant="outline" size="sm" onClick={handleCancelEdit} disabled={isSaving}>
                                {__('Cancel', 'lineconnect')}
                            </Button>
                            <Button variant="default" size="sm" onClick={handleSave} disabled={isSaving}>
                                {isSaving ? __('Saving...', 'lineconnect') : __('Save', 'lineconnect')}
                            </Button>
                        </>
                    )}
                </SheetFooter>
            </SheetContent>
        </Sheet>
    );
};

export default SessionDetailDrawer;
