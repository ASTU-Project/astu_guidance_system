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
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-auto p-2">
                        <div class="mx-auto max-w-4xl rounded-md border border-slate-200 bg-white shadow">
                            <div class="flex items-end gap-2 p-1">
                                <button id="new-chat-button" type="button" class="inline-flex h-10 items-center justify-center rounded-md border border-slate-200 px-3 text-md font-medium text-slate-700 hover:bg-slate-100" title="Start New Chat">
                                    <i class="fa fa-plus"></i>
                                </button>
                                <textarea
                                    id="chat-message-input"
                                    rows="1"
                                    placeholder="Ask the assistant..."
                                    class="min-h-[44px] max-h-40 flex-1 resize-none overflow-y-auto rounded-md bg-transparent px-3 py-2.5 text-slate-700 outline-none placeholder:text-slate-400"
                                ></textarea>
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.css" crossorigin="anonymous">
    <style>
        body {
            overflow: hidden;
        }

        .mode-toggle-button.active {
            background-color: #0f172a;
            color: #fff;
        }

        #chat-message-input {
            field-sizing: content;
        }

        .prose .katex-display,
        .source-panel .katex-display {
            overflow-x: auto;
            overflow-y: hidden;
            padding: 0.25rem 0;
        }
    </style>
@endpush

