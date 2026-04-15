@extends('layouts.student')

@section('title', 'AI Assistant')
@section('page-title', 'AI Assistant')

@section('content')
    <div class="space-y-5">
        <div class="overflow-hidden">
            <div>
                <section class="flex h-[calc(100vh-5rem)] sm:h-[calc(100vh-6rem)] flex-col">
                    <div class="mx-auto flex w-full max-w-4xl justify-center px-2 pb-2 sm:px-0">
                        <div class="inline-flex rounded-lg border border-slate-200 bg-white p-1 shadow-sm" role="tablist" aria-label="Assistant mode selector">
                            <button
                                id="mode-assistant-button"
                                type="button"
                                data-mode="assistant"
                                class="mode-toggle-button rounded-md px-3 py-1.5 text-xs font-semibold text-slate-700"
                                role="tab"
                                aria-selected="true"
                            >
                                Academic Assistant
                            </button>
                            <button
                                id="mode-guide-button"
                                type="button"
                                data-mode="guide"
                                class="mode-toggle-button rounded-md px-3 py-1.5 text-xs font-semibold text-slate-700"
                                role="tab"
                                aria-selected="false"
                            >
                                Academic Guide
                            </button>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto pb-2">
                        <div id="chat-thread" class="mx-auto flex max-w-4xl flex-col gap-4" data-chat-url="{{ route('student.ai-assistant.chat') }}">
                            <div class="flex items-end gap-3">
                                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-slate-900 text-sm font-semibold text-white">AI</div>
                                <div class="max-w-[82%] rounded-2xl rounded-bl-md bg-white px-4 py-3 text-sm text-slate-700 shadow-sm">
                                    Hello, I’m your student assistant. Ask me about academic status, campus help, or student guidance.
                                    <div class="mt-2 text-[11px] text-slate-400">{{ now()->format('h:i A') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-auto p-2">
                        <div class="mx-auto max-w-4xl rounded-md border border-slate-200 bg-white shadow">
                            <div class="flex items-center gap-2 p-1">
                                <button id="new-chat-button" type="button" class="inline-flex h-10 items-center justify-center rounded-md border border-slate-200 px-3 text-md font-medium text-slate-700 hover:bg-slate-100" title="Start New Chat">
                                    <i class="fa fa-plus"></i>
                                </button>
                                <input
                                    id="chat-message-input"
                                    type="text"
                                    placeholder="Ask the assistant..."
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
@endsection

@push('styles')
    <style>
        body {
            overflow: hidden;
        }

        .mode-toggle-button.active {
            background-color: #0f172a;
            color: #fff;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const messageInput = document.getElementById('chat-message-input');
            const sendButton = document.getElementById('chat-send-button');
            const newChatButton = document.getElementById('new-chat-button');
            const chatThread = document.getElementById('chat-thread');
            const modeButtons = Array.from(document.querySelectorAll('.mode-toggle-button'));

            if (!messageInput || !sendButton || !chatThread) {
                return;
            }

            const modeKey = 'student_ai_assistant_mode';

            let currentMode = localStorage.getItem(modeKey) || 'assistant';
            if (!['assistant', 'guide'].includes(currentMode)) {
                currentMode = 'assistant';
            }

            let sessionId = localStorage.getItem(getSessionKey(currentMode)) || '';
            let isSending = false;

            function getSessionKey(mode) {
                return `student_ai_assistant_session_id_${mode}`;
            }

            function getDraftKey(mode) {
                return `student_ai_assistant_draft_${mode}`;
            }

            function getThreadKey(mode) {
                return `student_ai_assistant_thread_html_${mode}`;
            }

            const escapeHtml = function (value) {
                return (value || '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;');
            };

            const timestamp = function () {
                return new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            };

            const scrollToBottom = function () {
                chatThread.parentElement.scrollTop = chatThread.parentElement.scrollHeight;
            };

            const persistThread = function () {
                sessionStorage.setItem(getThreadKey(currentMode), chatThread.innerHTML || '');
            };

            const persistDraft = function () {
                sessionStorage.setItem(getDraftKey(currentMode), messageInput.value || '');
            };

            const restoreDraft = function () {
                const savedDraft = sessionStorage.getItem(getDraftKey(currentMode));

                if (typeof savedDraft === 'string') {
                    messageInput.value = savedDraft;
                }
            };

            const restoreThread = function () {
                const savedThread = sessionStorage.getItem(getThreadKey(currentMode));

                if (typeof savedThread === 'string' && savedThread.trim() !== '') {
                    chatThread.innerHTML = savedThread;
                    scrollToBottom();
                }
            };

            const getWelcomeMessage = function (mode) {
                if (mode === 'guide') {
                    return 'Hello, I\'m your Academic Guide. Ask education-related questions and I\'ll guide you through resources.';
                }

                return 'Hello, I\'m your student assistant. Ask me about academic status, campus help, or student guidance.';
            };

            const getInputPlaceholder = function (mode) {
                if (mode === 'guide') {
                    return 'Ask your academic guide...';
                }

                return 'Ask the assistant...';
            };

            const renderWelcomeMessage = function (mode) {
                chatThread.innerHTML = '';

                const wrapper = document.createElement('div');
                wrapper.className = 'flex items-end gap-3';
                wrapper.innerHTML = `
                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-slate-900 text-sm font-semibold text-white">AI</div>
                    <div class="max-w-[82%] rounded-2xl rounded-bl-md bg-white px-4 py-3 text-sm text-slate-700 shadow-sm">
                        ${escapeHtml(getWelcomeMessage(mode))}
                        <div class="mt-2 text-[11px] text-slate-400">${timestamp()}</div>
                    </div>
                `;

                chatThread.appendChild(wrapper);
                persistThread();
                scrollToBottom();
            };

            const applyModeUI = function () {
                modeButtons.forEach(function (button) {
                    const isActive = button.getAttribute('data-mode') === currentMode;
                    button.classList.toggle('active', isActive);
                    button.setAttribute('aria-selected', isActive ? 'true' : 'false');
                });

                messageInput.placeholder = getInputPlaceholder(currentMode);
            };

            const switchMode = function (mode) {
                if (isSending || mode === currentMode) {
                    return;
                }

                const hasDraft = messageInput.value.trim().length > 0;
                const hasConversation = chatThread.childElementCount > 1;

                if ((hasDraft || hasConversation) && !window.confirm('Switch mode and start a new chat? Your current visible conversation will be cleared.')) {
                    return;
                }

                currentMode = mode;
                localStorage.setItem(modeKey, currentMode);

                sessionId = '';
                localStorage.removeItem(getSessionKey(currentMode));
                sessionStorage.removeItem(getThreadKey(currentMode));
                sessionStorage.removeItem(getDraftKey(currentMode));

                messageInput.value = '';
                applyModeUI();
                renderWelcomeMessage(currentMode);
                toggleSendButton();
                messageInput.focus();
            };

            const toggleSendButton = function () {
                const hasValue = messageInput.value.trim().length > 0;
                sendButton.classList.toggle('hidden', !hasValue);
            };

            const appendUserMessage = function (text) {
                const wrapper = document.createElement('div');
                wrapper.className = 'flex items-end justify-end gap-3';

                wrapper.innerHTML = `
                    <div class="max-w-[82%] rounded-2xl rounded-br-md bg-slate-900 px-4 py-3 text-sm text-white shadow-sm">
                        ${escapeHtml(text)}
                        <div class="mt-2 text-[11px] text-slate-300">${timestamp()}</div>
                    </div>
                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-slate-700 text-sm font-semibold text-white">Me</div>
                `;

                chatThread.appendChild(wrapper);
                scrollToBottom();
                persistThread();
            };

            const appendAssistantMessage = function (text, html) {
                const wrapper = document.createElement('div');
                wrapper.className = 'flex items-end gap-3';

                const safeText = escapeHtml(text).replace(/\n/g, '<br>');
                const formatted = (typeof html === 'string' && html.trim() !== '') ? html : safeText;

                wrapper.innerHTML = `
                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-slate-900 text-sm font-semibold text-white">AI</div>
                    <div class="max-w-[82%] rounded-2xl rounded-bl-md bg-white px-4 py-3 text-sm text-slate-700 shadow-sm">
                        <div class="prose prose-sm max-w-none prose-slate">${formatted}</div>
                        <div class="mt-2 text-[11px] text-slate-400">${timestamp()}</div>
                    </div>
                `;

                chatThread.appendChild(wrapper);
                scrollToBottom();
                persistThread();
            };

            const appendLoadingMessage = function () {
                const wrapper = document.createElement('div');
                wrapper.className = 'flex items-end gap-3';

                wrapper.innerHTML = `
                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-slate-900 text-sm font-semibold text-white">AI</div>
                    <div class="max-w-[82%] rounded-2xl rounded-bl-md bg-white px-4 py-3 text-sm text-slate-600 shadow-sm">
                        <span class="inline-flex items-center gap-1">
                            <span>Thinking</span>
                            <span class="animate-pulse">...</span>
                        </span>
                    </div>
                `;

                chatThread.appendChild(wrapper);
                scrollToBottom();
                persistThread();

                return wrapper;
            };

            const startNewChat = function () {
                if (isSending) {
                    return;
                }

                const hasMessages = chatThread.childElementCount > 0;
                const hasDraft = messageInput.value.trim().length > 0;

                if ((hasMessages || hasDraft) && !window.confirm('Start a new chat? Current visible conversation will be cleared.')) {
                    return;
                }

                sessionId = '';
                localStorage.removeItem(getSessionKey(currentMode));
                sessionStorage.removeItem(getThreadKey(currentMode));
                sessionStorage.removeItem(getDraftKey(currentMode));
                chatThread.innerHTML = '';
                messageInput.value = '';
                renderWelcomeMessage(currentMode);
                toggleSendButton();
                messageInput.focus();
            };

            const sendMessage = async function () {
                const message = messageInput.value.trim();

                if (!message || isSending) {
                    return;
                }

                isSending = true;
                sendButton.disabled = true;
                newChatButton.disabled = true;

                appendUserMessage(message);
                messageInput.value = '';
                persistDraft();
                toggleSendButton();

                const loadingBubble = appendLoadingMessage();

                try {
                    const response = await fetch(chatThread.dataset.chatUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({
                            message,
                            session_id: sessionId,
                        }),
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.error || 'Failed to send message.');
                    }

                    if (data.session_id) {
                        sessionId = data.session_id;
                        localStorage.setItem(getSessionKey(currentMode), sessionId);
                    }

                    loadingBubble.remove();
                    appendAssistantMessage(data.message || 'No response content returned.', data.message_html || '');
                } catch (error) {
                    loadingBubble.remove();
                    appendAssistantMessage(error.message || 'Unable to connect to the assistant.');
                } finally {
                    isSending = false;
                    sendButton.disabled = false;
                    newChatButton.disabled = false;
                    messageInput.focus();
                }
            };

            restoreDraft();
            restoreThread();
            if (chatThread.childElementCount === 0) {
                renderWelcomeMessage(currentMode);
            }
            applyModeUI();
            toggleSendButton();

            messageInput.addEventListener('input', function () {
                toggleSendButton();
                persistDraft();
            });

            messageInput.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    sendMessage();
                }
            });

            sendButton.addEventListener('click', sendMessage);
            newChatButton.addEventListener('click', startNewChat);
            modeButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    const mode = button.getAttribute('data-mode');
                    if (!mode) {
                        return;
                    }

                    switchMode(mode);
                });
            });
        });
    </script>
@endpush
