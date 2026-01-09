<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mero Bill - AI POS System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #0f172a; color: white; overflow-x: hidden; }
        
        /* Aurora Background Animation */
        .aurora-bg {
            position: absolute;
            top: -50%; left: -50%; right: -50%; bottom: -50%;
            background: radial-gradient(circle at 50% 50%, rgba(76, 29, 149, 0.4), transparent 50%),
                        radial-gradient(circle at 10% 20%, rgba(37, 99, 235, 0.3), transparent 40%),
                        radial-gradient(circle at 90% 80%, rgba(236, 72, 153, 0.3), transparent 40%);
            animation: aurora 20s infinite alternate linear;
            z-index: -1;
            filter: blur(60px);
        }
        @keyframes aurora {
            0% { transform: rotate(0deg) scale(1); }
            100% { transform: rotate(10deg) scale(1.2); }
        }

        /* Glassmorphism */
        .glass {
            background: rgba(255, 255, 255, 0.05);;
            backdrop-filter: blur(12px);
            border: 1px solid rgba(84, 13, 13, 0.52);
            box-shadow: 0 8px 32px 0 rgba(132, 11, 11, 0.73);
        }

        /* Scroll Animations */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 1s cubic-bezier(0.5, 0, 0, 1);
        }
        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }
        
        .delay-100 { transition-delay: 100ms; }
        .delay-200 { transition-delay: 200ms; }
        .delay-300 { transition-delay: 300ms; }
    </style>
