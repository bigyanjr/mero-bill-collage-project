<!-- includes/chat_widget.php -->
<div x-data="{ 
    isOpen: false, 
    messages: [
        { role: 'system', text: 'Hello! How can I assist you today?' }
    ],
    userInput: '',
    isLoading: false,
    
    async sendMessage() {
        if (this.userInput.trim() === '' || this.isLoading) return;
        
        const text = this.userInput;
        this.messages.push({ role: 'user', text: text });
        this.userInput = '';
        this.isLoading = true;
        
        // Scroll to bottom
        this.$nextTick(() => { this.scrollToBottom(); });

        try {
            const response = await fetch('../public/api_chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: text })
            });
            
            const data = await response.json();
            
            if (data.reply) {
                this.messages.push({ role: 'ai', text: data.reply });
            } else if (data.error) {
                this.messages.push({ role: 'error', text: 'Error: ' + data.error });
            }
        } catch (e) {
            this.messages.push({ role: 'error', text: 'Failed to connect to the assistant.' });
        } finally {
            this.isLoading = false;
            this.$nextTick(() => { this.scrollToBottom(); });
        }
    },
    
    scrollToBottom() {
        const chatBox = document.getElementById('chat-messages');
        if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
    }
}" 
class="fixed bottom-6 right-6 z-50 flex flex-col items-end">

    <!-- Chat Window -->
    <div x-show="isOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="bg-white w-80 sm:w-96 rounded-2xl shadow-2xl mb-4 border border-gray-200 overflow-hidden flex flex-col"
         style="height: 500px; display: none;">
        
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-4 flex justify-between items-center text-white">
            <div class="flex items-center space-x-2">
                <div class="h-2 w-2 bg-green-400 rounded-full animate-pulse"></div>
                <h3 class="font-bold text-sm">Mero Bill Assistant</h3>
            </div>
            <button @click="isOpen = false" class="text-white hover:text-gray-200 focus:outline-none">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <!-- Messages Area -->
        <div id="chat-messages" class="flex-1 p-4 overflow-y-auto bg-gray-50 space-y-4">
            <template x-for="(msg, index) in messages" :key="index">
                <div class="flex flex-col" :class="msg.role === 'user' ? 'items-end' : 'items-start'">
                    <!-- Bubble -->
                    <div class="max-w-[85%] rounded-2xl px-4 py-2 text-sm shadow-sm"
                         :class="{
                             'bg-blue-600 text-white rounded-br-none': msg.role === 'user',
                             'bg-white text-gray-800 border border-gray-200 rounded-bl-none': msg.role === 'ai',
                             'bg-gray-200 text-gray-600 rounded-bl-none': msg.role === 'system',
                             'bg-red-100 text-red-600 border border-red-200': msg.role === 'error'
                         }">
                        <p x-text="msg.text" class="whitespace-pre-wrap leading-relaxed"></p>
                    </div>
                    <!-- Label -->
                    <span class="text-[10px] text-gray-400 mt-1 mx-1" x-text="msg.role === 'user' ? 'You' : (msg.role === 'ai' ? 'AI' : 'System')"></span>
                </div>
            </template>
            
            <!-- Loading Indicator -->
            <div x-show="isLoading" class="flex justify-start">
                 <div class="bg-white border border-gray-200 rounded-2xl rounded-bl-none px-4 py-3 shadow-sm flex space-x-1 items-center">
                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
                </div>
            </div>
        </div>
        
        <!-- Input Area -->
        <div class="p-4 bg-white border-t border-gray-200">
            <form @submit.prevent="sendMessage" class="flex space-x-2">
                <input x-model="userInput" 
                       type="text" 
                       placeholder="Type your question..." 
                       class="flex-1 border border-gray-300 rounded-full px-4 py-2 text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-sm"
                >
                <button type="submit" 
                        class="bg-blue-600 text-white rounded-full p-2 hover:bg-blue-700 transition shadow-md disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="isLoading || userInput.trim() === ''">
                    <svg class="h-5 w-5 transform rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                </button>
            </form>
        </div>
    </div>

    <!-- Floating Toggle Button -->
    <button @click="isOpen = !isOpen" 
            class="group bg-blue-600 hover:bg-blue-700 text-white rounded-full p-4 shadow-lg transition-transform hover:scale-110 focus:outline-none ring-4 ring-transparent hover:ring-blue-200">
        <!-- Icon Open -->
        <svg x-show="!isOpen" class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
        <!-- Icon Close -->
        <svg x-show="isOpen" class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        
        <!-- Badge (Optional) -->
        <span class="absolute top-0 right-0 -mt-1 -mr-1 flex h-4 w-4">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-4 w-4 bg-red-500"></span>
        </span>
    </button>
</div>
