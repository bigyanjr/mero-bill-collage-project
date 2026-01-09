<?php
// public/chatbot.php
require_once '../includes/db.php';
require_once '../includes/auth.php';

require_login();

$title = "Help Assistant";
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="max-w-2xl mx-auto h-[calc(100vh-140px)] flex flex-col">
    <div class="bg-white shadow rounded-lg flex flex-col h-full overflow-hidden">
        <!-- Header -->
        <div class="bg-blue-600 p-4 text-white flex items-center shadow-md z-10">
            <div class="flex-shrink-0 bg-white bg-opacity-20 rounded-full p-2">
                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
            </div>
            <div class="ml-3">
                <h2 class="text-lg font-bold">Mero Bill Assistant</h2>
                <p class="text-blue-100 text-xs">Ask me how to use the system.</p>
            </div>
        </div>

        <!-- Chat Area -->
        <div id="chatMessages" class="flex-1 p-4 overflow-y-auto bg-gray-50 bg-[url('https://www.transparenttextures.com/patterns/subtle-dark-vertical.png')]">
            <!-- Welcome Message -->
            <div class="flex justify-start mb-4">
                <div class="px-4 py-2 max-w-[80%] shadow-sm chat-bubble-bot">
                    Namaste! I am your AI assistant. Ask me anything about Mero Bill, like "How do I create an invoice?" or "What is a Mart customer?".
                </div>
            </div>
        </div>

        <!-- Input Area -->
        <div class="p-4 bg-white border-t border-gray-200">
            <form id="chatForm" class="flex space-x-2">
                <input type="text" id="chatInput" class="flex-1 rounded-full border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2 border" placeholder="Type your question..." autocomplete="off">
                <button type="submit" class="bg-blue-600 text-white rounded-full p-2 hover:bg-blue-700 shadow-sm transition-colors duration-200 btn-hover">
                    <svg class="h-6 w-6 transform rotate-90" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>

<script src="../assets/js/chatbot.js"></script>
<?php include '../includes/footer.php'; ?>
