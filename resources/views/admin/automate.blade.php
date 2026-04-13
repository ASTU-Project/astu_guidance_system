@extends('layouts.admin')

@section('title', 'Automation')
@section('page-title', 'Automate')

@section('content')
    <div class="space-y-5">
        <div class="overflow-hidden">
            <div>

                <section class="flex h-[calc(100vh-5rem)] sm:h-[calc(100vh-6rem)] flex-col bg-slate-50">

                    <div class="flex-1 overflow-y-auto px-4 py-5 sm:px-6">
                        <div class="mx-auto flex max-w-4xl flex-col gap-4">
                            <div class="flex items-end gap-3">
                                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-slate-900 text-sm font-semibold text-white">AI</div>
                                <div class="max-w-[82%] rounded-2xl rounded-bl-md bg-white px-4 py-3 text-sm text-slate-700">
                                    I can help you manage policies, departments, students, calendar data, and MCP tools.
                                    <div class="mt-2 text-[11px] text-slate-400">Today, 10:48 AM</div>
                                </div>
                            </div>

                            <div class="flex items-end justify-end gap-3">
                                <div class="max-w-[82%] rounded-2xl rounded-br-md bg-slate-900 px-4 py-3 text-sm text-white">
                                    Show me the available MCP tools and current endpoint.
                                    <div class="mt-2 text-[11px] text-slate-300">Today, 10:49 AM</div>
                                </div>
                                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-slate-700 text-sm font-semibold text-white">B</div>
                            </div>

                            <div class="flex items-end gap-3">
                                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-slate-900 text-sm font-semibold text-white">AI</div>
                                <div class="max-w-[82%] rounded-2xl rounded-bl-md bg-white px-4 py-3 text-sm text-slate-700">
                                    <small>
                                        <b>Used Tools:</b>
                                        <span class="bg-slate-900 text-white p-1 px-3 font-bold rounded-xl">DepartmentList</span> 
                                        <span class="bg-slate-900 text-white p-1 px-3 font-bold rounded-xl">ListPoliciesTool</span>
                                    </small>
                                    <p  class="mt-2">Demo tools are ready. Open the tools popup to edit endpoint settings and permissions.</p>
                                    <div class="mt-2 text-[11px] text-slate-400">Today, 10:49 AM</div>
                                </div>
                            </div>
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

    <div id="tools-modal" class="hidden fixed inset-0 z-50 items-center justify-center px-4">
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
                            <input type="checkbox" class="h-4 w-4 rounded border-slate-300">
                        </label>
                        <label class="mt-3 flex items-center justify-between rounded-lg border p-3 text-sm text-slate-700">
                            <span>Confirm destructive actions</span>
                            <input type="checkbox" checked class="h-4 w-4 rounded border-slate-300">
                        </label>
                    </div>

                    <div class="rounded-xl p-4">
                        <h4 class="mb-3 text-sm font-semibold text-slate-900">History</h4>
                        <div class="space-y-2">
                            <div class="rounded-lg border p-3">
                                <p class="text-xs font-medium text-slate-800">Used tool: GPA Summary Calculator</p>
                                <p class="text-[11px] text-slate-500">Today, 10:48 AM</p>
                            </div>
                            <div class="rounded-lg border p-3">
                                <p class="text-xs font-medium text-slate-800">Used tool: Add Department</p>
                                <p class="text-[11px] text-slate-500">Today, 10:46 AM</p>
                            </div>
                            <div class="rounded-lg border p-3">
                                <p class="text-xs font-medium text-slate-800">Used tool: List Departments</p>
                                <p class="text-[11px] text-slate-500">Today, 10:43 AM</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="rounded-xl p-4">
                        <h4 class="mb-3 text-sm font-semibold text-slate-900">Tool Groups</h4>

                        <div class="space-y-3 text-sm text-slate-700">
                            <label class="flex items-center justify-between rounded-lg border p-3">
                                <span>Student Tools</span>
                                <input type="checkbox" checked class="h-4 w-4 rounded border-slate-300">
                            </label>
                            <label class="flex items-center justify-between rounded-lg border p-3">
                                <span>Department Tools</span>
                                <input type="checkbox" checked class="h-4 w-4 rounded border-slate-300">
                            </label>
                            <label class="flex items-center justify-between rounded-lg border p-3">
                                <span>Calendar Tools</span>
                                <input type="checkbox" checked class="h-4 w-4 rounded border-slate-300">
                            </label>
                            <label class="flex items-center justify-between rounded-lg border p-3">
                                <span>Policy Tools</span>
                                <input type="checkbox" checked class="h-4 w-4 rounded border-slate-300">
                            </label>
                        </div>
                    </div>

                    <div class="rounded-xl p-4">
                        <h4 class="mb-3 text-sm font-semibold text-slate-900">System Prompt</h4>
                        <textarea rows="7" class="w-full rounded-md border p-3 text-sm outline-none">Act as an academic administration assistant. Stay concise and only use enabled demo tools.</textarea>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 px-6 py-4">
                <button type="button" onclick="closeToolsModal()" class="rounded-md border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                    Cancel
                </button>
                <button type="button" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
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

            if (!messageInput || !sendButton) {
                return;
            }

            const toggleSendButton = function () {
                const hasValue = messageInput.value.trim().length > 0;
                sendButton.classList.toggle('hidden', !hasValue);
            };

            toggleSendButton();
            messageInput.addEventListener('input', toggleSendButton);
        });
    </script>
@endpush