@push('scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.js" crossorigin="anonymous"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/contrib/mhchem.min.js" crossorigin="anonymous"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/contrib/auto-render.min.js" crossorigin="anonymous"></script>
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

            function getSessionKey(mode) {
                return `student_ai_assistant_session_id_${mode}`;
            }

            function getDraftKey(mode) {
                return `student_ai_assistant_draft_${mode}`;
            }

            function getThreadKey(mode) {
                return `student_ai_assistant_thread_html_${mode}`;
            }

            function getHistoryKey(mode) {
                return `student_ai_assistant_history_${mode}`;
            }

            let currentMode = localStorage.getItem(modeKey) || 'assistant';
            if (!['assistant', 'guide'].includes(currentMode)) {
                currentMode = 'assistant';
            }

            let sessionId = localStorage.getItem(getSessionKey(currentMode)) || '';
            let isSending = false;
            let conversationHistory = [];

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

            const adjustInputHeight = function () {
                if (!(messageInput instanceof HTMLTextAreaElement)) {
                    return;
                }

                messageInput.style.height = 'auto';
                messageInput.style.height = `${Math.min(messageInput.scrollHeight, 160)}px`;
            };

            const renderMath = function (element) {
                if (typeof renderMathInElement !== 'function' || !element) {
                    return;
                }

                renderMathInElement(element, {
                    delimiters: [
                        { left: '$$', right: '$$', display: true },
                        { left: '\\[', right: '\\]', display: true },
                        { left: '\\begin{equation}', right: '\\end{equation}', display: true },
                        { left: '\\begin{equation*}', right: '\\end{equation*}', display: true },
                        { left: '\\begin{align}', right: '\\end{align}', display: true },
                        { left: '\\begin{align*}', right: '\\end{align*}', display: true },
                        { left: '$', right: '$', display: false },
                        { left: '\\(', right: '\\)', display: false },
                    ],
                    throwOnError: false,
                    errorColor: '#dc2626',
                    processEscapes: true,
                    ignoredTags: ['script', 'noscript', 'style', 'textarea', 'pre', 'code'],
                });
            };

            const persistDraft = function () {
                sessionStorage.setItem(getDraftKey(currentMode), messageInput.value || '');
            };

            const loadHistory = function (mode) {
                try {
                    const raw = sessionStorage.getItem(getHistoryKey(mode));
                    const parsed = raw ? JSON.parse(raw) : [];

                    if (!Array.isArray(parsed)) {
                        return [];
                    }

                    return parsed
                        .filter(function (item) {
                            return item
                                && (item.role === 'user' || item.role === 'assistant')
                                && typeof item.content === 'string'
                                && item.content.trim() !== '';
                        })
                        .slice(-20);
                } catch (_) {
                    return [];
                }
            };

            const persistHistory = function () {
                sessionStorage.setItem(getHistoryKey(currentMode), JSON.stringify(conversationHistory.slice(-20)));
            };

            const restoreDraft = function () {
                const savedDraft = sessionStorage.getItem(getDraftKey(currentMode));

                if (typeof savedDraft === 'string') {
                    messageInput.value = savedDraft;
                    adjustInputHeight();
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
                    </div>
                `;

                chatThread.appendChild(wrapper);
                renderMath(wrapper);
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

            const toggleSendButton = function () {
                const hasValue = messageInput.value.trim().length > 0;
                sendButton.classList.toggle('hidden', !hasValue);
            };

            const appendUserMessage = function (text) {
                const wrapper = document.createElement('div');
                wrapper.className = 'flex items-end justify-end gap-3';
                const safeText = escapeHtml(text).replace(/\n/g, '<br>');

                wrapper.innerHTML = `
                    <div class="max-w-[82%] rounded-2xl rounded-br-md bg-slate-900 px-4 py-3 text-sm text-white shadow-sm">
                        ${safeText}
                    </div>
                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-slate-700 text-sm font-semibold text-white">Me</div>
                `;

                chatThread.appendChild(wrapper);
                renderMath(wrapper);
                scrollToBottom();
                persistThread();

                conversationHistory.push({ role: 'user', content: text });
                persistHistory();
            };

            const splitAssistantContent = function (formattedHtml) {
                if (typeof formattedHtml !== 'string' || formattedHtml.trim() === '') {
                    return {
                        mainHtml: '',
                        sourcesHtml: '',
                    };
                }

                const container = document.createElement('div');
                container.innerHTML = formattedHtml;

                const blocks = Array.from(container.children);
                const sourceHeadingIndex = blocks.findIndex(function (node) {
                    return /^H[1-6]$/.test(node.tagName) && node.textContent.trim().toLowerCase() === 'sources';
                });

                if (sourceHeadingIndex === -1) {
                    return {
                        mainHtml: formattedHtml,
                        sourcesHtml: '',
                    };
                }

                const mainHtml = blocks
                    .slice(0, sourceHeadingIndex)
                    .map(function (node) {
                        return node.outerHTML;
                    })
                    .join('')
                    .trim();

                const sourcesHtml = blocks
                    .slice(sourceHeadingIndex + 1)
                    .map(function (node) {
                        return node.outerHTML;
                    })
                    .join('')
                    .trim();

                return {
                    mainHtml: mainHtml || formattedHtml,
                    sourcesHtml,
                };
            };

            const appendAssistantMessage = function (text, html) {
                const wrapper = document.createElement('div');
                wrapper.className = 'flex items-end gap-3';

                const safeText = escapeHtml(text).replace(/\n/g, '<br>');
                const formatted = (typeof html === 'string' && html.trim() !== '') ? html : safeText;
                const contentParts = splitAssistantContent(formatted);
                const sourcesPanel = contentParts.sourcesHtml
                    ? `
                        <div class="mt-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                            <div class="mb-1 text-[11px] font-semibold uppercase tracking-wide text-slate-500">Sources</div>
                            <div class="prose prose-sm max-w-none prose-slate source-panel">${contentParts.sourcesHtml}</div>
                        </div>
                    `
                    : '';

                wrapper.innerHTML = `
                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-slate-900 text-sm font-semibold text-white">AI</div>
                    <div class="max-w-[82%] rounded-2xl rounded-bl-md bg-white px-4 py-3 text-sm text-slate-700 shadow-sm">
                        <div class="prose prose-sm max-w-none prose-slate">${contentParts.mainHtml}</div>
                        ${sourcesPanel}
                    </div>
                `;

                chatThread.appendChild(wrapper);
                renderMath(wrapper);
                scrollToBottom();
                persistThread();

                conversationHistory.push({ role: 'assistant', content: text || '' });
                persistHistory();
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
                sessionStorage.removeItem(getHistoryKey(currentMode));

                conversationHistory = [];
                messageInput.value = '';
                adjustInputHeight();
                applyModeUI();
                renderWelcomeMessage(currentMode);
                toggleSendButton();
                messageInput.focus();
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
                sessionStorage.removeItem(getHistoryKey(currentMode));

                conversationHistory = [];
                chatThread.innerHTML = '';
                messageInput.value = '';
                adjustInputHeight();
                renderWelcomeMessage(currentMode);
                toggleSendButton();
                messageInput.focus();
            };

            const sendMessage = async function () {
                const message = messageInput.value.trim();

                if (!message || isSending) {
                    return;
                }

                const historyForRequest = conversationHistory.slice(-12);

                isSending = true;
                sendButton.disabled = true;
                newChatButton.disabled = true;

                appendUserMessage(message);
                messageInput.value = '';
                adjustInputHeight();
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
                            mode: currentMode,
                            history: historyForRequest,
                            top_k: 5,
                            stream: false,
                        }),
                    });

                    let data = {};
                    try {
                        data = await response.json();
                    } catch (_) {
                        data = {};
                    }

                    if (!response.ok) {
                        throw new Error(data.error || data.message || `Request failed (${response.status}).`);
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
            conversationHistory = loadHistory(currentMode);

            if (chatThread.childElementCount === 0) {
                renderWelcomeMessage(currentMode);
            }

            applyModeUI();
            adjustInputHeight();
            toggleSendButton();

            messageInput.addEventListener('input', function () {
                adjustInputHeight();
                toggleSendButton();
                persistDraft();
            });

            messageInput.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' && !event.shiftKey) {
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
