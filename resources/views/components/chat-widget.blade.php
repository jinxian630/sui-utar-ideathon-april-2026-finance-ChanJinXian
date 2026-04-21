{{-- FIX 5: Floating AI Chat Widget — fixed bottom-right, open-chat event listener --}}
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
        async send() {
            if (!this.input.trim() || this.loading) return;
            const userMsg = this.input.trim();
            this.messages.push({ role: 'user', text: userMsg });
            this.input = '';
            this.loading = true;

            // Simple local responses (no backend required yet)
            await new Promise(r => setTimeout(r, 800));
            let reply = 'I\'m analyzing your financial data... Keep saving consistently! 💪';
            if (userMsg.toLowerCase().includes('badge')) {
                reply = 'Check your badge progress in the bottom-right panel. Keep adding income to unlock the next tier! 🏅';
            } else if (userMsg.toLowerCase().includes('save') || userMsg.toLowerCase().includes('saving')) {
                reply = 'Great question! Try to allocate at least 20% of each income entry to savings. Small amounts compound over time. 📈';
            } else if (userMsg.toLowerCase().includes('plan')) {
                reply = 'A solid savings plan: Log every transaction, set a monthly income target, and track your badge progress regularly. 🎯';
            }
            this.messages.push({ role: 'assistant', text: reply });
            this.loading = false;

            this.$nextTick(() => {
                const el = this.$refs.msgContainer;
                if (el) el.scrollTop = el.scrollHeight;
            });
        }
    }"
    @open-chat.window="open = true"
    class="fixed bottom-6 right-6 z-50 flex flex-col items-end gap-3"
>
    {{-- Chat Panel --}}
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 scale-95"
        class="w-80 bg-[#1A1A2E] rounded-2xl border border-white/10 shadow-2xl shadow-purple-500/10 flex flex-col overflow-hidden"
        style="height: 420px;"
    >
        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-3 border-b border-white/5 bg-[#12121E]">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-600 to-violet-500 flex items-center justify-center text-base shadow-lg shadow-purple-500/30">🤖</div>
                <div>
                    <h4 class="text-sm font-semibold text-white">AI Financial Assistant</h4>
                    <p class="text-[10px] text-green-400 flex items-center gap-1 mt-0.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse inline-block"></span> Online
                    </p>
                </div>
            </div>
            <button @click="open = false" class="text-gray-500 hover:text-white transition text-xs w-6 h-6 flex items-center justify-center rounded-lg hover:bg-white/5">✕</button>
        </div>

        {{-- Messages --}}
        <div class="flex-1 overflow-y-auto p-4 flex flex-col gap-3"
             x-ref="msgContainer">

            {{-- Welcome message --}}
            <div x-show="messages.length === 0"
                 class="self-start max-w-[85%]">
                <div class="bg-white/5 border border-white/5 text-gray-200 p-3 rounded-2xl rounded-tl-sm text-xs leading-relaxed">
                    👋 Hi {{ explode(' ', auth()->user()->name ?? 'there')[0] }}! I'm your AI advisor.
                    Ask me anything about your savings, badges, or financial goals.
                </div>
            </div>

            <template x-for="(msg, i) in messages" :key="i">
                <div :class="msg.role === 'user' ? 'self-end' : 'self-start'"
                     class="max-w-[85%]">
                    <div :class="msg.role === 'user'
                                ? 'bg-purple-600/30 border border-purple-500/20 text-white rounded-2xl rounded-tr-sm'
                                : 'bg-white/5 border border-white/5 text-gray-200 rounded-2xl rounded-tl-sm'"
                         class="p-3 text-xs leading-relaxed">
                        <span x-text="msg.text"></span>
                    </div>
                </div>
            </template>

            {{-- Typing indicator --}}
            <div x-show="loading" class="self-start flex gap-1 px-3 py-2 bg-white/5 rounded-xl rounded-tl-sm">
                <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay:0ms"></span>
                <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay:150ms"></span>
                <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay:300ms"></span>
            </div>
        </div>

        {{-- Quick chips --}}
        <div class="px-3 py-2 flex gap-2 overflow-x-auto border-t border-white/5 bg-[#12121E]">
            <button @click="quickSend('How am I doing?')"
                    class="text-xs whitespace-nowrap bg-white/5 hover:bg-purple-600/20 border border-white/10 hover:border-purple-500/30 text-gray-400 hover:text-white px-3 py-1 rounded-full transition">
                How am I doing?
            </button>
            <button @click="quickSend('Next badge?')"
                    class="text-xs whitespace-nowrap bg-white/5 hover:bg-purple-600/20 border border-white/10 hover:border-purple-500/30 text-gray-400 hover:text-white px-3 py-1 rounded-full transition">
                Next badge?
            </button>
            <button @click="quickSend('Give me a savings plan')"
                    class="text-xs whitespace-nowrap bg-white/5 hover:bg-purple-600/20 border border-white/10 hover:border-purple-500/30 text-gray-400 hover:text-white px-3 py-1 rounded-full transition">
                Savings plan
            </button>
        </div>

        {{-- Input --}}
        <div class="flex items-center gap-2 px-4 py-3 border-t border-white/5 bg-[#12121E]">
            <input
                x-model="input"
                @keydown.enter="send()"
                type="text"
                placeholder="Ask your AI advisor..."
                class="flex-1 bg-[#0D0D14] border border-white/10 rounded-xl text-white text-xs px-3 py-2 placeholder-gray-600 focus:outline-none focus:border-purple-500/50 focus:ring-1 focus:ring-purple-500/20 transition"
            >
            <button
                @click="send()"
                :disabled="loading || !input.trim()"
                class="w-8 h-8 rounded-xl bg-gradient-to-r from-purple-600 to-violet-500 flex items-center justify-center text-white text-xs hover:opacity-90 transition disabled:opacity-40">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5">
                    <path d="M3.478 2.404a.75.75 0 00-.926.941l2.432 7.905H13.5a.75.75 0 010 1.5H4.984l-2.432 7.905a.75.75 0 00.926.94 60.519 60.519 0 0018.445-8.986.75.75 0 000-1.218A60.517 60.517 0 003.478 2.404z"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Floating bubble button --}}
    <button
        @click="open = !open"
        class="w-14 h-14 rounded-full bg-gradient-to-br from-purple-600 to-violet-500
               flex items-center justify-center shadow-lg shadow-purple-500/30
               hover:scale-105 active:scale-95 transition-transform duration-200">
        <span x-show="!open" class="text-2xl">🤖</span>
        <span x-show="open" x-cloak class="text-xl text-white font-light">✕</span>
    </button>
</div>

<style>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
