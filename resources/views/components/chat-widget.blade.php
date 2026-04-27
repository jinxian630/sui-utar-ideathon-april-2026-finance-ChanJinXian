<div
    x-data="{
        open: false,
        messages: [],
        input: '',
        loading: false,
        quickSend(text) {
            this.input = text;
            this.send();
        },
        scrollToBottom() {
            this.$nextTick(() => {
                const el = this.$refs.msgContainer;
                if (el) el.scrollTop = el.scrollHeight;
            });
        },
        async send() {
            if (!this.input.trim() || this.loading) return;

            const userMsg = this.input.trim();
            this.messages.push({ role: 'user', text: userMsg });
            this.input = '';
            this.loading = true;
            this.scrollToBottom();

            try {
                const response = await fetch('{{ route('api.chat.store', [], false) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({ message: userMsg })
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok || data.success === false) {
                    throw new Error(data.message || 'The AI assistant is unavailable right now.');
                }

                this.messages.push({
                    role: 'assistant',
                    text: data.reply || 'I could not generate a response. Please try again.'
                });
            } catch (error) {
                this.messages.push({
                    role: 'assistant',
                    text: error.message || 'The AI assistant is unavailable right now.'
                });
            } finally {
                this.loading = false;
                this.scrollToBottom();
            }
        }
    }"
    @open-chat.window="open = true"
    class="fixed bottom-6 right-6 z-50 flex flex-col items-end gap-4"
>
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 scale-95"
        class="w-[min(92vw,520px)] bg-[#10101D] rounded-2xl border border-cyan-400/25 shadow-2xl shadow-cyan-500/15 flex flex-col overflow-hidden"
        style="height: min(74vh, 640px);"
    >
        <div class="flex items-center justify-between px-5 py-4 border-b border-cyan-400/15 bg-[#0B0F1A]">
            <div class="flex items-center gap-3">
                <div class="cyber-ai-icon cyber-ai-icon-sm">
                    <span>AI</span>
                </div>
                <div>
                    <h4 class="text-lg font-semibold text-white">AI Financial Assistant</h4>
                    <p class="text-xs text-cyan-300 flex items-center gap-1 mt-0.5">
                        <span class="w-2 h-2 rounded-full bg-cyan-300 animate-pulse inline-block"></span> Live
                    </p>
                </div>
            </div>
            <button @click="open = false" class="text-gray-400 hover:text-white transition text-base w-9 h-9 flex items-center justify-center rounded-lg hover:bg-white/5" aria-label="Close chat">x</button>
        </div>

        <div class="flex-1 overflow-y-auto p-5 flex flex-col gap-4" x-ref="msgContainer">
            <div x-show="messages.length === 0" class="self-start max-w-[85%]">
                <div class="bg-white/5 border border-cyan-400/10 text-gray-100 p-4 rounded-2xl rounded-tl-sm text-base leading-relaxed">
                    Hi {{ explode(' ', auth()->user()->name ?? 'there')[0] }}. Ask me about your savings, badges, goals, or Sui sync status.
                </div>
            </div>

            <template x-for="(msg, i) in messages" :key="i">
                <div :class="msg.role === 'user' ? 'self-end' : 'self-start'" class="max-w-[85%]">
                    <div :class="msg.role === 'user'
                                ? 'bg-fuchsia-500/25 border border-fuchsia-400/25 text-white rounded-2xl rounded-tr-sm'
                                : 'bg-cyan-400/10 border border-cyan-400/15 text-gray-100 rounded-2xl rounded-tl-sm'"
                         class="p-4 text-base leading-relaxed whitespace-pre-line">
                        <span x-text="msg.text"></span>
                    </div>
                </div>
            </template>

            <div x-show="loading" class="self-start flex gap-1.5 px-4 py-3 bg-cyan-400/10 rounded-xl rounded-tl-sm">
                <span class="w-2 h-2 bg-cyan-300 rounded-full animate-bounce" style="animation-delay:0ms"></span>
                <span class="w-2 h-2 bg-cyan-300 rounded-full animate-bounce" style="animation-delay:150ms"></span>
                <span class="w-2 h-2 bg-cyan-300 rounded-full animate-bounce" style="animation-delay:300ms"></span>
            </div>
        </div>

        <div class="px-4 py-3 flex gap-2 overflow-x-auto border-t border-cyan-400/10 bg-[#0B0F1A]">
            <button @click="quickSend('Analyze my saving habits')"
                    class="text-sm whitespace-nowrap bg-white/5 hover:bg-cyan-500/15 border border-white/10 hover:border-cyan-400/35 text-gray-300 hover:text-white px-4 py-2 rounded-full transition">
                Analyze habits
            </button>
            <button @click="quickSend('When will I reach Diamond Saver?')"
                    class="text-sm whitespace-nowrap bg-white/5 hover:bg-cyan-500/15 border border-white/10 hover:border-cyan-400/35 text-gray-300 hover:text-white px-4 py-2 rounded-full transition">
                Diamond Saver
            </button>
            <button @click="quickSend('How can I reach RM 5,000 faster?')"
                    class="text-sm whitespace-nowrap bg-white/5 hover:bg-cyan-500/15 border border-white/10 hover:border-cyan-400/35 text-gray-300 hover:text-white px-4 py-2 rounded-full transition">
                RM 5,000 plan
            </button>
        </div>

        <div class="flex items-center gap-3 px-5 py-4 border-t border-cyan-400/10 bg-[#0B0F1A]">
            <input
                x-model="input"
                @keydown.enter="send()"
                type="text"
                placeholder="Ask your AI advisor..."
                class="flex-1 bg-[#070A12] border border-cyan-400/20 rounded-xl text-white text-base px-4 py-3 placeholder-gray-500 focus:outline-none focus:border-cyan-400/60 focus:ring-1 focus:ring-cyan-400/25 transition"
            >
            <button
                @click="send()"
                :disabled="loading || !input.trim()"
                class="w-11 h-11 rounded-xl bg-gradient-to-r from-cyan-400 to-fuchsia-500 flex items-center justify-center text-white text-sm hover:opacity-90 transition disabled:opacity-40 shadow-lg shadow-cyan-500/20"
                aria-label="Send message">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                    <path d="M3.478 2.404a.75.75 0 00-.926.941l2.432 7.905H13.5a.75.75 0 010 1.5H4.984l-2.432 7.905a.75.75 0 00.926.94 60.519 60.519 0 0018.445-8.986.75.75 0 000-1.218A60.517 60.517 0 003.478 2.404z"/>
                </svg>
            </button>
        </div>
    </div>

    <button
        @click="open = !open"
        class="cyber-ai-launcher"
        aria-label="Toggle AI chat">
        <span x-show="!open" class="cyber-ai-icon">
            <span>AI</span>
        </span>
        <span x-show="open" x-cloak class="text-2xl text-white font-light">x</span>
    </button>
