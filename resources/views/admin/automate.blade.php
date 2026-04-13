@extends('layouts.admin')

@section('title', 'Automation')
@section('page-title', 'Automate')

@section('content')
    <div class="space-y-5">
        <div class="overflow-hidden">
            <div>

                <section class="flex h-[calc(100vh-5rem)] sm:h-[calc(100vh-6rem)] flex-col bg-slate-50">

                    <div class="flex-1 overflow-y-auto px-4 py-5 sm:px-6">
                        <div id="chat-thread" class="mx-auto flex max-w-4xl flex-col gap-4" data-chat-url="{{ route('admin.automate.chat') }}">


                        </div>
                    </div>

                    <div class="mt-auto p-2">
                        <div class="mx-auto max-w-4xl rounded-md border border-slate-200 bg-white shadow">
                            <div class="flex items-center gap-2 p-1">
                                <input
                                    id="chat-message-input"
                                    type="text"
                                    placeholder="AI Automation..."
                                    class="h-11 flex-1 px-3 rounded-md bg-transparent text-slate-700 outline-none placeholder:text-slate-400"
                                >
                                <button id="chat-send-button" type="button" class="hidden inline-flex h-10 w-10 items-center justify-center rounded-md bg-slate-900 text-white hover:bg-slate-800" title="Send">
                                    <i class="fa fa-arrow-up"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <div id="tools-modal" class="hidden fixed inset-0 z-50 items-center justify-center px-4" data-settings-url="{{ route('admin.automation-settings.show') }}" data-settings-save-url="{{ route('admin.automation-settings.update') }}">
        <div class="absolute inset-0 bg-slate-950/60" onclick="closeToolsModal()"></div>

        <div class="relative w-full max-w-4xl rounded-md bg-white shadow-2xl overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-950">Tools Settings</h3>
                </div>
                <button type="button" onclick="closeToolsModal()" class="text-slate-400 hover:text-slate-700">
                    <i class="fa fa-times"></i>
                </button>
            </div>

            <div class="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">
                <div class="space-y-4">
                    <div class="rounded-xl p-4">
                        <h4 class="mb-3 text-sm font-semibold text-slate-900">Safety</h4>
                        <label class="flex items-center justify-between rounded-lg border p-3 text-sm text-slate-700">
                            <span>Enable write tools</span>
                            <input id="enable-write-tools" type="checkbox" class="h-4 w-4 rounded border-slate-300">
                        </label>
                        <label class="mt-3 flex items-center justify-between rounded-lg border p-3 text-sm text-slate-700">
                            <span>Confirm destructive actions</span>
                            <input id="confirm-destructive-actions" type="checkbox" checked class="h-4 w-4 rounded border-slate-300">
                        </label>
                    </div>

                    <div class="rounded-xl p-4">
                        <h4 class="mb-3 text-sm font-semibold text-slate-900">History</h4>
                        <div id="chat-history-list" class="space-y-2">
                            <div class="rounded-lg border p-3 text-xs text-slate-500">No chat history yet.</div>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="rounded-xl p-4">
                        <h4 class="mb-3 text-sm font-semibold text-slate-900">Tool Groups</h4>

                        <div class="space-y-3 text-sm text-slate-700">
                            <label class="flex items-center justify-between rounded-lg border p-3">
                                <span>Student Tools</span>
                                <input type="checkbox" data-tool-group="students" checked class="h-4 w-4 rounded border-slate-300">
                            </label>
                            <label class="flex items-center justify-between rounded-lg border p-3">
                                <span>Department Tools</span>
                                <input type="checkbox" data-tool-group="departments" checked class="h-4 w-4 rounded border-slate-300">
                            </label>
                            <label class="flex items-center justify-between rounded-lg border p-3">
                                <span>Calendar Tools</span>
                                <input type="checkbox" data-tool-group="calendar" checked class="h-4 w-4 rounded border-slate-300">
                            </label>
                            <label class="flex items-center justify-between rounded-lg border p-3">
                                <span>Policy Tools</span>
                                <input type="checkbox" data-tool-group="policies" checked class="h-4 w-4 rounded border-slate-300">
                            </label>
                        </div>
                    </div>

                    <div class="rounded-xl p-4">
                        <h4 class="mb-3 text-sm font-semibold text-slate-900">System Prompt</h4>
                        <textarea id="system-prompt" rows="7" class="w-full rounded-md border p-3 text-sm outline-none">Act as an academic administration assistant. Stay concise and only use enabled demo tools.</textarea>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 px-6 py-4">
                <button type="button" onclick="closeToolsModal()" class="rounded-md border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                    Cancel
                </button>
                <button id="save-settings-button" type="button" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                    Save Settings
                </button>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        body {
            overflow: hidden;
        }
    </style>
