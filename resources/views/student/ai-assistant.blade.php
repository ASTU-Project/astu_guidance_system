@extends('layouts.student')

@section('title', 'AI Assistant')
@section('page-title', 'AI Assistant')

@section('content')
    <div class="space-y-5">
        <div class="overflow-hidden">
            <div>
                <section class="flex h-[calc(100vh-5rem)] sm:h-[calc(100vh-6rem)] flex-col">
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
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const messageInput = document.getElementById('chat-message-input');
            const sendButton = document.getElementById('chat-send-button');
            const newChatButton = document.getElementById('new-chat-button');
            const chatThread = document.getElementById('chat-thread');

            if (!messageInput || !sendButton || !chatThread) {
                return;
            }

            const sessionKey = 'student_ai_assistant_session_id';
            const draftKey = 'student_ai_assistant_draft';
            const threadKey = 'student_ai_assistant_thread_html';

            let sessionId = localStorage.getItem(sessionKey) || '';
            let isSending = false;

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
                sessionStorage.setItem(threadKey, chatThread.innerHTML || '');
            };

            const persistDraft = function () {
                sessionStorage.setItem(draftKey, messageInput.value || '');
            };

            const restoreDraft = function () {
                const savedDraft = sessionStorage.getItem(draftKey);

                if (typeof savedDraft === 'string') {
                    messageInput.value = savedDraft;
                }
            };

            const restoreThread = function () {
                const savedThread = sessionStorage.getItem(threadKey);

                if (typeof savedThread === 'string' && savedThread.trim() !== '') {
                    chatThread.innerHTML = savedThread;
                    scrollToBottom();
                }
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
                localStorage.removeItem(sessionKey);
                sessionStorage.removeItem(threadKey);
                sessionStorage.removeItem(draftKey);
                chatThread.innerHTML = '';
                messageInput.value = '';
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
                        localStorage.setItem(sessionKey, sessionId);
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
        });
    </script>
@endpush
