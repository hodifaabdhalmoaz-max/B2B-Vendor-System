<!DOCTYPE html>
<html lang="ar" dir="rtl">
{{-- resources/views/errors/429.blade.php --}}
{{-- Custom 429 Too Many Requests page with countdown timer --}}
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>يرجى الانتظار — {{ config('app.name', 'دنيا الأطفال') }}</title>

    {{-- Google Fonts: Cairo for Arabic --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Cairo', 'Tahoma', 'Arial', sans-serif;
            direction: rtl;
            text-align: center;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
            color: #fff;
            overflow: hidden;
        }

        /* Floating particles animation */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            animation: float linear infinite;
        }

        @keyframes float {
            0% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(-10vh) rotate(720deg); opacity: 0; }
        }

        .container {
            position: relative;
            z-index: 1;
            max-width: 560px;
            padding: 3rem 2rem;
            text-align: center;
        }

        /* Shield icon with pulse */
        .icon-wrapper {
            width: 120px;
            height: 120px;
            margin: 0 auto 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(255, 165, 0, 0.1);
            border: 2px solid rgba(255, 165, 0, 0.3);
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(255, 165, 0, 0.3); }
            50% { box-shadow: 0 0 0 20px rgba(255, 165, 0, 0); }
        }

        .icon-wrapper svg {
            width: 56px;
            height: 56px;
            fill: #ffa500;
        }

        .error-code {
            font-size: 4rem;
            font-weight: 800;
            background: linear-gradient(135deg, #ffa500, #ff6347);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.1;
            margin-bottom: 0.5rem;
        }

        .error-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #e0e0e0;
        }

        .error-message {
            font-size: 1rem;
            color: #a0a0b0;
            line-height: 1.8;
            margin-bottom: 2rem;
        }

        /* Countdown timer */
        .countdown-wrapper {
            margin: 2rem auto;
            padding: 1.5rem 2rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }

        .countdown-label {
            font-size: 0.85rem;
            color: #a0a0b0;
            margin-bottom: 0.75rem;
        }

        .countdown {
            font-size: 3rem;
            font-weight: 800;
            color: #ffa500;
            font-variant-numeric: tabular-nums;
            direction: ltr;
        }

        .countdown-unit {
            font-size: 0.75rem;
            color: #888;
            display: block;
            margin-top: 0.25rem;
        }

        /* Progress bar */
        .progress-bar-wrapper {
            width: 100%;
            height: 4px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 2px;
            margin-top: 1rem;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #ffa500, #ff6347);
            border-radius: 2px;
            transition: width 1s linear;
        }

        /* Action buttons */
        .actions {
            margin-top: 2rem;
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-family: 'Cairo', sans-serif;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #ffa500, #ff6347);
            color: #fff;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 165, 0, 0.3);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.08);
            color: #e0e0e0;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }

        .btn svg {
            width: 18px;
            height: 18px;
        }

        /* Footer */
        .footer-note {
            margin-top: 2.5rem;
            font-size: 0.8rem;
            color: #666;
        }

        /* Mobile responsive */
        @media (max-width: 480px) {
            .error-code { font-size: 3rem; }
            .error-title { font-size: 1.2rem; }
            .countdown { font-size: 2.5rem; }
            .container { padding: 2rem 1.5rem; }
            .actions { flex-direction: column; }
            .btn { width: 100%; justify-content: center; }
        }
    </style>
</head>

<body>
    {{-- Floating particles --}}
    <div class="particles" id="particles"></div>

    <div class="container">
        {{-- Shield icon --}}
        <div class="icon-wrapper">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/>
            </svg>
        </div>

        <div class="error-code">429</div>
        <h1 class="error-title">طلبات كثيرة جداً</h1>

        <p class="error-message">
            نعتذر، لقد تم إرسال عدد كبير من الطلبات في وقت قصير.
            <br>يرجى الانتظار قليلاً ثم المحاولة مرة أخرى.
        </p>

        {{-- Countdown timer --}}
        <div class="countdown-wrapper">
            <div class="countdown-label">يمكنك المحاولة مرة أخرى بعد:</div>
            <div class="countdown" id="countdown">--</div>
            <span class="countdown-unit">ثانية</span>

            <div class="progress-bar-wrapper">
                <div class="progress-bar-fill" id="progressBar" style="width: 100%"></div>
            </div>
        </div>

        {{-- Action buttons --}}
        <div class="actions">
            <a href="{{ url('/') }}" class="btn btn-primary" id="homeBtn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                العودة للرئيسية
            </a>
            <button class="btn btn-secondary" onclick="window.location.reload()" id="retryBtn" disabled>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="23 4 23 10 17 10"/>
                    <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                </svg>
                إعادة المحاولة
            </button>
        </div>

        <p class="footer-note">
            إذا استمرت المشكلة، تواصل معنا عبر
            <a href="{{ route('contact') }}" style="color: #ffa500; text-decoration: none;">صفحة الاتصال</a>
        </p>
    </div>

    <script>
        // Retry-After value from the server response header (in seconds)
        // Default to 60 seconds if not set
        const retryAfter = {{ $retryAfter ?? 60 }};
        let remaining = retryAfter;

        const countdownEl = document.getElementById('countdown');
        const progressBar = document.getElementById('progressBar');
        const retryBtn = document.getElementById('retryBtn');

        function updateCountdown() {
            if (remaining <= 0) {
                // Timer expired — enable retry
                countdownEl.textContent = '٠';
                progressBar.style.width = '0%';
                retryBtn.disabled = false;
                retryBtn.style.opacity = '1';
                retryBtn.style.cursor = 'pointer';
                return;
            }

            // Format the remaining seconds in Arabic-Indic numerals
            countdownEl.textContent = remaining;

            // Update progress bar
            const percent = (remaining / retryAfter) * 100;
            progressBar.style.width = percent + '%';

            remaining--;
            setTimeout(updateCountdown, 1000);
        }

        // Start countdown
        updateCountdown();

        // Create floating particles
        (function createParticles() {
            const container = document.getElementById('particles');
            for (let i = 0; i < 30; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.width = particle.style.height = (Math.random() * 4 + 2) + 'px';
                particle.style.animationDuration = (Math.random() * 10 + 8) + 's';
                particle.style.animationDelay = (Math.random() * 10) + 's';
                container.appendChild(particle);
            }
        })();
    </script>
</body>
</html>
