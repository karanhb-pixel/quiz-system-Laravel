<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | Quiz System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: radial-gradient(circle at top right, #1e1b4b, #0f172a, #020617);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            color: white;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        .gradient-text {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 50%, #ec4899 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .progress-bar {
            height: 4px;
            background: linear-gradient(to right, #6366f1, #ec4899);
            width: 0%;
            transition: width 1s linear;
        }
    </style>
</head>
<body>
    <div class="glass-card p-12 rounded-3xl text-center max-w-xl mx-auto relative z-10 mx-4">
        <!-- Error Code -->
        <h1 class="text-9xl font-bold opacity-10 absolute -top-20 left-1/2 -translate-x-1/2 select-none">404</h1>
        
        <!-- Animated Icon -->
        <div class="flex justify-center mb-8">
            <div class="animate-float">
                <svg class="w-32 h-32 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 9.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0zM12 14v2m0 4h.01" />
                </svg>
            </div>
        </div>

        <h2 class="text-4xl font-bold mb-4">Lost in Space?</h2>
        <p class="text-gray-400 text-lg mb-8 leading-relaxed">
            The page you are looking for has vanished into the digital void. Don't worry, we're bringing you back to civilization.
        </p>

        <!-- Redirect Info -->
        <div class="mb-10">
            <p class="text-sm text-indigo-300 mb-2 font-medium tracking-widest uppercase">Redirecting in <span id="countdown">5</span> seconds</p>
            <div class="w-full bg-white/5 rounded-full overflow-hidden">
                <div id="progress" class="progress-bar"></div>
            </div>
        </div>

        <!-- Action Button -->
        <a href="{{ url('/') }}" class="inline-flex items-center px-8 py-4 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-2xl transition-all hover:scale-105 active:scale-95 shadow-lg shadow-indigo-500/25">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
            Take Me Home Now
        </a>
    </div>

    <!-- Decorative elements -->
    <div class="absolute top-1/4 left-1/4 w-64 h-64 bg-indigo-600/10 blur-[120px] rounded-full"></div>
    <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-purple-600/10 blur-[150px] rounded-full"></div>

    <script>
        let seconds = 5;
        const countdownEl = document.getElementById('countdown');
        const progressEl = document.getElementById('progress');
        
        const timer = setInterval(() => {
            seconds--;
            countdownEl.innerText = seconds;
            progressEl.style.width = ((5 - seconds) * 20) + '%';

            if (seconds <= 0) {
                clearInterval(timer);
                window.location.href = "{{ url('/') }}";
            }
        }, 1000);

        // Set initial progress
        setTimeout(() => { progressEl.style.width = '20%'; }, 100);
    </script>
</body>
</html>