</head>
<body class="antialiased selection:bg-pink-500 selection:text-white">

    <!-- Background -->
    <div class="aurora-bg fixed inset-0"></div>

    <!-- Navigation -->
    <nav class="fixed w-full z-50 transition-all duration-300" :class="{'glass py-2': window.scrollY > 20, 'py-6': window.scrollY <= 20}" x-data>
        <div class="max-w-7xl mx-auto px-6 flex justify-between items-center">
            <div class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-400 to-pink-500">
                MeroBill
            </div>
            <div class="hidden md:flex space-x-8 text-sm font-semibold tracking-wide">
                <a href="#features" class="hover:text-blue-400 transition">FEATURES</a>
                <a href="#demo" class="hover:text-blue-400 transition">LIVE DEMO</a>
                <a href="login.php" class="hover:text-blue-400 transition">LOGIN</a>
            </div>
            <a href="register_demo.php" class="px-6 py-2 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full font-bold shadow-lg hover:shadow-blue-500/50 hover:scale-105 transition transform">
                Get Started
            </a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="min-h-screen flex items-center justify-center relative pt-20">
        <div class="text-center px-6 max-w-5xl mx-auto">
            <div class="reveal active inline-block mb-4 px-4 py-1 rounded-full border border-blue-500/30 bg-blue-500/10 text-blue-300 text-sm font-mono tracking-widest">
                4TH SEMISTER PROJECT
            </div>
            <h1 class="reveal delay-100 active text-6xl md:text-8xl font-bold mb-6 leading-tight">
                Manage Business <br>
                <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-400 via-purple-400 to-pink-400 animate-pulse">With Intelligence</span>
            </h1>
            <p class="reveal delay-200 active text-xl text-gray-300 mb-10 max-w-2xl mx-auto leading-relaxed">
                Experience the future of POS. Instant invoicing, AI-driven insights, and seamless inventory management. 
                All wrapped in a stunning interface.
            </p>
            <div class="reveal delay-300 active flex flex-col md:flex-row gap-4 justify-center">
                <a href="#demo" class="px-8 py-4 bg-white text-black rounded-full font-bold hover:bg-gray-200 transition transform hover:-translate-y-1 shadow-xl">
                    Try Live Demo
                </a>
                <a href="login.php" class="px-8 py-4 glass text-white rounded-full font-bold hover:bg-white/10 transition transform hover:-translate-y-1">
                    Login Account
                </a>
            </div>
        </div>
        
        <!-- Floating Elements Animation -->
        <div class="absolute top-1/4 left-10 w-24 h-24 bg-blue-500 rounded-full blur-[80px] opacity-50 animate-bounce"></div>
        <div class="absolute bottom-1/4 right-10 w-32 h-32 bg-pink-500 rounded-full blur-[80px] opacity-50 animate-pulse"></div>
    </section>

    <!-- Demo Section -->
    <section id="demo" class="py-24 relative">
        <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-2 gap-16 items-center">
            <div class="reveal">
                <h2 class="text-4xl font-bold mb-6">Experience It <span class="text-blue-400">Instantly</span></h2>
                <p class="text-gray-400 text-lg mb-8">
                    Skip the sign-up forms. We've prepared a demo environment for you. 
                    Populate products, creating invoices, and chat with the AI assistant in seconds.
                </p>
                <ul class="space-y-4 mb-8 text-gray-300">
                    <li class="flex items-center gap-3">
                        <span class="w-2 h-2 bg-blue-500 rounded-full shadow-[0_0_10px_#3b82f6]"></span>
                        Pre-filled Inventory
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="w-2 h-2 bg-purple-500 rounded-full shadow-[0_0_10px_#a855f7]"></span>
                        Smart Chatbot Assistant
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="w-2 h-2 bg-pink-500 rounded-full shadow-[0_0_10px_#ec4899]"></span>
                        Real-time Analytics
                    </li>
                </ul>
                <a href="register_demo.php" class="text-blue-400 hover:text-white transition font-bold border-b border-blue-400/50 hover:border-white pb-1">
                    Learn about pricing &rarr;
                </a>
            </div>

            <!-- Credential Card -->
            <div class="reveal delay-200 relative group">
                <div class="absolute -inset-1 bg-gradient-to-r from-blue-600 to-pink-600 rounded-2xl blur opacity-75 group-hover:opacity-100 transition duration-1000 group-hover:duration-200"></div>
                <div class="relative glass p-8 rounded-2xl ring-1 ring-white/10">
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex gap-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                        </div>
                        <span class="text-xs font-mono text-gray-500">DEMO ACCESS</span>
                    </div>
                    
                    <div class="space-y-4 mb-8">
                        <div>
                            <label class="text-xs text-gray-500 font-mono">USERNAME</label>
                            <div class="flex justify-between items-center p-3 bg-black/30 rounded-lg border border-white/5 font-mono text-green-400">
                                <span>demo_user</span>
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                            </div>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 font-mono">PASSWORD</label>
                            <div class="flex justify-between items-center p-3 bg-black/30 rounded-lg border border-white/5 font-mono text-green-400">
                                <span>••••••••</span>
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                            </div>
                        </div>
                    </div>

                    <a href="register_demo.php" class="block w-full text-center py-4 bg-blue-600 hover:bg-blue-500 rounded-xl font-bold shadow-lg shadow-blue-600/30 transition transform hover:scale-[1.02] active:scale-95">
                        Launch Instant Demo
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Grid -->
    <section id="features" class="py-24 relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-6">
            <h2 class="text-4xl font-bold text-center mb-16 reveal">Why Mero Bill?</h2>
            
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Card 1 -->
                <div class="glass p-8 rounded-3xl hover:bg-white/5 transition duration-500 border-t border-white/10 reveal delay-100">
                    <div class="w-14 h-14 bg-blue-500/20 rounded-2xl flex items-center justify-center text-blue-400 mb-6">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">One-Click Invoicing</h3>
                    <p class="text-gray-400 leading-relaxed">Generate professional invoices in seconds. PDF export, email delivery, and payment tracking built-in.</p>
                </div>

                <!-- Card 2 -->
                <div class="glass p-8 rounded-3xl hover:bg-white/5 transition duration-500 border-t border-white/10 reveal delay-200">
                    <div class="w-14 h-14 bg-purple-500/20 rounded-2xl flex items-center justify-center text-purple-400 mb-6">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">AI Assistant</h3>
                    <p class="text-gray-400 leading-relaxed">"Create invoice for John". Talk to your business. Our AI handles the boring data entry for you.</p>
                </div>

                <!-- Card 3 -->
                <div class="glass p-8 rounded-3xl hover:bg-white/5 transition duration-500 border-t border-white/10 reveal delay-300">
                    <div class="w-14 h-14 bg-pink-500/20 rounded-2xl flex items-center justify-center text-pink-400 mb-6">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Financial Clarity</h3>
                    <p class="text-gray-400 leading-relaxed">Visual dashboards showing real-time sales, expenses, and growth metrics at a glance.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-white/10 py-12 relative z-10 bg-black/20">
        <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center opacity-60 hover:opacity-100 transition">
            <p>&copy; 2024 Mero Bill. All rights reserved.</p>
            <div class="flex space-x-6 mt-4 md:mt-0">
                <a href="#" class="hover:text-blue-400">Privacy</a>
                <a href="#" class="hover:text-blue-400">Terms</a>
                <a href="#" class="hover:text-blue-400">Contact</a>
            </div>
        </div>
    </footer>

    <script>
        // Scroll Animation Logic
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        };

        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.reveal').forEach((el) => {
            observer.observe(el);
        });
    </script>
</body>
</html>