</div>

<style>
@keyframes cyber-ai-breathe {
    0%, 100% {
        transform: scale(1);
        box-shadow: 0 0 18px rgba(34, 211, 238, 0.45), 0 0 42px rgba(217, 70, 239, 0.2);
    }
    50% {
        transform: scale(1.08);
        box-shadow: 0 0 28px rgba(34, 211, 238, 0.85), 0 0 70px rgba(217, 70, 239, 0.45);
    }
}

.cyber-ai-launcher {
    width: 4.75rem;
    height: 4.75rem;
    border-radius: 9999px;
    border: 1px solid rgba(34, 211, 238, 0.55);
    background:
        radial-gradient(circle at 35% 25%, rgba(255, 255, 255, 0.24), transparent 24%),
        linear-gradient(135deg, #00e5ff 0%, #7c3aed 52%, #ff2bd6 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    animation: cyber-ai-breathe 2.4s ease-in-out infinite;
    transition: transform 180ms ease, filter 180ms ease;
}

.cyber-ai-launcher:hover {
    filter: brightness(1.12);
}

.cyber-ai-icon {
    width: 3.45rem;
    height: 3.45rem;
    border-radius: 9999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    background: linear-gradient(145deg, rgba(5, 8, 18, 0.95), rgba(16, 16, 36, 0.78));
    border: 1px solid rgba(255, 255, 255, 0.28);
    position: relative;
    overflow: hidden;
}

.cyber-ai-icon::before,
.cyber-ai-icon::after {
    content: '';
    position: absolute;
    inset: 0.45rem;
    border-radius: inherit;
    border: 1px solid rgba(34, 211, 238, 0.45);
}

.cyber-ai-icon::after {
    inset: 0.85rem;
    border-color: rgba(217, 70, 239, 0.42);
}

.cyber-ai-icon span {
    position: relative;
    z-index: 1;
    text-shadow: 0 0 10px rgba(34, 211, 238, 0.9);
}

.cyber-ai-icon-sm {
    width: 2.75rem;
    height: 2.75rem;
    animation: cyber-ai-breathe 2.4s ease-in-out infinite;
}
</style>