@endpush

@push('scripts')
    <script>
        function openToolsModal() {
            const modal = document.getElementById('tools-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            if (typeof window.refreshAutomationSettings === 'function') {
                window.refreshAutomationSettings();
            }
        }

        function closeToolsModal() {
            const modal = document.getElementById('tools-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeToolsModal();
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            const messageInput = document.getElementById('chat-message-input');
            const sendButton = document.getElementById('chat-send-button');
            const chatThread = document.getElementById('chat-thread');
            const toolsModal = document.getElementById('tools-modal');
            const saveSettingsButton = document.getElementById('save-settings-button');
            const enableWriteTools = document.getElementById('enable-write-tools');
            const confirmDestructiveActions = document.getElementById('confirm-destructive-actions');
            const systemPrompt = document.getElementById('system-prompt');
            const chatHistoryList = document.getElementById('chat-history-list');

            if (!messageInput || !sendButton || !chatThread) {
                return;
            }

            let sessionId = localStorage.getItem('automate_chat_session_id') || '';
            let isSending = false;

            const toggleSendButton = function () {
                const hasValue = messageInput.value.trim().length > 0;
                sendButton.classList.toggle('hidden', !hasValue);
            };

            const scrollToBottom = function () {
                chatThread.parentElement.scrollTop = chatThread.parentElement.scrollHeight;
            };

            const timestamp = function () {
                return new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            };

            const appendUserMessage = function (text) {
                const wrapper = document.createElement('div');
                wrapper.className = 'flex items-end justify-end gap-3';

                wrapper.innerHTML = `
                    <div class="max-w-[82%] rounded-2xl rounded-br-md bg-slate-900 px-4 py-3 text-sm text-white">
                        ${text.replace(/</g, '&lt;').replace(/>/g, '&gt;')}
                        <div class="mt-2 text-[11px] text-slate-300">${timestamp()}</div>
                    </div>
                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-slate-700 text-sm font-semibold text-white">B</div>
                `;

                chatThread.appendChild(wrapper);
                scrollToBottom();
            };

            const appendAssistantMessage = function (text, html) {
                const wrapper = document.createElement('div');
                wrapper.className = 'flex items-end gap-3';

                const safeText = (text || '')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/\n/g, '<br>');

                const formattedContent = (typeof html === 'string' && html.trim() !== '') ? html : safeText;

                wrapper.innerHTML = `
                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-slate-900 text-sm font-semibold text-white">AI</div>
                    <div class="max-w-[82%] rounded-2xl rounded-bl-md bg-white px-4 py-3 text-sm text-slate-700">
                        <div class="prose prose-sm max-w-none prose-slate">${formattedContent}</div>
                        <div class="mt-2 text-[11px] text-slate-400">${timestamp()}</div>
                    </div>
                `;

                chatThread.appendChild(wrapper);
                scrollToBottom();
            };

            const getSelectedToolGroups = function () {
                return Array.from(document.querySelectorAll('[data-tool-group]'))
                    .filter(function (checkbox) {
                        return checkbox.checked;
                    })
                    .map(function (checkbox) {
                        return checkbox.getAttribute('data-tool-group');
                    })
                    .filter(Boolean);
            };

            const setSelectedToolGroups = function (groups) {
                const selectedGroups = Array.isArray(groups) ? groups : [];

                document.querySelectorAll('[data-tool-group]').forEach(function (checkbox) {
                    checkbox.checked = selectedGroups.includes(checkbox.getAttribute('data-tool-group'));
                });
            };

            const formatHistoryTime = function (dateString) {
                if (!dateString) {
                    return 'Unknown time';
                }

                const date = new Date(dateString.replace(' ', 'T'));

                if (Number.isNaN(date.getTime())) {
                    return dateString;
                }

                return date.toLocaleString([], {
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                });
            };

            const renderHistory = function (historyItems) {
                if (!chatHistoryList) {
                    return;
                }

                chatHistoryList.innerHTML = '';

                if (!Array.isArray(historyItems) || historyItems.length === 0) {
                    chatHistoryList.innerHTML = '<div class="rounded-lg border p-3 text-xs text-slate-500">No chat history yet.</div>';
                    return;
                }

                historyItems.slice(0, 5).forEach(function (item) {
                    const role = item.role === 'assistant' ? 'Assistant' : 'User';
                    const content = (item.content || '').toString();
                    const preview = content.length > 90 ? `${content.slice(0, 90)}...` : content;

                    const row = document.createElement('div');
                    row.className = 'rounded-lg border p-3';
                    row.innerHTML = `
                        <p class="text-xs font-medium text-slate-800">${role}: ${preview.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</p>
                        <p class="text-[11px] text-slate-500 mt-1">${formatHistoryTime(item.created_at)}</p>
                    `;

                    chatHistoryList.appendChild(row);
                });
            };

            const loadAutomationSettings = async function () {
                if (!toolsModal?.dataset.settingsUrl) {
                    return;
                }

                try {
                    const response = await fetch(toolsModal.dataset.settingsUrl, {
                        headers: {
                            'Accept': 'application/json',
                        },
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.error || 'Failed to load automation settings.');
                    }

                    const settings = data.settings || {};
                    enableWriteTools.checked = Boolean(settings.enable_write_tools);
                    confirmDestructiveActions.checked = Boolean(settings.confirm_destructive_actions ?? true);
                    setSelectedToolGroups(settings.enabled_tool_groups || []);
                    systemPrompt.value = settings.system_prompt || '';
                    renderHistory(data.history || []);
                } catch (error) {
                    console.error(error);
                }
            };

            const saveAutomationSettings = async function () {
                if (!toolsModal?.dataset.settingsSaveUrl) {
                    return;
                }

                saveSettingsButton.disabled = true;
                saveSettingsButton.textContent = 'Saving...';

                try {
                    const response = await fetch(toolsModal.dataset.settingsSaveUrl, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            enable_write_tools: enableWriteTools.checked,
                            confirm_destructive_actions: confirmDestructiveActions.checked,
                            enabled_tool_groups: getSelectedToolGroups(),
                            system_prompt: systemPrompt.value,
                        }),
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.error || 'Failed to save automation settings.');
                    }

                    closeToolsModal();
                } catch (error) {
                    appendAssistantMessage(error.message || 'Unable to save automation settings.');
                } finally {
                    saveSettingsButton.disabled = false;
                    saveSettingsButton.textContent = 'Save Settings';
                }
            };

            loadAutomationSettings();

            const sendMessage = async function () {
                const text = messageInput.value.trim();

                if (!text || isSending) {
                    return;
                }

                isSending = true;
                sendButton.disabled = true;

                appendUserMessage(text);
                messageInput.value = '';
                toggleSendButton();

                try {
                    const response = await fetch(chatThread.dataset.chatUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            message: text,
                            session_id: sessionId,
                        }),
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        const details = typeof data.details === 'string'
                            ? data.details
                            : JSON.stringify(data.details || {});

                        throw new Error([data.error || 'Chat request failed.', details]
                            .filter(Boolean)
                            .join('\n'));
                    }

                    if (data.session_id) {
                        sessionId = data.session_id;
                        localStorage.setItem('automate_chat_session_id', sessionId);
                    }

                    appendAssistantMessage(data.message || 'No response content returned.', data.message_html || '');
                } catch (error) {
                    appendAssistantMessage(error.message || 'Unable to connect to assistant.');
                } finally {
                    isSending = false;
                    sendButton.disabled = false;
                }
            };

            toggleSendButton();
            messageInput.addEventListener('input', toggleSendButton);
            sendButton.addEventListener('click', sendMessage);
            messageInput.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    sendMessage();
                }
            });

            saveSettingsButton?.addEventListener('click', saveAutomationSettings);

            window.refreshAutomationSettings = loadAutomationSettings;
        });
    </script>
@endpush
